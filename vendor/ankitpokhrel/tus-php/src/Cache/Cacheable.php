<?php

namespace TusPhp\Cache;

interface Cacheable
{
    /** @see https://tools.ietf.org/html/rfc7231#section-7.1.1.1 */
    public const RFC_7231 = 'D, d M Y H:i:s \G\M\T';

    /**
     * Get data associated with the key.
     *
     * @param string $key
     * @param bool   $withExpired
     *
     * @return mixed
     */
    public function get(string $key, bool $withExpired = false);

    /**
     * Set data to the given key.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set(string $key, $value);

    /**
     * Delete data associated with the key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Delete all data associated with the keys.
     *
     * @param array $keys
     *
     * @return bool
     */
    public function deleteAll(array $keys): bool;

    /**
     * Get time to live.
     *
     * @return int
     */
    public function getTtl(): int;

    /**
     * Get cache keys.
     *
     * @return array
     */
    public function keys(): array;

    /**
     * Set cache prefix.
     *
     * @param string $prefix
     *
     * @return self
     */
    public function setPrefix(string $prefix): self;

    /**
     * Get cache prefix.
     *
     * @return string
     */
    public function getPrefix(): string;
}
