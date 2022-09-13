<?php

namespace Amp\Sync;

use Amp\Promise;

/**
 * A thread-safe, asynchronous mutex using the pthreads locking mechanism.
 *
 * Compatible with POSIX systems and Microsoft Windows.
 *
 * @deprecated ext-pthreads development has been halted, see https://github.com/krakjoe/pthreads/issues/929
 */
class ThreadedMutex implements Mutex
{
    /** @var Internal\MutexStorage */
    private $mutex;

    /**
     * Creates a new threaded mutex.
     */
    public function __construct()
    {
        $this->mutex = new Internal\MutexStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function acquire(): Promise
    {
        return $this->mutex->acquire();
    }
}
