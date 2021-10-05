<?php

namespace React\Tests\Dns\Query;

use React\Dns\Model\Message;
use React\Dns\Query\CachingExecutor;
use React\Dns\Query\Query;
use React\Promise\Promise;
use React\Tests\Dns\TestCase;
use React\Promise\Deferred;
use React\Dns\Model\Record;

class CachingExecutorTest extends TestCase
{
    public function testQueryWillReturnPendingPromiseWhenCacheIsPendingWithoutSendingQueryToFallbackExecutor()
    {
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->never())->method('query');

        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn(new Promise(function () { }));

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    public function testQueryWillReturnPendingPromiseWhenCacheReturnsMissAndWillSendSameQueryToFallbackExecutor()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->once())->method('query')->with($query)->willReturn(new Promise(function () { }));

        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->willReturn(\React\Promise\resolve(null));

        $executor = new CachingExecutor($fallback, $cache);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableNever(), $this->expectCallableNever());
    }

    public function testQueryWillReturnResolvedPromiseWhenCacheReturnsHitWithoutSendingQueryToFallbackExecutor()
    {
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->never())->method('query');

        $message = new Message();
        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn(\React\Promise\resolve($message));

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableOnceWith($message), $this->expectCallableNever());
    }

    public function testQueryWillReturnResolvedPromiseWhenCacheReturnsMissAndFallbackExecutorResolvesAndSaveMessageToCacheWithMinimumTtlFromRecord()
    {
        $message = new Message();
        $message->answers[] = new Record('reactphp.org', Message::TYPE_A, Message::CLASS_IN, 3700, '127.0.0.1');
        $message->answers[] = new Record('reactphp.org', Message::TYPE_A, Message::CLASS_IN, 3600, '127.0.0.1');
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->once())->method('query')->willReturn(\React\Promise\resolve($message));

        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn(\React\Promise\resolve(null));
        $cache->expects($this->once())->method('set')->with('reactphp.org:1:1', $message, 3600);

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableOnceWith($message), $this->expectCallableNever());
    }

    public function testQueryWillReturnResolvedPromiseWhenCacheReturnsMissAndFallbackExecutorResolvesAndSaveMessageToCacheWithDefaultTtl()
    {
        $message = new Message();
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->once())->method('query')->willReturn(\React\Promise\resolve($message));

        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn(\React\Promise\resolve(null));
        $cache->expects($this->once())->method('set')->with('reactphp.org:1:1', $message, 60);

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableOnceWith($message), $this->expectCallableNever());
    }

    public function testQueryWillReturnResolvedPromiseWhenCacheReturnsMissAndFallbackExecutorResolvesWithTruncatedResponseButShouldNotSaveTruncatedMessageToCache()
    {
        $message = new Message();
        $message->tc = true;
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->once())->method('query')->willReturn(\React\Promise\resolve($message));

        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn(\React\Promise\resolve(null));
        $cache->expects($this->never())->method('set');

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableOnceWith($message), $this->expectCallableNever());
    }

    public function testQueryWillReturnRejectedPromiseWhenCacheReturnsMissAndFallbackExecutorRejects()
    {
        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->once())->method('query')->willReturn(\React\Promise\reject($exception = new \RuntimeException()));

        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->willReturn(\React\Promise\resolve(null));

        $executor = new CachingExecutor($fallback, $cache);

        $promise = $executor->query($query);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnceWith($exception));
    }

    public function testCancelQueryWillReturnRejectedPromiseAndCancelPendingPromiseFromCache()
    {
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->never())->method('query');

        $pending = new Promise(function () { }, $this->expectCallableOnce());
        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn($pending);

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);
        $promise->cancel();

        $exception = null;
        $promise->then(null, function ($reason) use (&$exception) {
            $exception = $reason;
        });

        /** @var \RuntimeException $exception */
        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('DNS query for reactphp.org (A) has been cancelled', $exception->getMessage());
    }

    public function testCancelQueryWillReturnRejectedPromiseAndCancelPendingPromiseFromFallbackExecutorWhenCacheReturnsMiss()
    {
        $pending = new Promise(function () { }, $this->expectCallableOnce());
        $fallback = $this->getMockBuilder('React\Dns\Query\ExecutorInterface')->getMock();
        $fallback->expects($this->once())->method('query')->willReturn($pending);

        $deferred = new Deferred();
        $cache = $this->getMockBuilder('React\Cache\CacheInterface')->getMock();
        $cache->expects($this->once())->method('get')->with('reactphp.org:1:1')->willReturn($deferred->promise());

        $executor = new CachingExecutor($fallback, $cache);

        $query = new Query('reactphp.org', Message::TYPE_A, Message::CLASS_IN);

        $promise = $executor->query($query);
        $deferred->resolve(null);
        $promise->cancel();

        $exception = null;
        $promise->then(null, function ($reason) use (&$exception) {
            $exception = $reason;
        });

        /** @var \RuntimeException $exception */
        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('DNS query for reactphp.org (A) has been cancelled', $exception->getMessage());
    }
}
