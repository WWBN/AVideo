<?php

namespace React\Tests\Socket;

use React\Promise\Promise;
use React\Socket\HappyEyeBallsConnectionBuilder;
use React\Dns\Model\Message;
use React\Promise\Deferred;

class HappyEyeBallsConnectionBuilderTest extends TestCase
{
    public function testConnectWillResolveTwiceViaResolver()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->never())->method('addTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->never())->method('connect');

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturn(new Promise(function () { }));

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
    }

    public function testConnectWillRejectWhenBothDnsLookupsReject()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->never())->method('addTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->never())->method('connect');

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturn(new Promise(function () {
            throw new \RuntimeException('DNS lookup error');
        }));

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 failed during DNS lookup: DNS lookup error', $exception->getMessage());
    }

    public function testConnectWillRejectWhenBothDnsLookupsRejectWithDifferentMessages()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->never())->method('addTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->never())->method('connect');

        $deferred = new Deferred();
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            $deferred->promise(),
            \React\Promise\reject(new \RuntimeException('DNS4 error'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $deferred->reject(new \RuntimeException('DNS6 error'));

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 failed during DNS lookup. Last error for IPv6: DNS6 error. Previous error for IPv4: DNS4 error', $exception->getMessage());
    }

    public function testConnectWillStartDelayTimerWhenIpv4ResolvesAndIpv6IsPending()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.05, $this->anything());
        $loop->expects($this->never())->method('cancelTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->never())->method('connect');

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            new Promise(function () { }),
            \React\Promise\resolve(array('127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
    }

    public function testConnectWillStartConnectingWithAttemptTimerButWithoutResolutionTimerWhenIpv6ResolvesAndIpv4IsPending()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything());
        $loop->expects($this->never())->method('cancelTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80?hostname=reactphp.org')->willReturn(new Promise(function () { }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            new Promise(function () { })
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
    }

    public function testConnectWillStartConnectingAndWillStartNextConnectionWithNewAttemptTimerWhenNextAttemptTimerFiresWithIpv4StillPending()
    {
        $timer = null;
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->exactly(2))->method('addTimer')->with(0.1, $this->callback(function ($cb) use (&$timer) {
            $timer = $cb;
            return true;
        }));
        $loop->expects($this->never())->method('cancelTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(2))->method('connect')->willReturn(new Promise(function () { }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1', '::2')),
            new Promise(function () { })
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();

        $this->assertNotNull($timer);
        $timer();
    }

    public function testConnectWillStartConnectingAndWillDoNothingWhenNextAttemptTimerFiresWithNoOtherIps()
    {
        $timer = null;
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->callback(function ($cb) use (&$timer) {
            $timer = $cb;
            return true;
        }));
        $loop->expects($this->never())->method('cancelTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80?hostname=reactphp.org')->willReturn(new Promise(function () { }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            new Promise(function () { })
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();

        $this->assertNotNull($timer);
        $timer();
    }

    public function testConnectWillStartConnectingWithAttemptTimerButWithoutResolutionTimerWhenIpv6ResolvesAndWillCancelAttemptTimerWhenIpv4Rejects()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80?hostname=reactphp.org')->willReturn(new Promise(function () { }));

        $deferred = new Deferred();
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            $deferred->promise()
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
        $deferred->reject(new \RuntimeException());
    }

    public function testConnectWillStartConnectingWithAttemptTimerWhenIpv6AndIpv4ResolvesAndWillStartNextConnectionAttemptWithoutAttemptTimerImmediatelyWhenFirstConnectionAttemptFails()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $deferred = new Deferred();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(2))->method('connect')->withConsecutive(
            array('tcp://[::1]:80?hostname=reactphp.org'),
            array('tcp://127.0.0.1:80?hostname=reactphp.org')
        )->willReturnOnConsecutiveCalls(
            $deferred->promise(),
            new Promise(function () { })
        );

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            \React\Promise\resolve(array('127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();

        $deferred->reject(new \RuntimeException());
    }

    public function testConnectWillStartConnectingWithAlternatingIPv6AndIPv4WhenResolverReturnsMultipleIPAdresses()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $deferred = new Deferred();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(4))->method('connect')->withConsecutive(
            array('tcp://[::1]:80?hostname=reactphp.org'),
            array('tcp://127.0.0.1:80?hostname=reactphp.org'),
            array('tcp://[::1]:80?hostname=reactphp.org'),
            array('tcp://127.0.0.1:80?hostname=reactphp.org')
        )->willReturnOnConsecutiveCalls(
            $deferred->promise(),
            $deferred->promise(),
            $deferred->promise(),
            new Promise(function () { })
        );

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1', '::1')),
            \React\Promise\resolve(array('127.0.0.1', '127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();

        $deferred->reject(new \RuntimeException());
    }

    public function testConnectWillStartConnectingWithAttemptTimerWhenOnlyIpv6ResolvesAndWillStartNextConnectionAttemptWithoutAttemptTimerImmediatelyWhenFirstConnectionAttemptFails()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(2))->method('connect')->withConsecutive(
            array('tcp://[::1]:80?hostname=reactphp.org'),
            array('tcp://[::1]:80?hostname=reactphp.org')
        )->willReturnOnConsecutiveCalls(
            \React\Promise\reject(new \RuntimeException()),
            new Promise(function () { })
        );

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1', '::1')),
            \React\Promise\reject(new \RuntimeException())
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
    }

    public function testConnectWillStartConnectingAndWillStartNextConnectionWithoutNewAttemptTimerWhenNextAttemptTimerFiresAfterIpv4Rejected()
    {
        $timer = null;
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->callback(function ($cb) use (&$timer) {
            $timer = $cb;
            return true;
        }));
        $loop->expects($this->never())->method('cancelTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(2))->method('connect')->willReturn(new Promise(function () { }));

        $deferred = new Deferred();
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1', '::2')),
            $deferred->promise()
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
        $deferred->reject(new \RuntimeException());

        $this->assertNotNull($timer);
        $timer();
    }

    public function testConnectWillStartAndCancelResolutionTimerAndStartAttemptTimerWhenIpv4ResolvesAndIpv6ResolvesAfterwardsAndStartConnectingToIpv6()
    {
        $timerDelay = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $timerAttempt = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->exactly(2))->method('addTimer')->withConsecutive(
            array(
                0.05,
                $this->anything()
            ),
            array(
                0.1,
                $this->anything()
            )
        )->willReturnOnConsecutiveCalls($timerDelay, $timerAttempt);
        $loop->expects($this->once())->method('cancelTimer')->with($timerDelay);

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80?hostname=reactphp.org')->willReturn(new Promise(function () { }));

        $deferred = new Deferred();
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            $deferred->promise(),
            \React\Promise\resolve(array('127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->connect();
        $deferred->resolve(array('::1'));
    }

    public function testConnectWillRejectWhenOnlyTcp6ConnectionRejectsAndCancelNextAttemptTimerImmediately()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $deferred = new Deferred();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80?hostname=reactphp.org')->willReturn($deferred->promise());

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            \React\Promise\reject(new \RuntimeException('DNS failed'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $deferred->reject(new \RuntimeException('Connection refused'));

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 failed: Last error for IPv6: Connection refused. Previous error for IPv4: DNS failed', $exception->getMessage());
    }

    public function testConnectWillRejectWhenOnlyTcp4ConnectionRejectsAndWillNeverStartNextAttemptTimer()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->never())->method('addTimer');

        $deferred = new Deferred();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://127.0.0.1:80?hostname=reactphp.org')->willReturn($deferred->promise());

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\reject(new \RuntimeException('DNS failed')),
            \React\Promise\resolve(array('127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $deferred->reject(new \RuntimeException('Connection refused'));

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 failed: Last error for IPv4: Connection refused. Previous error for IPv6: DNS failed', $exception->getMessage());
    }

    public function testConnectWillRejectWhenAllConnectionsRejectAndCancelNextAttemptTimerImmediately()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $deferred = new Deferred();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(2))->method('connect')->willReturn($deferred->promise());

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            \React\Promise\resolve(array('127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $deferred->reject(new \RuntimeException('Connection refused'));

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 failed: Connection refused', $exception->getMessage());
    }

    public function testCancelConnectWillRejectPromiseAndCancelBothDnsLookups()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->never())->method('addTimer');

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->never())->method('connect');

        $cancelled = 0;
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            new Promise(function () { }, function () use (&$cancelled) {
                ++$cancelled;
                throw new \RuntimeException();
            }),
            new Promise(function () { }, function () use (&$cancelled) {
                ++$cancelled;
                throw new \RuntimeException();
            })
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $promise->cancel();

        $this->assertEquals(2, $cancelled);

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 cancelled during DNS lookup', $exception->getMessage());
    }

    public function testCancelConnectWillRejectPromiseAndCancelPendingIpv6LookupAndCancelDelayTimer()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->never())->method('connect');

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            new Promise(function () { }, $this->expectCallableOnce()),
            \React\Promise\resolve(array('127.0.0.1'))
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $promise->cancel();

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 cancelled during DNS lookup', $exception->getMessage());
    }

    public function testCancelConnectWillRejectPromiseAndCancelPendingIpv6ConnectionAttemptAndPendingIpv4LookupAndCancelAttemptTimer()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with(0.1, $this->anything())->willReturn($timer);
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        $cancelled = 0;
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80?hostname=reactphp.org')->willReturn(new Promise(function () { }, function () use (&$cancelled) {
            ++$cancelled;
            throw new \RuntimeException('Ignored message');
        }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('reactphp.org', Message::TYPE_AAAA),
            array('reactphp.org', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            \React\Promise\resolve(array('::1')),
            new Promise(function () { }, $this->expectCallableOnce())
        );

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->connect();
        $promise->cancel();

        $this->assertEquals(1, $cancelled);

        $exception = null;
        $promise->then(null, function ($e) use (&$exception) {
            $exception = $e;
        });

        $this->assertInstanceOf('RuntimeException', $exception);
        $this->assertEquals('Connection to tcp://reactphp.org:80 cancelled', $exception->getMessage());
    }

    public function testResolveWillReturnResolvedPromiseWithEmptyListWhenDnsResolverFails()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->once())->method('resolveAll')->with('reactphp.org', Message::TYPE_A)->willReturn(\React\Promise\reject(new \RuntimeException()));

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $promise = $builder->resolve(Message::TYPE_A, $this->expectCallableNever());

        $this->assertInstanceof('React\Promise\PromiseInterface', $promise);
        $promise->then($this->expectCallableOnceWith(array()), $this->expectCallableNever());
    }

    public function testAttemptConnectionWillConnectViaConnectorToGivenIpWithPortAndHostnameFromUriParts()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://10.1.1.1:80?hostname=reactphp.org')->willReturn(new Promise(function () { }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->never())->method('resolveAll');

        $uri = 'tcp://reactphp.org:80';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->attemptConnection('10.1.1.1');
    }

    public function testAttemptConnectionWillConnectViaConnectorToGivenIpv6WithAllUriParts()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80/path?test=yes&hostname=reactphp.org#start')->willReturn(new Promise(function () { }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->never())->method('resolveAll');

        $uri = 'tcp://reactphp.org:80/path?test=yes#start';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $builder->attemptConnection('::1');
    }

    public function testCheckCallsRejectFunctionImmediateWithoutLeavingDanglingPromiseWhenConnectorRejectsImmediately()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80/path?test=yes&hostname=reactphp.org#start')->willReturn(\React\Promise\reject(new \RuntimeException()));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->never())->method('resolveAll');

        $uri = 'tcp://reactphp.org:80/path?test=yes#start';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $ref = new \ReflectionProperty($builder, 'connectQueue');
        $ref->setAccessible(true);
        $ref->setValue($builder, array('::1'));

        $builder->check($this->expectCallableNever(), function () { });

        $ref = new \ReflectionProperty($builder, 'connectionPromises');
        $ref->setAccessible(true);
        $promises = $ref->getValue($builder);

        $this->assertEquals(array(), $promises);
    }

    public function testCleanUpCancelsAllPendingConnectionAttempts()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->exactly(2))->method('connect')->with('tcp://[::1]:80/path?test=yes&hostname=reactphp.org#start')->willReturnOnConsecutiveCalls(
            new Promise(function () { }, $this->expectCallableOnce()),
            new Promise(function () { }, $this->expectCallableOnce())
        );

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->never())->method('resolveAll');

        $uri = 'tcp://reactphp.org:80/path?test=yes#start';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $ref = new \ReflectionProperty($builder, 'connectQueue');
        $ref->setAccessible(true);
        $ref->setValue($builder, array('::1', '::1'));

        $builder->check($this->expectCallableNever(), function () { });
        $builder->check($this->expectCallableNever(), function () { });

        $builder->cleanUp();
    }

    public function testCleanUpCancelsAllPendingConnectionAttemptsWithoutStartingNewAttemptsDueToCancellationRejection()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('tcp://[::1]:80/path?test=yes&hostname=reactphp.org#start')->willReturn(new Promise(function () { }, function () {
            throw new \RuntimeException();
        }));

        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();
        $resolver->expects($this->never())->method('resolveAll');

        $uri = 'tcp://reactphp.org:80/path?test=yes#start';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);

        $ref = new \ReflectionProperty($builder, 'connectQueue');
        $ref->setAccessible(true);
        $ref->setValue($builder, array('::1', '::1'));

        $builder->check($this->expectCallableNever(), function () { });

        $builder->cleanUp();
    }

    public function testMixIpsIntoConnectQueueSometimesAssignsInOriginalOrder()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();

        $uri = 'tcp://reactphp.org:80/path?test=yes#start';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        for ($i = 0; $i < 100; ++$i) {
            $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);
            $builder->mixIpsIntoConnectQueue(array('::1', '::2'));

            $ref = new \ReflectionProperty($builder, 'connectQueue');
            $ref->setAccessible(true);
            $value = $ref->getValue($builder);

            if ($value === array('::1', '::2')) {
                break;
            }
        }

        $this->assertEquals(array('::1', '::2'), $value);
    }

    public function testMixIpsIntoConnectQueueSometimesAssignsInReverseOrder()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->getMock();

        $uri = 'tcp://reactphp.org:80/path?test=yes#start';
        $host = 'reactphp.org';
        $parts = parse_url($uri);

        for ($i = 0; $i < 100; ++$i) {
            $builder = new HappyEyeBallsConnectionBuilder($loop, $connector, $resolver, $uri, $host, $parts);
            $builder->mixIpsIntoConnectQueue(array('::1', '::2'));

            $ref = new \ReflectionProperty($builder, 'connectQueue');
            $ref->setAccessible(true);
            $value = $ref->getValue($builder);

            if ($value === array('::2', '::1')) {
                break;
            }
        }

        $this->assertEquals(array('::2', '::1'), $value);
    }
}
