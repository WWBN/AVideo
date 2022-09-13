<?php

namespace Amp\Parallel\Worker;

/**
 * An interface for worker pools.
 */
interface Pool extends Worker
{
    /** @var int The default maximum pool size. */
    const DEFAULT_MAX_SIZE = 32;

    /**
     * Gets a worker from the pool. The worker is marked as busy and will only be reused if the pool runs out of
     * idle workers. The worker will be automatically marked as idle once no references to the returned worker remain.
     *
     * @return \Amp\Parallel\Worker\Worker
     *
     * @throws \Amp\Parallel\Context\StatusError If the queue is not running.
     */
    public function getWorker(): Worker;

    /**
     * Gets the number of workers currently running in the pool.
     *
     * @return int The number of workers.
     */
    public function getWorkerCount(): int;

    /**
     * Gets the number of workers that are currently idle.
     *
     * @return int The number of idle workers.
     */
    public function getIdleWorkerCount(): int;

    /**
     * Gets the maximum number of workers the pool may spawn to handle concurrent tasks.
     *
     * @return int The maximum number of workers.
     */
    public function getMaxSize(): int;
}
