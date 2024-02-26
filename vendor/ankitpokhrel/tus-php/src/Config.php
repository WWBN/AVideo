<?php

namespace TusPhp;

class Config
{
    /** @const string */
    private const DEFAULT_CONFIG_PATH = __DIR__ . '/Config/server.php';

    /** @var array */
    protected static $config = [];

    /**
     * Get path to the base cache directory.
     *
     * @return string
     */
    public static function getCacheHome(): string
    {
        if (($cacheDir = getenv('TUS_CACHE_HOME')) !== false) {
            return $cacheDir;
        }

        // See https://specifications.freedesktop.org/basedir-spec/basedir-spec-latest.html
        if (($cacheDir = getenv('XDG_CACHE_HOME')) !== false) {
            return $cacheDir;
        }

        return \dirname(__DIR__);
    }


    /**
     * Load default application configs.
     *
     * @param string|array $config
     * @param bool         $force
     *
     * @return void
     */
    public static function set($config = null, bool $force = false)
    {
        if ( ! $force && ! empty(self::$config)) {
            return;
        }

        if (\is_array($config)) {
            self::$config = $config;
        } else {
            self::$config = require $config ?? self::DEFAULT_CONFIG_PATH;
        }
    }

    /**
     * Get config.
     *
     * @param string|null $key Key to extract.
     *
     * @return mixed
     */
    public static function get(string $key = null)
    {
        self::set();

        if (empty($key)) {
            return self::$config;
        }

        $keys  = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if ( ! isset($value[$k])) {
                return null;
            }

            $value = $value[$k];
        }

        return $value;
    }
}
