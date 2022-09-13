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
     * @param \Amp\Parallel\Worker\Environment
     *
     * @return mixed|\Amp\Promise|\Generator
     */
    public function run(Environment $environment);
}
