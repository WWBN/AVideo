<?php

namespace Amp\Parallel\Worker;

interface Environment extends \ArrayAccess
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * @param string $key
     *
     * @return mixed|null Returns null if the key does not exist.
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param mixed $value Using null for the value deletes the key.
     * @param int $ttl Number of seconds until data is automatically deleted. Use null for unlimited TTL.
     */
    public function set(string $key, $value, int $ttl = null);

    /**
     * @param string $key
     */
    public function delete(string $key);

    /**
     * Removes all values.
     */
    public function clear();
}
