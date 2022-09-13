<?php

namespace Amp\Sync;

/**
 * A handle on an acquired lock from a synchronization object.
 *
 * This object is not thread-safe; after acquiring a lock from a mutex or
 * semaphore, the lock should reside in the same thread or process until it is
 * released.
 */
class Lock
{
    /** @var callable|null The function to be called on release or null if the lock has been released. */
    private $releaser;

    /** @var int */
    private $id;

    /**
     * Creates a new lock permit object.
     *
     * @param int $id The lock identifier.
     * @param callable(self): void $releaser A function to be called upon release.
     */
    public function __construct(int $id, callable $releaser)
    {
        $this->id = $id;
        $this->releaser = $releaser;
    }

    /**
     * Checks if the lock has already been released.
     *
     * @return bool True if the lock has already been released, otherwise false.
     */
    public function isReleased(): bool
    {
        return !$this->releaser;
    }

    /**
     * @return int Lock identifier.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Releases the lock. No-op if the lock has already been released.
     */
    public function release()
    {
        if (!$this->releaser) {
            return;
        }

        // Invoke the releaser function given to us by the synchronization source
        // to release the lock.
        $releaser = $this->releaser;
        $this->releaser = null;
        $releaser($this);
    }

    /**
     * Releases the lock when there are no more references to it.
     */
    public function __destruct()
    {
        if (!$this->isReleased()) {
            $this->release();
        }
    }
}
