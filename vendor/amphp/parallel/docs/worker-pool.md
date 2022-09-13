---
title: Worker Pool
permalink: /worker-pool
---
The easiest way to use workers is through a worker pool. `Pool` implements `Worker`, so worker pools can be used to enqueue
tasks in the same way as a worker, but rather than using a single worker process or thread, the pool uses multiple workers
to execute tasks. This allows multiple tasks to be executed simultaneously.

## `Pool`

The `Pool` interface extends [`Worker`](./workers#worker), adding methods to get information about the pool or pull a single `Worker` instance
out of the pool. A pool uses multiple `Worker` instances to execute enqueued [tasks](./workers#task).

```php
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
     * @return Worker
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
```

If a set of tasks should be run within a single worker, use the `Pool::getWorker()` method to pull a single worker from the pool.
The worker is automatically returned to the pool when the instance returned is destroyed.

### Global worker pool

A global worker pool is available and can be set using the function `Amp\Parallel\Worker\pool(?Pool $pool = null)`.
Passing an instance of `Pool` will set the global pool to the given instance. Invoking the function without an instance will return
the current global instance.
