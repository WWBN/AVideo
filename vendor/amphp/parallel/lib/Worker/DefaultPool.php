<?php

namespace Amp\Parallel\Worker;

use Amp\Parallel\Context\StatusError;
use Amp\Promise;
use function Amp\asyncCall;

/**
 * Provides a pool of workers that can be used to execute multiple tasks asynchronously.
 *
 * A worker pool is a collection of worker threads that can perform multiple
 * tasks simultaneously. The load on each worker is balanced such that tasks
 * are completed as soon as possible and workers are used efficiently.
 */
final class DefaultPool implements Pool
{
    /** @var bool Indicates if the pool is currently running. */
    private $running = true;

    /** @var int The maximum number of workers the pool should spawn. */
    private $maxSize;

    /** @var WorkerFactory A worker factory to be used to create new workers. */
    private $factory;

    /** @var \SplObjectStorage A collection of all workers in the pool. */
    private $workers;

    /** @var \SplQueue A collection of idle workers. */
    private $idleWorkers;

    /** @var \SplQueue A queue of workers that have been assigned to tasks. */
    private $busyQueue;

    /** @var \Closure */
    private $push;

    /** @var Promise|null */
    private $exitStatus;

    /**
     * Creates a new worker pool.
     *
     * @param int $maxSize The maximum number of workers the pool should spawn.
     *     Defaults to `Pool::DEFAULT_MAX_SIZE`.
     * @param WorkerFactory|null $factory A worker factory to be used to create
     *     new workers.
     *
     * @throws \Error
     */
    public function __construct(int $maxSize = self::DEFAULT_MAX_SIZE, WorkerFactory $factory = null)
    {
        if ($maxSize < 0) {
            throw new \Error("Maximum size must be a non-negative integer");
        }

        $this->maxSize = $maxSize;

        // Use the global factory if none is given.
        $this->factory = $factory ?: factory();

        $this->workers = new \SplObjectStorage;
        $this->idleWorkers = new \SplQueue;
        $this->busyQueue = new \SplQueue;

        $workers = $this->workers;
        $idleWorkers = $this->idleWorkers;
        $busyQueue = $this->busyQueue;

        $this->push = static function (Worker $worker) use ($workers, $idleWorkers, $busyQueue): void {
            if (!$workers->contains($worker) || ($workers[$worker] -= 1) > 0) {
                return;
            }

            // Worker is completely idle, remove from busy queue and add to idle queue.
            foreach ($busyQueue as $key => $busy) {
                if ($busy === $worker) {
                    unset($busyQueue[$key]);
                    break;
                }
            }

            $idleWorkers->push($worker);
        };
    }

    public function __destruct()
    {
        if ($this->isRunning()) {
            $this->kill();
        }
    }

    /**
     * Checks if the pool is running.
     *
     * @return bool True if the pool is running, otherwise false.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Checks if the pool has any idle workers.
     *
     * @return bool True if the pool has at least one idle worker, otherwise false.
     */
    public function isIdle(): bool
    {
        return $this->idleWorkers->count() > 0 || $this->workers->count() === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkerCount(): int
    {
        return $this->workers->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdleWorkerCount(): int
    {
        return $this->idleWorkers->count();
    }

    /**
     * Enqueues a {@see Task} to be executed by the worker pool.
     *
     * @param Task $task The task to enqueue.
     *
     * @return Promise<mixed> The return value of Task::run().
     *
     * @throws StatusError If the pool has been shutdown.
     * @throws TaskFailureThrowable If the task throws an exception.
     */
    public function enqueue(Task $task): Promise
    {
        $worker = $this->pull();

        $promise = $worker->enqueue($task);
        $promise->onResolve(function () use ($worker): void {
            ($this->push)($worker);
        });
        return $promise;
    }

    /**
     * Shuts down the pool and all workers in it.
     *
     * @return Promise<int[]> Array of exit status from all workers.
     *
     * @throws StatusError If the pool has not been started.
     */
    public function shutdown(): Promise
    {
        if ($this->exitStatus) {
            return $this->exitStatus;
        }

        $this->running = false;

        $shutdowns = [];
        foreach ($this->workers as $worker) {
            if ($worker->isRunning()) {
                $shutdowns[] = $worker->shutdown();
            }
        }

        return $this->exitStatus = Promise\all($shutdowns);
    }

    /**
     * Kills all workers in the pool and halts the worker pool.
     */
    public function kill(): void
    {
        $this->running = false;

        foreach ($this->workers as $worker) {
            \assert($worker instanceof Worker);
            if ($worker->isRunning()) {
                $worker->kill();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWorker(): Worker
    {
        return new Internal\PooledWorker($this->pull(), $this->push);
    }

    /**
     * Pulls a worker from the pool.
     *
     * @return Worker
     * @throws StatusError
     */
    private function pull(): Worker
    {
        if (!$this->isRunning()) {
            throw new StatusError("The pool was shutdown");
        }

        do {
            if ($this->idleWorkers->isEmpty()) {
                if ($this->getWorkerCount() >= $this->maxSize) {
                    // All possible workers busy, so shift from head (will be pushed back onto tail below).
                    $worker = $this->busyQueue->shift();
                } else {
                    // Max worker count has not been reached, so create another worker.
                    $worker = $this->factory->create();
                    if (!$worker->isRunning()) {
                        throw new WorkerException('Worker factory did not create a viable worker');
                    }
                    $this->workers->attach($worker, 0);
                    break;
                }
            } else {
                // Shift a worker off the idle queue.
                $worker = $this->idleWorkers->shift();
            }

            \assert($worker instanceof Worker);

            if ($worker->isRunning()) {
                break;
            }

            // Worker crashed; trigger error and remove it from the pool.

            asyncCall(function () use ($worker): \Generator {
                try {
                    $code = yield $worker->shutdown();
                    \trigger_error('Worker in pool exited unexpectedly with code ' . $code, \E_USER_WARNING);
                } catch (\Throwable $exception) {
                    \trigger_error(
                        'Worker in pool crashed with exception on shutdown: ' . $exception->getMessage(),
                        \E_USER_WARNING
                    );
                }
            });

            $this->workers->detach($worker);
        } while (true);

        $this->busyQueue->push($worker);
        $this->workers[$worker] += 1;

        return $worker;
    }
}
