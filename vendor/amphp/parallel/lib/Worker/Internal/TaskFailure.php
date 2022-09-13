<?php

namespace Amp\Parallel\Worker\Internal;

use Amp\Failure;
use Amp\Parallel\Sync;
use Amp\Parallel\Worker\TaskFailureError;
use Amp\Parallel\Worker\TaskFailureException;
use Amp\Parallel\Worker\TaskFailureThrowable;
use Amp\Promise;

/** @internal */
final class TaskFailure extends TaskResult
{
    const PARENT_EXCEPTION = 0;
    const PARENT_ERROR = 1;

    /** @var string */
    private $type;

    /** @var int */
    private $parent;

    /** @var string */
    private $message;

    /** @var int|string */
    private $code;

    /** @var string[] */
    private $trace;

    /** @var self|null */
    private $previous;

    public function __construct(string $id, \Throwable $exception)
    {
        parent::__construct($id);
        $this->type = \get_class($exception);
        $this->parent = $exception instanceof \Error ? self::PARENT_ERROR : self::PARENT_EXCEPTION;
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
        $this->trace = Sync\flattenThrowableBacktrace($exception);

        if ($previous = $exception->getPrevious()) {
            $this->previous = new self($id, $previous);
        }
    }

    public function promise(): Promise
    {
        return new Failure($this->createException());
    }

    private function createException(): TaskFailureThrowable
    {
        $previous = $this->previous ? $this->previous->createException() : null;

        if ($this->parent === self::PARENT_ERROR) {
            return new TaskFailureError($this->type, $this->message, $this->code, $this->trace, $previous);
        }

        return new TaskFailureException($this->type, $this->message, $this->code, $this->trace, $previous);
    }
}
