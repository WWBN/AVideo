<?php

namespace Amp\Parallel\Context;

use Amp\Loop;
use Amp\Parallel\Sync\ChannelException;
use Amp\Parallel\Sync\ChannelledSocket;
use Amp\Parallel\Sync\ExitFailure;
use Amp\Parallel\Sync\ExitResult;
use Amp\Parallel\Sync\ExitSuccess;
use Amp\Parallel\Sync\SerializationException;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\TimeoutException;
use parallel\Runtime;
use function Amp\call;

/**
 * Implements an execution context using native threads provided by the parallel extension.
 */
final class Parallel implements Context
{
    const EXIT_CHECK_FREQUENCY = 250;
    const KEY_LENGTH = 32;

    /** @var string|null */
    private static $autoloadPath;

    /** @var int Next thread ID. */
    private static $nextId = 1;

    /** @var Internal\ProcessHub */
    private $hub;

    /** @var int|null */
    private $id;

    /** @var Runtime|null */
    private $runtime;

    /** @var ChannelledSocket|null A channel for communicating with the parallel thread. */
    private $channel;

    /** @var string Script path. */
    private $script;

    /** @var string[] */
    private $args = [];

    /** @var int */
    private $oid = 0;

    /** @var bool */
    private $killed = false;

    /**
     * Checks if threading is enabled.
     *
     * @return bool True if threading is enabled, otherwise false.
     */
    public static function isSupported(): bool
    {
        return \extension_loaded('parallel');
    }

    /**
     * Creates and starts a new thread.
     *
     * @param string|array $script Path to PHP script or array with first element as path and following elements options
     *     to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
     *
     * @return Promise<Thread> The thread object that was spawned.
     */
    public static function run($script): Promise
    {
        $thread = new self($script);
        return call(function () use ($thread): \Generator {
            yield $thread->start();
            return $thread;
        });
    }

    /**
     * @param string|array $script Path to PHP script or array with first element as path and following elements options
     *     to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
     *
     * @throws \Error Thrown if the pthreads extension is not available.
     */
    public function __construct($script)
    {
        if (!self::isSupported()) {
            throw new \Error("The parallel extension is required to create parallel threads.");
        }

        $this->hub = Loop::getState(self::class);
        if (!$this->hub instanceof Internal\ParallelHub) {
            $this->hub = new Internal\ParallelHub;
            Loop::setState(self::class, $this->hub);
        }

        if (\is_array($script)) {
            $this->script = (string) \array_shift($script);
            $this->args = \array_values(\array_map("strval", $script));
        } else {
            $this->script = (string) $script;
        }

        if (self::$autoloadPath === null) {
            $paths = [
                \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . "vendor" . \DIRECTORY_SEPARATOR . "autoload.php",
                \dirname(__DIR__, 4) . \DIRECTORY_SEPARATOR . "autoload.php",
            ];

            foreach ($paths as $path) {
                if (\file_exists($path)) {
                    self::$autoloadPath = $path;
                    break;
                }
            }

            if (self::$autoloadPath === null) {
                throw new \Error("Could not locate autoload.php");
            }
        }
    }

    /**
     * Returns the thread to the condition before starting. The new thread can be started and run independently of the
     * first thread.
     */
    public function __clone()
    {
        $this->runtime = null;
        $this->channel = null;
        $this->id = null;
        $this->oid = 0;
        $this->killed = false;
    }

    /**
     * Kills the thread if it is still running.
     *
     * @throws \Amp\Parallel\Context\ContextException
     */
    public function __destruct()
    {
        if (\getmypid() === $this->oid) {
            $this->kill();
        }
    }

    /**
     * Checks if the context is running.
     *
     * @return bool True if the context is running, otherwise false.
     */
    public function isRunning(): bool
    {
        return $this->channel !== null;
    }

    /**
     * Spawns the thread and begins the thread's execution.
     *
     * @return Promise<int> Resolved once the thread has started.
     *
     * @throws \Amp\Parallel\Context\StatusError If the thread has already been started.
     * @throws \Amp\Parallel\Context\ContextException If starting the thread was unsuccessful.
     */
    public function start(): Promise
    {
        if ($this->oid !== 0) {
            throw new StatusError('The thread has already been started.');
        }

        $this->oid = \getmypid();

        $this->runtime = new Runtime(self::$autoloadPath);

        $this->id = self::$nextId++;

        $future = $this->runtime->run(static function (int $id, string $uri, string $key, string $path, array $argv): int {
            // @codeCoverageIgnoreStart
            // Only executed in thread.
            \define("AMP_CONTEXT", "parallel");
            \define("AMP_CONTEXT_ID", $id);

            if (!$socket = \stream_socket_client($uri, $errno, $errstr, 5, \STREAM_CLIENT_CONNECT)) {
                \trigger_error("Could not connect to IPC socket", E_USER_ERROR);
                return 1;
            }

            $channel = new ChannelledSocket($socket, $socket);

            try {
                Promise\wait($channel->send($key));
            } catch (\Throwable $exception) {
                \trigger_error("Could not send key to parent", E_USER_ERROR);
                return 1;
            }

            try {
                Loop::unreference(Loop::repeat(self::EXIT_CHECK_FREQUENCY, function (): void {
                    // Timer to give the chance for the PHP VM to be interrupted by Runtime::kill(), since system calls such as
                    // select() will not be interrupted.
                }));

                try {
                    if (!\is_file($path)) {
                        throw new \Error(\sprintf("No script found at '%s' (be sure to provide the full path to the script)", $path));
                    }

                    $argc = \array_unshift($argv, $path);

                    try {
                        // Protect current scope by requiring script within another function.
                        $callable = (function () use ($argc, $argv): callable { // Using $argc so it is available to the required script.
                            return require $argv[0];
                        })->bindTo(null, null)();
                    } catch (\TypeError $exception) {
                        throw new \Error(\sprintf("Script '%s' did not return a callable function", $path), 0, $exception);
                    } catch (\ParseError $exception) {
                        throw new \Error(\sprintf("Script '%s' contains a parse error", $path), 0, $exception);
                    }

                    $result = new ExitSuccess(Promise\wait(call($callable, $channel)));
                } catch (\Throwable $exception) {
                    $result = new ExitFailure($exception);
                }

                Promise\wait(call(function () use ($channel, $result): \Generator {
                    try {
                        yield $channel->send($result);
                    } catch (SerializationException $exception) {
                        // Serializing the result failed. Send the reason why.
                        yield $channel->send(new ExitFailure($exception));
                    }
                }));
            } catch (\Throwable $exception) {
                \trigger_error("Could not send result to parent; be sure to shutdown the child before ending the parent", E_USER_ERROR);
                return 1;
            } finally {
                $channel->close();
            }

            return 0;
        // @codeCoverageIgnoreEnd
        }, [
            $this->id,
            $this->hub->getUri(),
            $this->hub->generateKey($this->id, self::KEY_LENGTH),
            $this->script,
            $this->args
        ]);

        return call(function () use ($future): \Generator {
            try {
                $this->channel = yield $this->hub->accept($this->id);
                $this->hub->add($this->id, $this->channel, $future);
            } catch (\Throwable $exception) {
                $this->kill();
                throw new ContextException("Starting the parallel runtime failed", 0, $exception);
            }

            if ($this->killed) {
                $this->kill();
            }

            return $this->id;
        });
    }

    /**
     * Immediately kills the context.
     */
    public function kill(): void
    {
        $this->killed = true;

        if ($this->runtime !== null) {
            try {
                $this->runtime->kill();
            } finally {
                $this->close();
            }
        }
    }

    /**
     * Closes channel and socket if still open.
     */
    private function close(): void
    {
        $this->runtime = null;

        if ($this->channel !== null) {
            $this->channel->close();
        }

        $this->channel = null;

        $this->hub->remove($this->id);
    }

    /**
     * Gets a promise that resolves when the context ends and joins with the
     * parent context.
     *
     * @return \Amp\Promise<mixed>
     *
     * @throws StatusError Thrown if the context has not been started.
     * @throws SynchronizationError Thrown if an exit status object is not received.
     * @throws ContextException If the context stops responding.
     */
    public function join(): Promise
    {
        if ($this->channel === null) {
            throw new StatusError('The thread has not been started or has already finished.');
        }

        return call(function (): \Generator {
            try {
                $response = yield $this->channel->receive();
                $this->close();
            } catch (\Throwable $exception) {
                $this->kill();
                throw new ContextException("Failed to receive result from thread", 0, $exception);
            }

            if (!$response instanceof ExitResult) {
                $this->kill();
                throw new SynchronizationError('Did not receive an exit result from thread.');
            }

            return $response->getResult();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function receive(): Promise
    {
        if ($this->channel === null) {
            throw new StatusError('The thread has not been started.');
        }

        return call(function (): \Generator {
            try {
                $data = yield $this->channel->receive();
            } catch (ChannelException $e) {
                throw new ContextException("The thread stopped responding, potentially due to a fatal error or calling exit", 0, $e);
            }

            if ($data instanceof ExitResult) {
                $data = $data->getResult();
                throw new SynchronizationError(\sprintf(
                    'Thread unexpectedly exited with result of type: %s',
                    \is_object($data) ? \get_class($data) : \gettype($data)
                ));
            }

            return $data;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function send($data): Promise
    {
        if ($this->channel === null) {
            throw new StatusError('The thread has not been started or has already finished.');
        }

        if ($data instanceof ExitResult) {
            throw new \Error('Cannot send exit result objects.');
        }

        return call(function () use ($data): \Generator {
            try {
                return yield $this->channel->send($data);
            } catch (ChannelException $e) {
                if ($this->channel === null) {
                    throw new ContextException("The thread stopped responding, potentially due to a fatal error or calling exit", 0, $e);
                }

                try {
                    $data = yield Promise\timeout($this->join(), 100);
                } catch (ContextException | ChannelException | TimeoutException $ex) {
                    $this->kill();
                    throw new ContextException("The thread stopped responding, potentially due to a fatal error or calling exit", 0, $e);
                }

                throw new SynchronizationError(\sprintf(
                    'Thread unexpectedly exited with result of type: %s',
                    \is_object($data) ? \get_class($data) : \gettype($data)
                ), 0, $e);
            }
        });
    }

    /**
     * Returns the ID of the thread. This ID will be unique to this process.
     *
     * @return int
     *
     * @throws \Amp\Process\StatusError
     */
    public function getId(): int
    {
        if ($this->id === null) {
            throw new StatusError('The thread has not been started');
        }

        return $this->id;
    }
}
