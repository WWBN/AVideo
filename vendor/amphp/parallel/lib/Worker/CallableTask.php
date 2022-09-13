<?php

namespace Amp\Parallel\Worker;

/**
 * Task implementation dispatching a simple callable.
 */
final class CallableTask implements Task
{
    /** @var callable */
    private $callable;

    /** @var mixed[] */
    private $args;

    /**
     * @param callable $callable Callable will be serialized.
     * @param mixed    $args Arguments to pass to the function. Must be serializable.
     */
    public function __construct(callable $callable, array $args)
    {
        $this->callable = $callable;
        $this->args = $args;
    }

    public function run(Environment $environment)
    {
        if ($this->callable instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance as a callable, the class must be autoloadable');
        }

        if (\is_array($this->callable) && ($this->callable[0] ?? null) instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance method as a callable, the class must be autoloadable');
        }

        if (!\is_callable($this->callable)) {
            $message = 'User-defined functions must be autoloadable (that is, defined in a file autoloaded by composer)';
            if (\is_string($this->callable)) {
                $message .= \sprintf("; unable to load function '%s'", $this->callable);
            }

            throw new \Error($message);
        }

        return ($this->callable)(...$this->args);
    }
}
