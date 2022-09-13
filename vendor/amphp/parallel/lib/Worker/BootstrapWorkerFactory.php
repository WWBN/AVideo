<?php

namespace Amp\Parallel\Worker;

use Amp\Parallel\Context\Parallel;
use Amp\Parallel\Context\Thread;

/**
 * Worker factory that includes a custom bootstrap file after initializing the worker.
 */
final class BootstrapWorkerFactory implements WorkerFactory
{
    /** @var string */
    private $bootstrapPath;

    /** @var string */
    private $className;

    /**
     * @param string $bootstrapFilePath Path to custom bootstrap file.
     * @param string $envClassName      Name of class implementing \Amp\Parallel\Worker\Environment to instigate in each
     *     worker. Defaults to \Amp\Parallel\Worker\BasicEnvironment.
     *
     * @throws \Error If the given class name does not exist or does not implement {@see Environment}.
     */
    public function __construct(string $bootstrapFilePath, string $envClassName = BasicEnvironment::class)
    {
        if (!\file_exists($bootstrapFilePath)) {
            throw new \Error(\sprintf("No file found at autoload path given '%s'", $bootstrapFilePath));
        }

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

        $this->bootstrapPath = $bootstrapFilePath;
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
            return new WorkerParallel($this->className, $this->bootstrapPath);
        }

        if (Thread::isSupported()) {
            return new WorkerThread($this->className, $this->bootstrapPath);
        }

        return new WorkerProcess(
            $this->className,
            [],
            \getenv("AMP_PHP_BINARY") ?: (\defined("AMP_PHP_BINARY") ? \AMP_PHP_BINARY : null),
            $this->bootstrapPath
        );
    }
}
