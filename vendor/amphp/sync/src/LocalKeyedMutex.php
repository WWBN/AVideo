<?php

namespace Amp\Sync;

use Amp\Promise;
use function Amp\call;

final class LocalKeyedMutex implements KeyedMutex
{
    /** @var LocalMutex[] */
    private $mutex = [];

    /** @var int[] */
    private $locks = [];

    public function acquire(string $key): Promise
    {
        if (!isset($this->mutex[$key])) {
            $this->mutex[$key] = new LocalMutex;
            $this->locks[$key] = 0;
        }

        return call(function () use ($key) {
            $this->locks[$key]++;

            /** @var Lock $lock */
            $lock = yield $this->mutex[$key]->acquire();

            return new Lock(0, function () use ($lock, $key) {
                if (--$this->locks[$key] === 0) {
                    unset($this->mutex[$key], $this->locks[$key]);
                }

                $lock->release();
            });
        });
    }
}
