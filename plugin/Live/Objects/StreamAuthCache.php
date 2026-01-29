<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

/**
 * Manages pre-authorization cache for RTMP streaming
 * Stores temporary authorizations in the format {IP}_{streamKey}
 */
class StreamAuthCache
{
    private static $cacheDir = null;
    private static $ttl = 60; // seconds

    /**
     * Initialize cache directory
     */
    private static function initCacheDir()
    {
        if (self::$cacheDir === null) {
            global $global;
            self::$cacheDir = $global['systemRootPath'] . 'videos/cache/stream_auth/';
            if (!file_exists(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
            }
        }
        return self::$cacheDir;
    }

    /**
     * Generate cache key in format: {IP}_{streamKey}
     */
    private static function getCacheKey($ip, $streamKey)
    {
        $encrypted = encryptString($ip . '_' . $streamKey);
        // Use md5 hash for filename safety (avoid special chars and length issues)
        return md5($encrypted);
    }

    /**
     * Get cache file path
     */
    private static function getCacheFile($ip, $streamKey)
    {
        self::initCacheDir();
        $key = self::getCacheKey($ip, $streamKey);
        return self::$cacheDir . $key . '.json';
    }

    /**
     * Create a pre-authorization
     *
     * @param string $streamKey Stream key
     * @param int $users_id User ID
     * @return bool
     */
    public static function create($streamKey, $users_id)
    {
        $ip = getRealIpAddr();
        $file = self::getCacheFile($ip, $streamKey);
        $data = [
            'ip' => $ip,
            'streamKey' => $streamKey,
            'users_id' => $users_id,
            'created' => time(),
            'expires' => time() + self::$ttl
        ];

        $result = file_put_contents($file, json_encode($data));

        if ($result !== false) {
            _error_log("StreamAuthCache::create - Authorization created: IP={$ip}, Key={$streamKey}, User={$users_id}");
            return true;
        }

        _error_log("StreamAuthCache::create - ERROR creating authorization: IP={$ip}, Key={$streamKey}");
        return false;
    }

    /**
     * Check if valid authorization exists
     *
     * @param string $streamKey Stream key
     * @return array|false Returns authorization data or false
     */
    public static function get($streamKey)
    {
        $ip = getRealIpAddr();
        $file = self::getCacheFile($ip, $streamKey);

        if (!file_exists($file)) {
            return false;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if ($data === null) {
            return false;
        }

        // Check if expired
        if ($data['expires'] < time()) {
            _error_log("StreamAuthCache::get - Authorization expired: IP={$ip}, Key={$streamKey}");
            self::delete($streamKey);
            return false;
        }

        return $data;
    }

    /**
     * Remove an authorization (single-use)
     *
     * @param string $streamKey Stream key
     * @return bool
     */
    public static function delete($streamKey)
    {
        $ip = getRealIpAddr();
        $file = self::getCacheFile($ip, $streamKey);

        if (file_exists($file)) {
            $result = unlink($file);
            if ($result) {
                _error_log("StreamAuthCache::delete - Authorization removed: IP={$ip}, Key={$streamKey}");
            }
            return $result;
        }

        return true;
    }

    /**
     * Clean up expired authorizations
     */
    public static function cleanup()
    {
        self::initCacheDir();

        $files = glob(self::$cacheDir . '*.json');
        $cleaned = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content !== false) {
                $data = json_decode($content, true);
                if ($data !== null && isset($data['expires'])) {
                    if ($data['expires'] < time()) {
                        unlink($file);
                        $cleaned++;
                    }
                }
            }
        }

        if ($cleaned > 0) {
            _error_log("StreamAuthCache::cleanup - {$cleaned} expired authorizations removed");
        }

        return $cleaned;
    }

    /**
     * Get configured TTL
     */
    public static function getTTL()
    {
        return self::$ttl;
    }
}
