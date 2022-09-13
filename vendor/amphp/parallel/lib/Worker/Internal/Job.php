<?php

namespace Amp\Parallel\Worker\Internal;

use Amp\Parallel\Worker\Task;

/** @internal */
final class Job
{
    /** @var string */
    private $id;

    /** @var Task */
    private $task;

    public function __construct(Task $task)
    {
        static $id = 'a';

        $this->task = $task;
        $this->id = $id++;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTask(): Task
    {
        // Classes that cannot be autoloaded will be unserialized as an instance of __PHP_Incomplete_Class.
        if ($this->task instanceof \__PHP_Incomplete_Class) {
            throw new \Error(\sprintf("Classes implementing %s must be autoloadable by the Composer autoloader", Task::class));
        }

        return $this->task;
    }
}
