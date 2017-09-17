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
            ->with('8.8.8.8', $this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnValue($this->expectPromiseOnce()));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $retryExecutor->query('8.8.8.8', $query);
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
            ->with('8.8.8.8', $this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->onConsecutiveCalls(
                $this->returnCallback(function ($domain, $query) {
                    return Promise\reject(new TimeoutException("timeout"));
                }),
                $this->returnCallback(function ($domain, $query) use ($response) {
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

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $retryExecutor->query('8.8.8.8', $query)->then($callback, $errorback);
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
            ->with('8.8.8.8', $this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($domain, $query) {
                return Promise\reject(new TimeoutException("timeout"));
            }));

        $callback = $this->expectCallableNever();

        $errorback = $this->createCallableMock();
        $errorback
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf('RuntimeException'));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $retryExecutor->query('8.8.8.8', $query)->then($callback, $errorback);
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
            ->with('8.8.8.8', $this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($domain, $query) {
                return Promise\reject(new \Exception);
            }));

        $callback = $this->expectCallableNever();

        $errorback = $this->createCallableMock();
        $errorback
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf('Exception'));

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $retryExecutor->query('8.8.8.8', $query)->then($callback, $errorback);
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
            ->with('8.8.8.8', $this->isInstanceOf('React\Dns\Query\Query'))
            ->will($this->returnCallback(function ($domain, $query) use (&$cancelled) {
                $deferred = new Deferred(function ($resolve, $reject) use (&$cancelled) {
                    ++$cancelled;
                    $reject(new CancellationException('Cancelled'));
                });

                return $deferred->promise();
            })
        );

        $retryExecutor = new RetryExecutor($executor, 2);

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN, 1345656451);
        $promise = $retryExecutor->query('8.8.8.8', $query);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        $this->assertEquals(0, $cancelled);
        $promise->cancel();
        $this->assertEquals(1, $cancelled);
    }

    protected function expectPromiseOnce($return = null)
    {
        $mock = $this->createPromiseMock();
        $mock
            ->expects($this->once())
            ->method('then')
            ->will($this->returnValue($return));

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
        $response->header->set('qr', 1);
        $response->questions[] = new Record('igor.io', Message::TYPE_A, Message::CLASS_IN);
        $response->answers[] = new Record('igor.io', Message::TYPE_A, Message::CLASS_IN, 3600, '178.79.169.131');
        $response->prepare();

        return $response;
    }
}

