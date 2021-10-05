<?php

namespace React\Tests\Socket;

use React\Dns\Model\Message;
use React\Dns\Resolver\ResolverInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise;
use React\Promise\CancellablePromiseInterface;

/**
 * @internal
 */
final class TimerSpeedUpEventLoop implements LoopInterface
{
    /** @var LoopInterface */
    private $loop;
    
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }
    
    public function addReadStream($stream, $listener)
    {
        return $this->loop->addReadStream($stream, $listener);
    }

    public function addWriteStream($stream, $listener)
    {
        return $this->loop->addWriteStream($stream, $listener);
    }

    public function removeReadStream($stream)
    {
        return $this->loop->removeReadStream($stream);
    }

    public function removeWriteStream($stream)
    {
        return $this->loop->removeWriteStream($stream);
    }

    public function addTimer($interval, $callback)
    {
        return $this->loop->addTimer($interval / 10, $callback);
    }

    public function addPeriodicTimer($interval, $callback)
    {
        return $this->loop->addPeriodicTimer($interval / 10, $callback);
    }

    public function cancelTimer(TimerInterface $timer)
    {
        return $this->loop->cancelTimer($timer);
    }

    public function futureTick($listener)
    {
        return $this->loop->futureTick($listener);
    }

    public function addSignal($signal, $listener)
    {
        return $this->loop->addSignal($signal, $listener);
    }

    public function removeSignal($signal, $listener)
    {
        return $this->loop->removeSignal($signal, $listener);
    }

    public function run()
    {
        return $this->loop->run();
    }

    public function stop()
    {
        return $this->loop->stop();
    }
}