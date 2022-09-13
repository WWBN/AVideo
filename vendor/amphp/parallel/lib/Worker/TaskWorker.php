<?php

namespace Amp\Parallel\Worker;

use Amp\Failure;
use Amp\Parallel\Context\Context;
use Amp\Parallel\Context\StatusError;
use Amp\Parallel\Sync\ChannelException;
use Amp\Promise;
use Amp\Success;
use Amp\TimeoutException;
use function Amp\call;

/**
 * Base class for most common types of task workers.
 */
abstract class TaskWorker implements Worker
{
    const SHUTDOWN_TIMEOUT = 1000;
    const ERROR_TIMEOUT = 250;

    /** @var Context */
    private $context;

    /** @var bool */
    private $started = false;

    /** @var Promise|null */
    private $pending;

    /** @var Promise|null */
    private $exitStatus;

    /**
     * @param Context $context A context running an instance of TaskRunner.
     */
    public function __construct(Context $context)
    {
        if ($context->isRunning()) {
            throw new \Error("The context was already running");
        }

        $this->context = $context;

        $context = &$this->context;
        $pending = &$this->pending;

        \register_shutdown_function(static function () use (&$context, &$pending): void {
            if ($context === null || !$context->isRunning()) {
                return;
            }

            try {
                Promise\wait(Promise\timeout(call(function () use ($context, $pending): \Generator {
                    if ($pending) {
                        yield $pending;
                    }

                    yield $context->send(0);
                    return yield $context->join();
                }), self::SHUTDOWN_TIMEOUT));
            } catch (\Throwable $exception) {
                if ($context !== null) {
                    $context->kill();
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning(): bool
    {
        // Report as running unless shutdown or crashed.
        return !$this->started || ($this->exitStatus === null && $this->context !== null && $this->context->isRunning());
    }

    /**
     * {@inheritdoc}
     */
    public function isIdle(): bool
    {
        return $this->pending === null;
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue(Task $task): Promise
    {
        if ($this->exitStatus) {
            throw new StatusError("The worker has been shut down");
        }

        $promise = $this->pending = call(function () use ($task): \Generator {
            if ($this->pending) {
                try {
                    yield $this->pending;
                } catch (\Throwable $exception) {
                    // Ignore error from prior job.
                }
            }

            if ($this->exitStatus !== null || $this->context === null) {
                throw new WorkerException("The worker was shutdown");
            }

            if (!$this->context->isRunning()) {
                if ($this->started) {
                    throw new WorkerException("The worker crashed");
                }

                $this->started = true;
                yield $this->context->start();
            }

            $job = new Internal\Job($task);

            try {
                yield $this->context->send($job);
                $result = yield $this->context->receive();
            } catch (ChannelException $exception) {
                try {
                    yield Promise\timeout($this->context->join(), self::ERROR_TIMEOUT);
                } catch (TimeoutException $timeout) {
                    $this->kill();
                    throw new WorkerException("The worker failed unexpectedly", 0, $exception);
                }

                throw new WorkerException("The worker exited unexpectedly", 0, $exception);
            }

            if (!$result instanceof Internal\TaskResult) {
                $this->kill();
                throw new WorkerException("Context did not return a task result");
            }

            if ($result->getId() !== $job->getId()) {
                $this->kill();
                throw new WorkerException("Task results returned out of order");
            }

            return $result->promise();
        });

        $promise->onResolve(function () use ($promise): void {
            if ($this->pending === $promise) {
                $this->pending = null;
            }
        });

        return $promise;
    }

    /**
     * {@inheritdoc}
     */
    public function shutdown(): Promise
    {
        if ($this->exitStatus !== null) {
            return $this->exitStatus;
        }

        if ($this->context === null || !$this->context->isRunning()) {
            return $this->exitStatus = new Success(-1); // Context crashed?
        }

        return $this->exitStatus = call(function (): \Generator {
            if ($this->pending) {
                // If a task is currently running, wait for it to finish.
                yield Promise\any([$this->pending]);
            }

            yield $this->context->send(0);

            try {
                return yield Promise\timeout($this->context->join(), self::SHUTDOWN_TIMEOUT);
            } catch (\Throwable $exception) {
                $this->context->kill();
                throw new WorkerException("Failed to gracefully shutdown worker", 0, $exception);
            } finally {
                // Null properties to free memory because the shutdown function has references to these.
                $this->context = null;
                $this->pending = null;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function kill(): void
    {
        if ($this->exitStatus !== null || $this->context === null) {
            return;
        }

        if ($this->context->isRunning()) {
            $this->context->kill();
            $this->exitStatus = new Failure(new WorkerException("The worker was killed"));
            return;
        }

        $this->exitStatus = new Success(0);

        // Null properties to free memory because the shutdown function has references to these.
        $this->context = null;
        $this->pending = null;
    }
}
