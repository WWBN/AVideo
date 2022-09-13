<?php

namespace Amp\Parallel\Worker;

/**
 * A worker process that executes task objects.
 */
final class WorkerProcess extends TaskWorker
{
    const SCRIPT_PATH = __DIR__ . "/Internal/worker-process.php";

    /**
     * @param string      $envClassName  Name of class implementing \Amp\Parallel\Worker\Environment to instigate.
     *     Defaults to \Amp\Parallel\Worker\BasicEnvironment.
     * @param mixed[]     $env           Array of environment variables to pass to the worker. Empty array inherits from the current
     *     PHP process. See the $env parameter of \Amp\Process\Process::__construct().
     * @param string|null $binary        Path to PHP binary. Null will attempt to automatically locate the binary.
     * @param string|null $bootstrapPath Path to custom bootstrap file.
     *
     * @throws \Error If the PHP binary path given cannot be found or is not executable.
     */
    public function __construct(
        string $envClassName = BasicEnvironment::class,
        array $env = [],
        string $binary = null,
        string $bootstrapPath = null
    ) {
        $script = [
            self::SCRIPT_PATH,
            $envClassName,
        ];

        if ($bootstrapPath !== null) {
            $script[] = $bootstrapPath;
        }

        parent::__construct(new Internal\WorkerProcess($script, $env, $binary));
    }
}
