<?php

namespace TusPhp\Cache;

use TusPhp\File;
use Carbon\Carbon;
use TusPhp\Config;

class FileStore extends AbstractCache
{
    /** @var int */
    public const LOCK_NONE = 0;

    /** @var string */
    protected $cacheDir;

    /** @var string */
    protected $cacheFile;

    /**
     * FileStore constructor.
     *
     * @param string|null $cacheDir
     * @param string|null $cacheFile
     */
    public function __construct(string $cacheDir = null, string $cacheFile = null)
    {
        $cacheDir  = $cacheDir ?? Config::get('file.dir');
        $cacheFile = $cacheFile ?? Config::get('file.name');

        $this->setCacheDir($cacheDir);
        $this->setCacheFile($cacheFile);
    }

    /**
     * Set cache dir.
     *
     * @param string $path
     *
     * @return self
     */
    public function setCacheDir(string $path): self
    {
        $this->cacheDir = $path;

        return $this;
    }

    /**
     * Get cache dir.
     *
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * Set cache file.
     *
     * @param string $file
     *
     * @return self
     */
    public function setCacheFile(string $file): self
    {
        $this->cacheFile = $file;

        return $this;
    }

    /**
     * Get cache file.
     *
     * @return string
     */
    public function getCacheFile(): string
    {
        return $this->cacheDir . $this->cacheFile;
    }

    /**
     * Create cache dir if not exists.
     *
     * @return void
     */
    protected function createCacheDir()
    {
        if ( ! file_exists($this->cacheDir)) {
            mkdir($this->cacheDir);
        }
    }

    /**
     * Create a cache file.
     *
     * @return void
     */
    protected function createCacheFile()
    {
        $this->createCacheDir();

        $cacheFilePath = $this->getCacheFile();

        if ( ! file_exists($cacheFilePath)) {
            touch($cacheFilePath);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, bool $withExpired = false)
    {
        $key      = $this->getActualCacheKey($key);
        $contents = $this->getCacheContents();

        if (empty($contents[$key])) {
            return null;
        }

        if ($withExpired) {
            return $contents[$key];
        }

        return $this->isValid($key) ? $contents[$key] : null;
    }

    /**
     * @param string        $path
     * @param int           $type
     * @param callable|null $cb
     *
     * @return mixed
     */
    protected function lock(string $path, int $type = LOCK_SH, callable $cb = null, $fopenType = FILE::READ_BINARY)
    {
        $out    = false;
        $handle = @fopen($path, $fopenType);

        if (false === $handle) {
            return $out;
        }

        try {
            if (flock($handle, $type)) {
                clearstatcache(true, $path);

                $out = $cb($handle);
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return $out;
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param string $path
     *
     * @return string
     */
    public function sharedGet(string $path): string
    {
        return $this->lock($path, LOCK_SH, function ($handle) use ($path) {
            $fstat    = fstat($handle);
            $size     = $fstat ? $fstat['size'] : 1;
            $contents = fread($handle, $size ?: 1);

            if (false === $contents) {
                return '';
            }

            return $contents;
        });
    }

    /**
     * Write the contents of a file with exclusive lock.
     *
     * @param string $path
     * @param string $contents
     * @param int    $lock
     *
     * @return int|false
     */
    public function put(string $path, string $contents, int $lock = LOCK_EX)
    {
        return file_put_contents($path, $contents, $lock);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, $value)
    {
        $cacheKey  = $this->getActualCacheKey($key);
        $cacheFile = $this->getCacheFile();

        if ( ! file_exists($cacheFile) || ! $this->isValid($cacheKey)) {
            $this->createCacheFile();
        }

        return $this->lock($cacheFile, LOCK_EX, function ($handle) use ($cacheKey, $cacheFile, $value) {
            $size     = fstat($handle)['size'];
            $contents = fread($handle, $size ?: 1) ?? '';
            $contents = json_decode($contents, true) ?? [];

            if ( ! empty($contents[$cacheKey]) && \is_array($value)) {
                $contents[$cacheKey] = $value + $contents[$cacheKey];
            } else {
                $contents[$cacheKey] = $value;
            }
            ftruncate($handle, 0);
            return fwrite($handle, json_encode($contents));
        }, FILE::APPEND_WRITE);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): bool
    {
        $cacheKey = $this->getActualCacheKey($key);
        $contents = $this->getCacheContents();

        if (isset($contents[$cacheKey])) {
            unset($contents[$cacheKey]);

            return false !== $this->put($this->getCacheFile(), json_encode($contents));
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function keys(): array
    {
        $contents = $this->getCacheContents();

        if (\is_array($contents)) {
            return array_keys($contents);
        }

        return [];
    }

    /**
     * Check if cache is still valid.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isValid(string $key): bool
    {
        $key  = $this->getActualCacheKey($key);
        $meta = $this->getCacheContents()[$key] ?? [];

        if (empty($meta['expires_at'])) {
            return false;
        }

        return Carbon::now() < Carbon::createFromFormat(self::RFC_7231, $meta['expires_at']);
    }

    /**
     * Get cache contents.
     *
     * @return array|bool
     */
    public function getCacheContents()
    {
        $cacheFile = $this->getCacheFile();

        if ( ! file_exists($cacheFile)) {
            return false;
        }

        return json_decode($this->sharedGet($cacheFile), true) ?? [];
    }

    /**
     * Get actual cache key with prefix.
     *
     * @param string $key
     *
     * @return string
     */
    public function getActualCacheKey(string $key): string
    {
        $prefix = $this->getPrefix();

        if (false === strpos($key, $prefix)) {
            $key = $prefix . $key;
        }

        return $key;
    }
}
