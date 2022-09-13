<?php

namespace Amp\Parallel\Worker;

/**
 * Common interface for exceptions thrown when Task::run() throws an exception when being executed in a worker.
 */
interface TaskFailureThrowable extends \Throwable
{
    /**
     * @return string Original exception class name.
     */
    public function getOriginalClassName(): string;

    /**
     * @return string Original exception message.
     */
    public function getOriginalMessage(): string;

    /**
     * @return int|string Original exception code.
     */
    public function getOriginalCode();

    /**
     * Returns the original exception stack trace.
     *
     * @return array Same as {@see Throwable::getTrace()}, except all function arguments are formatted as strings.
     */
    public function getOriginalTrace(): array;

    /**
     * Original backtrace flattened to a human-readable string.
     *
     * @return string
     */
    public function getOriginalTraceAsString(): string;
}
