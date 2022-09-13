<?php

namespace Amp\Sync;

use Amp\Promise;

/**
 * A non-blocking counting semaphore.
 *
 * Objects that implement this interface should guarantee that all operations are atomic. Implementations do not have to
 * guarantee that acquiring a lock is first-come, first serve.
 */
interface Semaphore
{
    /**
     * Acquires a lock on the semaphore.
     *
     * @return Promise<Lock> Resolves with an integer keyed lock object. Identifiers returned by the
     *    locks should be 0-indexed. Releasing an identifier MUST make that same identifier available. May fail with
     *    a SyncException if an error occurs when attempting to obtain the lock (e.g. a shared memory segment closed).
     */
    public function acquire(): Promise;
}
