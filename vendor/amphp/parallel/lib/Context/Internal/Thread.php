<?php

namespace Amp\Parallel\Context\Internal;

use Amp\Loop;
use Amp\Parallel\Sync\Channel;
use Amp\Parallel\Sync\ChannelException;
use Amp\Parallel\Sync\ChannelledSocket;
use Amp\Parallel\Sync\ExitFailure;
use Amp\Parallel\Sync\ExitSuccess;
use Amp\Parallel\Sync\SerializationException;
use function Amp\call;

/**
 * An internal thread that executes a given function concurrently.
 *
 * @internal
 */
final class Thread extends \Thread
{
    const KILL_CHECK_FREQUENCY = 250;

    private $id;

    /** @var callable The function to execute in the thread. */
    private $function;

    /** @var mixed[] Arguments to pass to the function. */
    private $args;

    /** @var resource */
    private $socket;

    /** @var bool */
    private $killed = false;

    /**
     * Creates a new thread object.
     *
     * @param int $id            Thread ID.
     * @param resource $socket   IPC communication socket.
     * @param callable $function The function to execute in the thread.
     * @param mixed[]  $args     Arguments to pass to the function.
     */
    public function __construct(int $id, $socket, callable $function, array $args = [])
    {
        $this->id = $id;
        $this->function = $function;
        $this->args = $args;
        $this->socket = $socket;
    }

    /**
     * Runs the thread code and the initialized function.
     *
     * @codeCoverageIgnore Only executed in thread.
     */
    public function run()
    {
        \define("AMP_CONTEXT", "thread");
        \define("AMP_CONTEXT_ID", $this->id);

        /* First thing we need to do is re-initialize the class autoloader. If
         * we don't do this first, any object of a class that was loaded after
         * the thread started will just be garbage data and unserializable
         * values (like resources) will be lost. This happens even with
         * thread-safe objects.
         */

        // Protect scope by using an unbound closure (protects static access as well).
        (static function (): void {
            $paths = [
                \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . "vendor" . \DIRECTORY_SEPARATOR . "autoload.php",
                \dirname(__DIR__, 5) . \DIRECTORY_SEPARATOR . "autoload.php",
            ];

            foreach ($paths as $path) {
                if (\file_exists($path)) {
                    $autoloadPath = $path;
                    break;
                }
            }

            if (!isset($autoloadPath)) {
                throw new \Error("Could not locate autoload.php");
            }

            require $autoloadPath;
        })->bindTo(null, null)();

        // At this point, the thread environment has been prepared so begin using the thread.

        if ($this->killed) {
            return; // Thread killed while requiring autoloader, simply exit.
        }

        Loop::run(function (): \Generator {
            $watcher = Loop::repeat(self::KILL_CHECK_FREQUENCY, function (): void {
                if ($this->killed) {
                    Loop::stop();
                }
            });
            Loop::unreference($watcher);

            try {
                $channel = new ChannelledSocket($this->socket, $this->socket);
                yield from $this->execute($channel);
            } catch (\Throwable $exception) {
                return; // Parent context exited or destroyed thread, no need to continue.
            } finally {
                Loop::cancel($watcher);
            }
        });
    }

    /**
     * Sets a local variable to true so the running event loop can check for a kill signal.
     */
    public function kill()
    {
        return $this->killed = true;
    }

    /**
     * @param \Amp\Parallel\Sync\Channel $channel
     *
     * @return \Generator
     *
     * @codeCoverageIgnore Only executed in thread.
     */
    private function execute(Channel $channel): \Generator
    {
        try {
            $result = new ExitSuccess(yield call($this->function, $channel, ...$this->args));
        } catch (\Throwable $exception) {
            $result = new ExitFailure($exception);
        }

        if ($this->killed) {
            return; // Parent is not listening for a result.
        }

        // Attempt to return the result.
        try {
            try {
                yield $channel->send($result);
            } catch (SerializationException $exception) {
                // Serializing the result failed. Send the reason why.
                yield $channel->send(new ExitFailure($exception));
            }
        } catch (ChannelException $exception) {
            // The result was not sendable! The parent context must have died or killed the context.
        }
    }
}
