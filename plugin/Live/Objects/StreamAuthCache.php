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
            _error_log("StreamAuthCache::create - Authorization created: IP={$ip}, Key={$streamKey}, User={$users_id}, File={$file}");
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

        _error_log("StreamAuthCache::get - Looking for auth: IP={$ip}, Key={$streamKey}, File={$file}");

        if (!file_exists($file)) {
            _error_log("StreamAuthCache::get - File does not exist: {$file}");
            return false;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            _error_log("StreamAuthCache::get - Could not read file: {$file}");
            return false;
        }

        $data = json_decode($content, true);
        if ($data === null) {
            _error_log("StreamAuthCache::get - Invalid JSON in file: {$file}");
            return false;
        }

        // Check if expired
        if ($data['expires'] < time()) {
            _error_log("StreamAuthCache::get - Authorization expired: IP={$ip}, Key={$streamKey}");
            self::delete($streamKey);
            return false;
        }

        _error_log("StreamAuthCache::get - Valid authorization found: IP={$ip}, Key={$streamKey}, User={$data['users_id']}");
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

    /**
     * Process pre-authorization request
     * Validates credentials and creates temporary authorization
     *
     * @param string $username Username
     * @param string $password Password
     * @return stdClass Response object with error, msg, rtmpUrl, expiresIn
     */
    public static function processPreauthorization($username, $password)
    {
        require_once dirname(__FILE__) . '/../../../objects/user.php';
        AVideoPlugin::loadPlugin('Live');

        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = "";
        $obj->rtmpUrl = "";
        $obj->expiresIn = self::getTTL();

        _error_log("StreamAuthCache::processPreauthorization - Request from IP: " . getRealIpAddr());

        // Validate input
        if (empty($username)) {
            $obj->msg = "Missing credentials";
            _error_log("StreamAuthCache::processPreauthorization - Missing user");
            return $obj;
        }

        if (empty($password)) {
            $obj->msg = "Missing credentials";
            _error_log("StreamAuthCache::processPreauthorization - Missing pass");
            return $obj;
        }

        try {
            // Check if password is a user hash
            if (preg_match('/^_user_hash_/', $password)) {
                // For user hash, extract and validate it
                $userHashData = User::getPasswordFromUserHashIfTheItIsValid($password);
                if ($userHashData === false) {
                    $obj->msg = "Invalid or expired user hash";
                    _error_log("StreamAuthCache::processPreauthorization - Invalid user hash provided");
                    return $obj;
                }

                // User hash is valid, decode to get user ID
                $decoded = json_decode(decryptString($password));
                if (empty($decoded) || empty($decoded->u)) {
                    $obj->msg = "Malformed user hash";
                    _error_log("StreamAuthCache::processPreauthorization - Malformed user hash");
                    return $obj;
                }

                // Load user directly by ID (no password check needed)
                $user = new User($decoded->u);
                _error_log("StreamAuthCache::processPreauthorization - Using valid user hash for user ID: {$decoded->u}");
            } else {
                // Normal password authentication
                $user = new User(0, $username, $password);
            }

            if (empty($user->getBdId())) {
                $obj->msg = "Invalid credentials";
                _error_log("StreamAuthCache::processPreauthorization - Invalid credentials for user: {$username}");
                return $obj;
            }

            // Check if user can stream
            if (!$user->thisUserCanStream() && !User::isAdmin($user->getBdId())) {
                $obj->msg = "User not allowed to stream: " . User::getLastUserCanStreamReason();
                _error_log("StreamAuthCache::processPreauthorization - User {$user->getBdId()} cannot stream: " . User::getLastUserCanStreamReason());
                return $obj;
            }

            // Get user's LiveTransmition
            $liveTransmition = LiveTransmition::getFromDbByUser($user->getBdId());

            if (empty($liveTransmition) || empty($liveTransmition['key'])) {
                $obj->msg = "Stream key not found for user";
                _error_log("StreamAuthCache::processPreauthorization - Stream key not found for user: {$user->getBdId()}");
                return $obj;
            }

            $streamKey = $liveTransmition['key'];

            // Create temporary authorization (IP obtained internally)
            $authCreated = self::create($streamKey, $user->getBdId());

            if (!$authCreated) {
                $obj->msg = "Failed to create authorization";
                _error_log("StreamAuthCache::processPreauthorization - Failed to create auth for Key: {$streamKey}");
                return $obj;
            }

            // Get RTMP URL
            $rtmpServer = Live::getServer();
            $obj->rtmpUrl = rtrim($rtmpServer, '/') . '/' . $streamKey;

            // Success
            $obj->error = false;
            $obj->msg = "Authorized";

            _error_log("StreamAuthCache::processPreauthorization - Authorization created successfully for user {$user->getBdId()}, Key: {$streamKey}");

        } catch (Exception $e) {
            $obj->msg = "Authentication error: " . $e->getMessage();
            _error_log("StreamAuthCache::processPreauthorization - Exception: " . $e->getMessage());
        }

        // Clean up expired authorizations
        self::cleanup();

        return $obj;
    }
}
