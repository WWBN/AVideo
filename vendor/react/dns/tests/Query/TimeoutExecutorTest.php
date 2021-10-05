<?php

namespace React\Tests\Dns\Query;

use React\Dns\Model\Message;
use React\Dns\Query\CancellationException;
use React\Dns\Query\Query;
use React\Dns\Query\TimeoutException;
use React\Dns\Query\TimeoutExecutor;
use React\EventLoop\Factory;
use React\Promise;
use React\Promise\Deferred;
use React\Tests\Dns\TestCase;

class TimeoutExecutorTest extends TestCase
{
    private $loop;
    private $wrapped;
    private $executor;

    /**
     * @before
     */
    public function setUpExecutor()
    {
        $this->loop = Factory::create();

        $this->wrapped = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();

        $this->executor = new TimeoutExecutor($this->wrapped, 5.0, $this->loop);
    }

    public function testCtorWithoutLoopShouldAssignDefaultLoop()
    {
        $executor = new TimeoutExecutor($this->executor, 5.0);

        $ref = new \ReflectionProperty($executor, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($executor);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testCancellingPromiseWillCancelWrapped()
    {
        $cancelled = 0;

        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->will($this->returnCallback(function ($query) use (&$cancelled) {
                $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                    ++$cancelled;
                    $reject(new CancellationException('Cancelled'));
                });

                return $deferred->promise();
            }));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $this->executor->query($query);

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

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $this->executor->query($query);

        $promise->then($this->expectCallableOnce(), $this->expectCallableNever());
    }

    public function testRejectsPromiseWhenWrappedRejects()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->willReturn(Promise\reject(new \RuntimeException()));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $this->executor->query($query);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnceWith(new \RuntimeException()));
    }

    public function testWrappedWillBeCancelledOnTimeout()
    {
        $this->executor = new TimeoutExecutor($this->wrapped, 0, $this->loop);

        $cancelled = 0;

        $this->wrapped
            ->expects($this->once())
            ->method('query')
            ->will($this->returnCallback(function ($query) use (&$cancelled) {
                $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                    ++$cancelled;
                    $reject(new CancellationException('Cancelled'));
                });

                return $deferred->promise();
            }));

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $this->executor->query($query);

        $this->assertEquals(0, $cancelled);

        try {
            \Clue\React\Block\await($promise, $this->loop);
            $this->fail();
        } catch (TimeoutException $exception) {
            $this->assertEquals('DNS query for igor.io (A) timed out' , $exception->getMessage());
        }

        $this->assertEquals(1, $cancelled);
    }
}
