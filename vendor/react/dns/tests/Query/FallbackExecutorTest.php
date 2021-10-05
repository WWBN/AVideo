<?php

namespace React\Tests\Dns\Query;

use React\Dns\Model\Message;
use React\Dns\Query\FallbackExecutor;
use React\Dns\Query\Query;
use React\Promise\Promise;
use React\Tests\Dns\TestCase;

class FallbackExecutorTest extends TestCase
{
    public function testQueryWillReturnPendingPromiseWhenPrimaryExecutorIsStillPending()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(new Promise(function () { }));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    public function testQueryWillResolveWithMessageWhenPrimaryExecutorResolvesWithMessage()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\resolve(new Message()));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableOnceWith($this->isInstanceOf('React\Dns\Model\Message')), $this->expectCallableNever());
    }

    public function testQueryWillReturnPendingPromiseWhenPrimaryExecutorRejectsPromiseAndSecondaryExecutorIsStillPending()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException()));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->once())->method('query')->with($query)->willReturn(new Promise(function () { }));

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    public function testQueryWillResolveWithMessageWhenPrimaryExecutorRejectsPromiseAndSecondaryExecutorResolvesWithMessage()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException()));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\resolve(new Message()));

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableOnceWith($this->isInstanceOf('React\Dns\Model\Message')), $this->expectCallableNever());
    }

    public function testQueryWillRejectWithExceptionMessagesConcatenatedAfterColonWhenPrimaryExecutorRejectsPromiseAndSecondaryExecutorRejectsPromiseWithMessageWithColon()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException('DNS query for reactphp.org (A) failed: Unable to connect to DNS server A')));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException('DNS query for reactphp.org (A) failed: Unable to connect to DNS server B')));

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce($this->isInstanceOf('Exception')));

        $exception = null;
        $promise->then(null, function ($reason) use (&$exception) {
            $exception = $reason;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('DNS query for reactphp.org (A) failed: Unable to connect to DNS server A. Unable to connect to DNS server B', $exception->getMessage());
    }

    public function testQueryWillRejectWithExceptionMessagesConcatenatedInFullWhenPrimaryExecutorRejectsPromiseAndSecondaryExecutorRejectsPromiseWithMessageWithNoColon()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException('Reason A')));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException('Reason B')));

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce($this->isInstanceOf('Exception')));

        $exception = null;
        $promise->then(null, function ($reason) use (&$exception) {
            $exception = $reason;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Reason A. Reason B', $exception->getMessage());
    }

    public function testCancelQueryWillReturnRejectedPromiseWithoutCallingSecondaryExecutorWhenPrimaryExecutorIsStillPending()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(new Promise(function () { }, function () { throw new \RuntimeException(); }));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->never())->method('query');

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);
        $promise->cancel();

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testCancelQueryWillReturnRejectedPromiseWhenPrimaryExecutorRejectsAndSecondaryExecutorIsStillPending()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->with($query)->willReturn(\React\Promise\reject(new \RuntimeException()));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->once())->method('query')->with($query)->willReturn(new Promise(function () { }, function () { throw new \RuntimeException(); }));

        $executor = new FallbackExecutor($primary, $secondary);

        $promise = $executor->query($query);
        $promise->cancel();

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testCancelQueryShouldNotCauseGarbageReferencesWhenCancellingPrimaryExecutor()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->willReturn(new Promise(function () { }, function () { throw new \RuntimeException(); }));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->never())->method('query');

        $executor = new FallbackExecutor($primary, $secondary);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);
        $promise->cancel();
        $promise = null;

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testCancelQueryShouldNotCauseGarbageReferencesWhenCancellingSecondaryExecutor()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $primary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $primary->expects($this->once())->method('query')->willReturn(\React\Promise\reject(new \RuntimeException()));

        $secondary = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $secondary->expects($this->once())->method('query')->willReturn(new Promise(function () { }, function () { throw new \RuntimeException(); }));

        $executor = new FallbackExecutor($primary, $secondary);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);
        $promise->cancel();
        $promise = null;

        $this->assertEquals(0, gc_collect_cycles());
    }
}
