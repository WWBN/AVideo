<?php

namespace Amp\Sync;

use Amp\Promise;
use function Amp\call;

final class LocalKeyedSemaphore implements KeyedSemaphore
{
    /** @var LocalSemaphore[] */
    private $semaphore = [];

    /** @var int[] */
    private $locks = [];

    /** @var int */
    private $maxLocks;

    public function __construct(int $maxLocks)
    {
        $this->maxLocks = $maxLocks;
    }

    public function acquire(string $key): Promise
    {
        if (!isset($this->semaphore[$key])) {
            $this->semaphore[$key] = new LocalSemaphore($this->maxLocks);
            $this->locks[$key] = 0;
        }

        return call(function () use ($key) {
            $this->locks[$key]++;

            /** @var Lock $lock */
            $lock = yield $this->semaphore[$key]->acquire();

            return new Lock(0, function () use ($lock, $key) {
                if (--$this->locks[$key] === 0) {
                    unset($this->semaphore[$key], $this->locks[$key]);
                }

                $lock->release();
            });
        });
    }
}
