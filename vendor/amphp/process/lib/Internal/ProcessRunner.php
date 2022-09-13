<?php

namespace Amp\Process\Internal;

use Amp\Process\ProcessException;
use Amp\Promise;

interface ProcessRunner
{
    /**
     * Start a process using the supplied parameters.
     *
     * @param string      $command The command to execute.
     * @param string|null $cwd The working directory for the child process.
     * @param array       $env Environment variables to pass to the child process.
     * @param array       $options `proc_open()` options.
     *
     * @return ProcessHandle
     *
     * @throws ProcessException If starting the process fails.
     */
    public function start(string $command, string $cwd = null, array $env = [], array $options = []): ProcessHandle;

    /**
     * Wait for the child process to end.
     *
     * @param ProcessHandle $handle The process descriptor.
     *
     * @return Promise <int> Succeeds with exit code of the process or fails if the process is killed.
     */
    public function join(ProcessHandle $handle): Promise;

    /**
     * Forcibly end the child process.
     *
     * @param ProcessHandle $handle The process descriptor.
     *
     * @throws ProcessException If terminating the process fails.
     */
    public function kill(ProcessHandle $handle);

    /**
     * Send a signal signal to the child process.
     *
     * @param ProcessHandle $handle The process descriptor.
     * @param int           $signo Signal number to send to process.
     *
     * @throws ProcessException If sending the signal fails.
     */
    public function signal(ProcessHandle $handle, int $signo);

    /**
     * Release all resources held by the process handle.
     *
     * @param ProcessHandle $handle The process descriptor.
     */
    public function destroy(ProcessHandle $handle);
}
