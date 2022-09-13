<?php

namespace Amp\Parallel\Worker;

use Amp\Loop;
use Amp\Struct;

final class BasicEnvironment implements Environment
{
    /** @var array */
    private $data = [];

    /** @var \SplPriorityQueue */
    private $queue;

    /** @var string */
    private $timer;

    public function __construct()
    {
        $this->queue = $queue = new \SplPriorityQueue;
        $data = &$this->data;

        $this->timer = Loop::repeat(1000, static function (string $watcherId) use ($queue, &$data): void {
            $time = \time();
            while (!$queue->isEmpty()) {
                list($key, $expiration) = $queue->top();

                if (!isset($data[$key])) {
                    // Item removed.
                    $queue->extract();
                    continue;
                }

                $struct = $data[$key];

                if ($struct->expire === 0) {
                    // Item was set again without a TTL.
                    $queue->extract();
                    continue;
                }

                if ($struct->expire !== $expiration) {
                    // Expiration changed or TTL updated.
                    $queue->extract();
                    continue;
                }

                if ($time < $struct->expire) {
                    // Item at top has not expired, break out of loop.
                    break;
                }

                unset($data[$key]);

                $queue->extract();
            }

            if ($queue->isEmpty()) {
                Loop::disable($watcherId);
            }
        });

        Loop::disable($this->timer);
        Loop::unreference($this->timer);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     *
     * @return mixed|null Returns null if the key does not exist.
     */
    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }

        $struct = $this->data[$key];

        if ($struct->ttl !== null) {
            $expire = \time() + $struct->ttl;
            if ($struct->expire < $expire) {
                $struct->expire = $expire;
                $this->queue->insert([$key, $struct->expire], -$struct->expire);
            }
        }

        return $struct->data;
    }

    /**
     * @param string $key
     * @param mixed $value Using null for the value deletes the key.
     * @param int $ttl Number of seconds until data is automatically deleted. Use null for unlimited TTL.
     *
     * @throws \Error If the time-to-live is not a positive integer.
     */
    public function set(string $key, $value, int $ttl = null): void
    {
        if ($value === null) {
            $this->delete($key);
            return;
        }

        if ($ttl !== null && $ttl <= 0) {
            throw new \Error("The time-to-live must be a positive integer or null");
        }

        $struct = new class {
            use Struct;
            public $data;
            public $expire = 0;
            public $ttl;
        };

        $struct->data = $value;

        if ($ttl !== null) {
            $struct->ttl = $ttl;
            $struct->expire = \time() + $ttl;
            $this->queue->insert([$key, $struct->expire], -$struct->expire);

            Loop::enable($this->timer);
        }

        $this->data[$key] = $struct;
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Alias of exists().
     *
     * @param $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->exists($key);
    }

    /**
     * Alias of get().
     *
     * @param string $key
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Alias of set() with $ttl = null.
     *
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Alias of delete().
     *
     * @param string $key
     */
    public function offsetUnset($key): void
    {
        $this->delete($key);
    }

    /**
     * Removes all values.
     */
    public function clear(): void
    {
        $this->data = [];

        Loop::disable($this->timer);
        $this->queue = new \SplPriorityQueue;
    }
}
