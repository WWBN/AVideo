---
title: Workers
permalink: /workers
---

## `Worker`

`Worker` provides a simple interface for executing PHP code in parallel in a separate PHP process or thread.
Classes implementing [`Task`](#task) are used to define the code to be run in parallel.

```php
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
     * Enqueues a task to be executed by the worker.
     *
     * @param Task $task The task to enqueue.
     *
     * @return \Amp\Promise<mixed> Resolves with the return value of Task::run().
     */
    public function enqueue(Task $task): Promise;

    /**
     * @return \Amp\Promise<int> Exit code.
     */
    public function shutdown(): Promise;

    /**
     * Immediately kills the context.
     */
    public function kill();
}
```

## `Task`

The `Task` interface has a single `run()` method that gets invoked in the worker to dispatch the work that needs to be done.
The `run()` method can be written using blocking code since the code is executed in a separate process or thread. The method
may also be asynchronous, returning a `Promise` or `Generator` that is run as a coroutine.

```php
<?php

namespace Amp\Parallel\Worker;

/**
 * A runnable unit of execution.
 */
interface Task
{
    /**
     * Runs the task inside the caller's context.
     *
     * Does not have to be a coroutine, can also be a regular function returning a value.
     *
     * @param Environment
     *
     * @return mixed|\Amp\Promise|\Generator
     */
    public function run(Environment $environment);
}
```

Task instances are `serialize`'d in the main process and `unserialize`'d in the worker.
That means that all data that is passed between the main process and a worker needs to be serializable.

## `Environment`

The passed `Environment` allows to persist data between multiple tasks executed by the same worker, e.g. database connections or file handles, without resorting to globals for that.
Additionally `Environment` allows setting a TTL for entries, so can be used as a cache.

```php
<?php

namespace Amp\Parallel\Worker;

interface Environment extends \ArrayAccess
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * @param string $key
     *
     * @return mixed|null Returns null if the key does not exist.
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param mixed $value Using null for the value deletes the key.
     * @param int $ttl Number of seconds until data is automatically deleted. Use null for unlimited TTL.
     */
    public function set(string $key, $value, int $ttl = null);

    /**
     * @param string $key
     */
    public function delete(string $key);

    /**
     * Removes all values.
     */
    public function clear();
}
```
