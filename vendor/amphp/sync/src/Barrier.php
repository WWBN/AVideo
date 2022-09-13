<?php

namespace Amp\Sync;

use Amp\Deferred;
use Amp\Promise;

/**
 * A barrier is a synchronization primitive.
 *
 * The barrier is initialized with a certain count, which can be increased and decreased until it reaches zero.
 *
 * A count of one can be used to block multiple coroutines until a certain condition is met.
 *
 * A count of N can be used to await multiple coroutines doing an action to complete.
 *
 * **Example**
 *
 * ```php
 * $barrier = new Amp\Sync\Barrier(2);
 * $barrier->arrive();
 * $barrier->arrive(); // promise returned from Barrier::await() is now resolved
 *
 * yield $barrier->await();
 * ```
 */
final class Barrier
{
    /** @var int */
    private $count;
    /** @var Deferred */
    private $deferred;

    public function __construct(int $count)
    {
        if ($count < 1) {
            throw new \Error('Count must be positive, got ' . $count);
        }

        $this->count = $count;
        $this->deferred = new Deferred();
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function arrive(int $count = 1): void
    {
        if ($count < 1) {
            throw new \Error('Count must be at least 1, got ' . $count);
        }

        if ($count > $this->count) {
            throw new \Error('Count cannot be greater than remaining count: ' . $count . ' > ' . $this->count);
        }

        $this->count -= $count;

        if ($this->count === 0) {
            $this->deferred->resolve();
        }
    }

    public function register(int $count = 1): void
    {
        if ($count < 1) {
            throw new \Error('Count must be at least 1, got ' . $count);
        }

        if ($this->count === 0) {
            throw new \Error('Can\'t increase count, because the barrier already broke');
        }

        $this->count += $count;
    }

    public function await(): Promise
    {
        return $this->deferred->promise();
    }
}
