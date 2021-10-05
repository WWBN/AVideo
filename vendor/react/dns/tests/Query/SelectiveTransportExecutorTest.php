<?php

namespace React\Tests\Dns\Query;

use React\Dns\Model\Message;
use React\Dns\Query\Query;
use React\Dns\Query\SelectiveTransportExecutor;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Tests\Dns\TestCase;

class SelectiveTransportExecutorTest extends TestCase
{
    /**
     * @before
     */
    public function setUpMocks()
    {
        $this->datagram = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $this->stream = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();

        $this->executor = new SelectiveTransportExecutor($this->datagram, $this->stream);
    }

    public function testQueryResolvesWhenDatagramTransportResolvesWithoutUsingStreamTransport()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $response = new Message();

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\resolve($response));

        $this->stream
            ->expects($this->never())
            ->method('query');

        $promise = $this->executor->query($query);

        $promise->then($this->expectCallableOnceWith($response));
    }

    public function testQueryResolvesWhenStreamTransportResolvesAfterDatagramTransportRejectsWithSizeError()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $response = new Message();

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\reject(new \RuntimeException('', defined('SOCKET_EMSGSIZE') ? SOCKET_EMSGSIZE : 90)));

        $this->stream
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\resolve($response));

        $promise = $this->executor->query($query);

        $promise->then($this->expectCallableOnceWith($response));
    }

    public function testQueryRejectsWhenDatagramTransportRejectsWithRuntimeExceptionWithoutUsingStreamTransport()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\reject(new \RuntimeException()));

        $this->stream
            ->expects($this->never())
            ->method('query');

        $promise = $this->executor->query($query);

        $promise->then(null, $this->expectCallableOnce());
    }

    public function testQueryRejectsWhenStreamTransportRejectsAfterDatagramTransportRejectsWithSizeError()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\reject(new \RuntimeException('', defined('SOCKET_EMSGSIZE') ? SOCKET_EMSGSIZE : 90)));

        $this->stream
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\reject(new \RuntimeException()));

        $promise = $this->executor->query($query);

        $promise->then(null, $this->expectCallableOnce());
    }

    public function testCancelPromiseWillCancelPromiseFromDatagramExecutor()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(new Promise(function () {}, $this->expectCallableOnce()));

        $promise = $this->executor->query($query);
        $promise->cancel();
    }

    public function testCancelPromiseWillCancelPromiseFromStreamExecutorWhenDatagramExecutorRejectedWithTruncatedResponse()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $deferred = new Deferred();
        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($deferred->promise());

        $this->stream
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(new Promise(function () {}, $this->expectCallableOnce()));

        $promise = $this->executor->query($query);
        $deferred->reject(new \RuntimeException('', defined('SOCKET_EMSGSIZE') ? SOCKET_EMSGSIZE : 90));
        $promise->cancel();
    }

    public function testCancelPromiseShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(new Promise(function () {}, function () {
                throw new \RuntimeException('Cancelled');
            }));

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = $this->executor->query($query);
        $promise->cancel();
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testCancelPromiseAfterTruncatedResponseShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $deferred = new Deferred();
        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($deferred->promise());

        $this->stream
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(new Promise(function () {}, function () {
                throw new \RuntimeException('Cancelled');
            }));

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = $this->executor->query($query);
        $deferred->reject(new \RuntimeException('', defined('SOCKET_EMSGSIZE') ? SOCKET_EMSGSIZE : 90));
        $promise->cancel();
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testRejectedPromiseAfterTruncatedResponseShouldNotCreateAnyGarbageReferences()
    {
        $query = new Query('igor.io', Message::TYPE_A, Message::CLASS_IN);

        $this->datagram
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\reject(new \RuntimeException('', defined('SOCKET_EMSGSIZE') ? SOCKET_EMSGSIZE : 90)));

        $this->stream
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn(\React\Promise\reject(new \RuntimeException()));

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $promise = $this->executor->query($query);
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }
}
