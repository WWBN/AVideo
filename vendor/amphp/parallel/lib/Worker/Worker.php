<?php

namespace Amp\Parallel\Worker;

use Amp\Promise;

/**
 * An interface for a parallel worker thread that runs a queue of tasks.
 */
interface Worker
{
    /**
     * Checks if the worker is running.
     *
     * @return bool True if the worker is running, otherwise false.
     */
    public function isRunning(): bool;

    /**
     * Checks if the worker is currently idle.
     *
     * @return bool
     */
    public function isIdle(): bool;

    /**
     * Enqueues a {@see Task} to be executed by the worker.
     *
     * @param Task $task The task to enqueue.
     *
     * @return Promise<mixed> Resolves with the return value of {@see Task::run()}.
     *
     * @throws TaskFailureThrowable Promise fails if {@see Task::run()} throws an exception.
     */
    public function enqueue(Task $task): Promise;

    /**
     * @return Promise<int> Resolves with the worker exit code.
     */
    public function shutdown(): Promise;

    /**
     * Immediately kills the context.
     */
    public function kill();
}
