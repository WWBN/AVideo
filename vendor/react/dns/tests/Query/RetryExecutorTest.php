<?php

namespace React\Tests\Dns\Query;

use React\Tests\Dns\TestCase;
use React\Dns\Query\RetryExecutor;
use React\Dns\Query\Query;
use React\Dns\Model\Message;
use React\Dns\Query\TimeoutException;
use React\Dns\Model\Record;
use React\Promise;
use React\Promise\Deferred;
use React\Dns\Query\CancellationException;

class RetryExecutorTest extends TestCase
{
    /**
    * @covers React\Dns\Query\RetryExecutor
    * @test
    */
    public function queryShouldDelegateToDecoratedExecutor()
    {
        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnValue($this->expectPromiseOnce()));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $retryExecutor->query($query);
    }

    /**
    * @covers React\Dns\Query\RetryExecutor
    * @test
    */
    public function queryShouldRetryQueryOnTimeout()
    {
        $response = $this->createStandardResponse();

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->exactly(2))
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->onConsecutiveCalls(
                $this->returnCallback(function ($query) {
                    return Promise\reject(new TimeoutException("timeout"));
                }),
                $this->returnCallback(function ($query) use ($response) {
                    return Promise\resolve($response);
                })
            ));

        $callback = $this->createCallableMock();
        $callback
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf('React\Dns\Model\Message'));

        $errorback = $this->expectCallableNever();

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $retryExecutor->query($query)->then($callback, $errorback);
    }

    /**
    * @covers React\Dns\Query\RetryExecutor
    * @test
    */
    public function queryShouldStopRetryingAfterSomeAttempts()
    {
        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->exactly(3))
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($query) {
                return Promise\reject(new TimeoutException("timeout"));
            }));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $retryExecutor->query($query);

        $exception = null;
        $promise->then(null, function ($reason) use (&$exception) {
            $exception = $reason;
        });

        /** @var \RuntimeException $exception */
        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('DNS query for igor.io (A) failed: too many retries', $exception->getMessage());
    }

    /**
    * @covers React\Dns\Query\RetryExecutor
    * @test
    */
    public function queryShouldForwardNonTimeoutErrors()
    {
        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($query) {
                return Promise\reject(new \Exception);
            }));

        $callback = $this->expectCallableNever();

        $errorback = $this->createCallableMock();
        $errorback
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf('Exception'));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $retryExecutor->query($query)->then($callback, $errorback);
    }

    /**
     * @covers React\Dns\Query\RetryExecutor
     * @test
     */
    public function queryShouldCancelQueryOnCancel()
    {
        $cancelled = 0;

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($query) use (&$cancelled) {
                $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                    ++$cancelled;
                    $reject(new CancellationException('Cancelled'));
                });

                return $deferred->promise();
            })
        );

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $retryExecutor->query($query);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        $this->assertEquals(0, $cancelled);
        $promise->cancel();
        $this->assertEquals(1, $cancelled);
    }

    /**
     * @covers React\Dns\Query\RetryExecutor
     * @test
     */
    public function queryShouldCancelSecondQueryOnCancel()
    {
        $deferred = new Deferred();
        $cancelled = 0;

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->exactly(2))
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->onConsecutiveCalls(
                $this->returnValue($deferred->promise()),
                $this->returnCallback(function ($query) use (&$cancelled) {
                    $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                        ++$cancelled;
                        $reject(new CancellationException('Cancelled'));
                    });

                    return $deferred->promise();
                })
        ));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $retryExecutor->query($query);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        // first query will time out after a while and this sends the next query
        $deferred->reject(new TimeoutException());

        $this->assertEquals(0, $cancelled);
        $promise->cancel();
        $this->assertEquals(1, $cancelled);
    }

    /**
     * @covers React\Dns\Query\RetryExecutor
     * @test
     */
    public function queryShouldNotCauseGarbageReferencesOnSuccess()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->willReturn(Promise\resolve($this->createStandardResponse()));

        $retryExecutor = new RetryExecutor($executor, 0);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $retryExecutor->query($query);

        $this->assertEquals(0, gc_collect_cycles());
    }

    /**
     * @covers React\Dns\Query\RetryExecutor
     * @test
     */
    public function queryShouldNotCauseGarbageReferencesOnTimeoutErrors()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->any())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->willReturn(Promise\reject(new TimeoutException("timeout")));

        $retryExecutor = new RetryExecutor($executor, 0);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $retryExecutor->query($query);

        $this->assertEquals(0, gc_collect_cycles());
    }

    /**
     * @covers React\Dns\Query\RetryExecutor
     * @test
     */
    public function queryShouldNotCauseGarbageReferencesOnCancellation()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $deferred = new Deferred(function () {
            throw new \RuntimeException();
        });

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->willReturn($deferred->promise());

        $retryExecutor = new RetryExecutor($executor, 0);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $promise = $retryExecutor->query($query);
        $promise->cancel();
        $promise = null;

        $this->assertEquals(0, gc_collect_cycles());
    }

    /**
     * @covers React\Dns\Query\RetryExecutor
     * @test
     */
    public function queryShouldNotCauseGarbageReferencesOnNonTimeoutErrors()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $executor = $this->createExecutorMock();
        $executor
            ->expects($this->once())
            ->method('query')
            ->with($this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($query) {
                return Promise\reject(new \Exception);
            }));

        $retryExecutor = new RetryExecutor($executor, 2);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $retryExecutor->query($query);

        $this->assertEquals(0, gc_collect_cycles());
    }

    protected function expectPromiseOnce($return = null)
    {
        $mock = $this->createPromiseMock();
        $mock
            ->expects($this->once())
            ->method('then')
            ->will($this->returnValue(Promise\resolve($return)));

        return $mock;
    }

    protected function createExecutorMock()
    {
        return $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
    }

    protected function createPromiseMock()
    {
        return $this->getMockBuilder('React\Promise\PromiseInterface')->getMock();
    }

    protected function createStandardResponse()
    {
        $response = new Message();
        $response->qr = true;
        $response->questions[] = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $response->answers[] = new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131');

        return $response;
    }
}

