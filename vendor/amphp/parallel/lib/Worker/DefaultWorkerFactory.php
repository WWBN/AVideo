<?php

namespace Amp\Parallel\Worker;

use Amp\Parallel\Context\Parallel;
use Amp\Parallel\Context\Thread;

/**
 * The built-in worker factory type.
 */
final class DefaultWorkerFactory implements WorkerFactory
{
    /** @var string */
    private $className;

    /**
     * @param string $envClassName Name of class implementing \Amp\Parallel\Worker\Environment to instigate in each
     *     worker. Defaults to \Amp\Parallel\Worker\BasicEnvironment.
     *
     * @throws \Error If the given class name does not exist or does not implement {@see Environment}.
     */
    public function __construct(string $envClassName = BasicEnvironment::class)
    {
        if (!\class_exists($envClassName)) {
            throw new \Error(\sprintf("Invalid environment class name '%s'", $envClassName));
        }

        if (!\is_subclass_of($envClassName, Environment::class)) {
            throw new \Error(\sprintf(
                "The class '%s' does not implement '%s'",
                $envClassName,
                Environment::class
            ));
        }

        $this->className = $envClassName;
    }

    /**
     * {@inheritdoc}
     *
     * The type of worker created depends on the extensions available. If multi-threading is enabled, a WorkerThread
     * will be created. If threads are not available a WorkerProcess will be created.
     */
    public function create(): Worker
    {
        if (Parallel::isSupported()) {
            return new WorkerParallel($this->className);
        }

        if (Thread::isSupported()) {
            return new WorkerThread($this->className);
        }

        return new WorkerProcess(
            $this->className,
            [],
            \getenv("AMP_PHP_BINARY") ?: (\defined("AMP_PHP_BINARY") ? \AMP_PHP_BINARY : null)
        );
    }
}
