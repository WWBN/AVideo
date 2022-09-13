<?php

namespace Amp\Parallel\Worker;

/**
 * @deprecated TaskFailureException will be thrown from failed Tasks instead of this class.
 */
class TaskException extends \Exception
{
    /** @var string Class name of exception thrown from task. */
    private $name;

    /** @var string Stack trace of the exception thrown from task. */
    private $trace;

    /**
     * @param string          $name     The exception class name.
     * @param string          $message  The panic message.
     * @param string          $trace    The panic stack trace.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(string $name, string $message = '', string $trace = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->name = $name;
        $this->trace = $trace;
    }

    /**
     * @deprecated Use TaskFailureThrowable::getOriginalClassName() instead.
     *
     * Returns the class name of the exception thrown from the task.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated Use TaskFailureThrowable::getOriginalTraceAsString() instead.
     *
     * Gets the stack trace at the point the exception was thrown in the task.
     *
     * @return string
     */
    public function getWorkerTrace(): string
    {
        return $this->trace;
    }
}
