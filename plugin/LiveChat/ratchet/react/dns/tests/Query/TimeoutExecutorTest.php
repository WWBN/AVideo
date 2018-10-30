<?php

namespace React\Tests\Dns\Query;

use React\Dns\Query\TimeoutExecutor;
use React\Dns\Query\Query;
use React\Dns\Model\Message;
use React\Promise\Deferred;
use React\Dns\Query\CancellationException;
use React\Tests\Dns\TestCase;
use React\EventLoop\Factory;
use React\Promise;

class TimeoutExecutorTest extends TestCase
{
    public function setUp()
    {
        $this->loop = Factory::create();

        $this->wrapped = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();

        $this->executor = new TimeoutExecutor($this->wrapped, 5.0, $this->loop);
    }

    public function testCancellingPromiseWillCancelWrapped()
    {
        $cancelled = 0;

        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->will($this->returnCallback(function ($domain, $query) use (&$cancelled) {
                $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                    ++$cancelled;
                    $reject(new CancellationException('Cancelled'));
                });

                return $deferred->promise();
            }));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $this->assertEquals(0, $cancelled);
        $promise->cancel();
        $this->assertEquals(1, $cancelled);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testResolvesPromiseWhenWrappedResolves()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->willReturn(Promise\resolve('0.0.0.0'));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());
    }

    public function testRejectsPromiseWhenWrappedRejects()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->willReturn(Promise\reject(new \RuntimeException()));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $this->executor->query('8.8.8.8:53', $query);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnceWith(new \RuntimeException()));
    }

    public function testWrappedWillBeCancelledOnTimeout()
    {
        $this->executor = new TimeoutExecutor($this->wrapped, 0, $this->loop);

        $cancelled = 0;

        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->will($this->returnCallback(function ($domain, $query) use (&$cancelled) {
                $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                    ++$cancelled;
                    $reject(new CancellationException('Cancelled'));
                });

                return $deferred->promise();
            }));

        $callback = $this->expectCallableNever();

        $errorback = $this->createCallableMock();
        $errorback
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->logicalAnd(
                $this->isInstanceOf('React\Dns\Query\TimeoutException'),
                $this->attribute($this->equalTo('DNS query for igor.io timed out'), 'message')
            ));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $this->executor->query('8.8.8.8:53', $query)->then($callback, $errorback);

        $this->assertEquals(0, $cancelled);

        $this->loop->run();

        $this->assertEquals(1, $cancelled);
    }
}
