<?php

namespace Amp\Process;

use Amp\Loop;
use Amp\Process\Internal\Posix\Runner as PosixProcessRunner;
use Amp\Process\Internal\ProcessHandle;
use Amp\Process\Internal\ProcessRunner;
use Amp\Process\Internal\ProcessStatus;
use Amp\Process\Internal\Windows\Runner as WindowsProcessRunner;
use Amp\Promise;
use function Amp\call;

final class Process
{
    /** @var ProcessRunner */
    private $processRunner;

    /** @var string */
    private $command;

    /** @var string */
    private $cwd = "";

    /** @var array */
    private $env = [];

    /** @var array */
    private $options;

    /** @var ProcessHandle */
    private $handle;

    /** @var int|null */
    private $pid;

    /**
     * @param   string|string[] $command Command to run.
     * @param   string|null     $cwd Working directory or use an empty string to use the working directory of the
     *     parent.
     * @param   mixed[]         $env Environment variables or use an empty array to inherit from the parent.
     * @param   mixed[]         $options Options for `proc_open()`.
     *
     * @throws \Error If the arguments are invalid.
     */
    public function __construct($command, string $cwd = null, array $env = [], array $options = [])
    {
        $command = \is_array($command)
            ? \implode(" ", \array_map(__NAMESPACE__ . "\\escapeArguments", $command))
            : (string) $command;

        $cwd = $cwd ?? "";

        $envVars = [];
        foreach ($env as $key => $value) {
            if (\is_array($value)) {
                throw new \Error("\$env cannot accept array values");
            }

            $envVars[(string) $key] = (string) $value;
        }

        $this->command = $command;
        $this->cwd = $cwd;
        $this->env = $envVars;
        $this->options = $options;

        $this->processRunner = Loop::getState(self::class);

        if ($this->processRunner === null) {
            $this->processRunner = IS_WINDOWS
                ? new WindowsProcessRunner
                : new PosixProcessRunner;

            Loop::setState(self::class, $this->processRunner);
        }
    }

    /**
     * Stops the process if it is still running.
     */
    public function __destruct()
    {
        if ($this->handle !== null) {
            $this->processRunner->destroy($this->handle);
        }
    }

    public function __clone()
    {
        throw new \Error("Cloning is not allowed!");
    }

    /**
     * Start the process.
     *
     * @return Promise<int> Resolves with the PID.
     *
     * @throws StatusError If the process has already been started.
     */
    public function start(): Promise
    {
        if ($this->handle) {
            throw new StatusError("Process has already been started.");
        }

        return call(function () {
            $this->handle = $this->processRunner->start($this->command, $this->cwd, $this->env, $this->options);
            return $this->pid = yield $this->handle->pidDeferred->promise();
        });
    }

    /**
     * Wait for the process to end.
     *
     * @return Promise <int> Succeeds with process exit code or fails with a ProcessException if the process is killed.
     *
     * @throws StatusError If the process has already been started.
     */
    public function join(): Promise
    {
        if (!$this->handle) {
            throw new StatusError("Process has not been started.");
        }

        return $this->processRunner->join($this->handle);
    }

    /**
     * Forcibly end the process.
     *
     * @throws StatusError If the process is not running.
     * @throws ProcessException If terminating the process fails.
     */
    public function kill()
    {
        if (!$this->isRunning()) {
            throw new StatusError("Process is not running.");
        }

        $this->processRunner->kill($this->handle);
    }

    /**
     * Send a signal signal to the process.
     *
     * @param int $signo Signal number to send to process.
     *
     * @throws StatusError If the process is not running.
     * @throws ProcessException If sending the signal fails.
     */
    public function signal(int $signo)
    {
        if (!$this->isRunning()) {
            throw new StatusError("Process is not running.");
        }

        $this->processRunner->signal($this->handle, $signo);
    }

    /**
     * Returns the PID of the child process.
     *
     * @return int
     *
     * @throws StatusError If the process has not started or has not completed starting.
     */
    public function getPid(): int
    {
        if (!$this->pid) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }

        return $this->pid;
    }

    /**
     * Returns the command to execute.
     *
     * @return string The command to execute.
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Gets the current working directory.
     *
     * @return string The current working directory an empty string if inherited from the current PHP process.
     */
    public function getWorkingDirectory(): string
    {
        if ($this->cwd === "") {
            return \getcwd() ?: "";
        }

        return $this->cwd;
    }

    /**
     * Gets the environment variables array.
     *
     * @return string[] Array of environment variables.
     */
    public function getEnv(): array
    {
        return $this->env;
    }

    /**
     * Gets the options to pass to proc_open().
     *
     * @return mixed[] Array of options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Determines if the process is still running.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->handle && $this->handle->status !== ProcessStatus::ENDED;
    }

    /**
     * Gets the process input stream (STDIN).
     *
     * @return ProcessOutputStream
     */
    public function getStdin(): ProcessOutputStream
    {
        if (!$this->handle || $this->handle->status === ProcessStatus::STARTING) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }

        return $this->handle->stdin;
    }

    /**
     * Gets the process output stream (STDOUT).
     *
     * @return ProcessInputStream
     */
    public function getStdout(): ProcessInputStream
    {
        if (!$this->handle || $this->handle->status === ProcessStatus::STARTING) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }

        return $this->handle->stdout;
    }

    /**
     * Gets the process error stream (STDERR).
     *
     * @return ProcessInputStream
     */
    public function getStderr(): ProcessInputStream
    {
        if (!$this->handle || $this->handle->status === ProcessStatus::STARTING) {
            throw new StatusError("Process has not been started or has not completed starting.");
        }

        return $this->handle->stderr;
    }

    public function __debugInfo(): array
    {
        return [
            'command' => $this->getCommand(),
            'cwd' => $this->getWorkingDirectory(),
            'env' => $this->getEnv(),
            'options' => $this->getOptions(),
            'pid' => $this->pid,
            'status' => $this->handle ? $this->handle->status : -1,
        ];
    }
}
