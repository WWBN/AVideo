<?php

namespace Amp\Sync;

use Amp\Promise;

/**
 * A non-blocking synchronization primitive that can be used for mutual exclusion across contexts based on keys.
 *
 * Objects that implement this interface should guarantee that all operations are atomic. Implementations do not have to
 * guarantee that acquiring a lock is first-come, first serve.
 */
interface KeyedMutex extends KeyedSemaphore
{
    /**
     * Acquires a lock on the mutex.
     *
     * @param string $key Lock key
     *
     * @return Promise<Lock> Resolves with a lock object with an ID of 0. May fail with a SyncException
     *     if an error occurs when attempting to obtain the lock (e.g. a shared memory segment closed).
     */
    public function acquire(string $key): Promise;
}
