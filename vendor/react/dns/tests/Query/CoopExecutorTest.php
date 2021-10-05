<?php

use React\Dns\Query\CoopExecutor;
use React\Dns\Model\Message;
use React\Dns\Query\Query;
use React\Promise\Promise;
use React\Tests\Dns\TestCase;
use React\Promise\Deferred;

class CoopExecutorTest extends TestCase
{
    public function testQueryOnceWillPassExactQueryToBaseExecutor()
    {
        $pending = new Promise(function () { });
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->with($query)->willReturn($pending);
        $connector = new CoopExecutor($base);

        $connector->query($query);
    }

    public function testQueryOnceWillResolveWhenBaseExecutorResolves()
    {
        $message = new Message();

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn(\React\Promise\resolve($message));
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $promise = $connector->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceWith($message));
    }

    public function testQueryOnceWillRejectWhenBaseExecutorRejects()
    {
        $exception = new RuntimeException();

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn(\React\Promise\reject($exception));
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $promise = $connector->query($query);

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then(null, $this->expectCallableOnceWith($exception));
    }

    public function testQueryTwoDifferentQueriesWillPassExactQueryToBaseExecutorTwice()
    {
        $pending = new Promise(function () { });
        $query1 = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $query2 = new Query('reactphp.org', Message::TYPE_AAAA, Message::CLASS_IN);
        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->exactly(2))->method('query')->withConsecutive(
            array($query1),
            array($query2)
        )->willReturn($pending);
        $connector = new CoopExecutor($base);

        $connector->query($query1);
        $connector->query($query2);
    }

    public function testQueryTwiceWillPassExactQueryToBaseExecutorOnceWhenQueryIsStillPending()
    {
        $pending = new Promise(function () { });
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->with($query)->willReturn($pending);
        $connector = new CoopExecutor($base);

        $connector->query($query);
        $connector->query($query);
    }

    public function testQueryTwiceWillPassExactQueryToBaseExecutorTwiceWhenFirstQueryIsAlreadyResolved()
    {
        $deferred = new Deferred();
        $pending = new Promise(function () { });
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->exactly(2))->method('query')->with($query)->willReturnOnConsecutiveCalls($deferred->promise(), $pending);

        $connector = new CoopExecutor($base);

        $connector->query($query);

        $deferred->resolve(new Message());

        $connector->query($query);
    }

    public function testQueryTwiceWillPassExactQueryToBaseExecutorTwiceWhenFirstQueryIsAlreadyRejected()
    {
        $deferred = new Deferred();
        $pending = new Promise(function () { });
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->exactly(2))->method('query')->with($query)->willReturnOnConsecutiveCalls($deferred->promise(), $pending);

        $connector = new CoopExecutor($base);

        $connector->query($query);

        $deferred->reject(new RuntimeException());

        $connector->query($query);
    }

    public function testCancelQueryWillCancelPromiseFromBaseExecutorAndReject()
    {
        $promise = new Promise(function () { }, $this->expectCallableOnce());

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn($promise);
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $promise = $connector->query($query);

        $promise->cancel();

        $exception = null;
        $promise->then(null, function ($reason) use (&$exception) {
            $exception = $reason;
        });

        /** @var \RuntimeException $exception */
        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('DNS query for reactphp.org (A) has been cancelled', $exception->getMessage());
    }

    public function testCancelOneQueryWhenOtherQueryIsStillPendingWillNotCancelPromiseFromBaseExecutorAndRejectCancelled()
    {
        $promise = new Promise(function () { }, $this->expectCallableNever());

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn($promise);
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $promise1 = $connector->query($query);
        $promise2 = $connector->query($query);

        $promise1->cancel();

        $promise1->then(null, $this->expectCallableOnce());
        $promise2->then(null, $this->expectCallableNever());
    }

    public function testCancelSecondQueryWhenFirstQueryIsStillPendingWillNotCancelPromiseFromBaseExecutorAndRejectCancelled()
    {
        $promise = new Promise(function () { }, $this->expectCallableNever());

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn($promise);
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $promise1 = $connector->query($query);
        $promise2 = $connector->query($query);

        $promise2->cancel();

        $promise2->then(null, $this->expectCallableOnce());
        $promise1->then(null, $this->expectCallableNever());
    }

    public function testCancelAllPendingQueriesWillCancelPromiseFromBaseExecutorAndRejectCancelled()
    {
        $promise = new Promise(function () { }, $this->expectCallableOnce());

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn($promise);
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);
        $promise1 = $connector->query($query);
        $promise2 = $connector->query($query);

        $promise1->cancel();
        $promise2->cancel();

        $promise1->then(null, $this->expectCallableOnce());
        $promise2->then(null, $this->expectCallableOnce());
    }

    public function testQueryTwiceWillQueryBaseExecutorTwiceIfFirstQueryHasAlreadyBeenCancelledWhenSecondIsStarted()
    {
        $promise = new Promise(function () { }, $this->expectCallableOnce());
        $pending = new Promise(function () { });

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->exactly(2))->method('query')->willReturnOnConsecutiveCalls($promise, $pending);
        $connector = new CoopExecutor($base);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise1 = $connector->query($query);
        $promise1->cancel();

        $promise2 = $connector->query($query);

        $promise1->then(null, $this->expectCallableOnce());

        $promise2->then(null, $this->expectCallableNever());
    }

    public function testCancelQueryShouldNotCauseGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        $deferred = new Deferred(function () {
            throw new \RuntimeException();
        });

        $base = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $base->expects($this->once())->method('query')->willReturn($deferred->promise());
        $connector = new CoopExecutor($base);

        gc_collect_cycles();
        gc_collect_cycles(); // clear twice to avoid leftovers in PHP 7.4 with ext-xdebug and code coverage turned on

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $connector->query($query);
        $promise->cancel();
        $promise = null;

        $this->assertEquals(0, gc_collect_cycles());
    }
}
