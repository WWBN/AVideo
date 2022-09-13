<?php

namespace Amp\Sync;

use Amp\Promise;

/**
 * A non-blocking synchronization primitive that can be used for mutual exclusion across contexts.
 *
 * Objects that implement this interface should guarantee that all operations are atomic. Implementations do not have to
 * guarantee that acquiring a lock is first-come, first serve.
 */
interface Mutex extends Semaphore
{
    /**
     * Acquires a lock on the mutex.
     *
     * @return Promise<Lock> Resolves with a lock object with an ID of 0. May fail with a SyncException
     *     if an error occurs when attempting to obtain the lock (e.g. a shared memory segment closed).
     */
    public function acquire(): Promise;
}
