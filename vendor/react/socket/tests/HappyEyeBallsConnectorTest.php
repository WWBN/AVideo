<?php

namespace React\Tests\Socket;

use React\Dns\Model\Message;
use React\EventLoop\StreamSelectLoop;
use React\Promise;
use React\Promise\Deferred;
use React\Socket\HappyEyeBallsConnector;
use Clue\React\Block;

class HappyEyeBallsConnectorTest extends TestCase
{
    private $loop;
    private $tcp;
    private $resolver;
    private $connector;
    private $connection;

    /**
     * @before
     */
    public function setUpMocks()
    {
        $this->loop = new TimerSpeedUpEventLoop(new StreamSelectLoop());
        $this->tcp = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $this->resolver = $this->getMockBuilder('React\Dns\Resolver\ResolverInterface')->disableOriginalConstructor()->getMock();
        $this->connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();

        $this->connector = new HappyEyeBallsConnector($this->loop, $this->tcp, $this->resolver);
    }

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $connector = new HappyEyeBallsConnector(null, $this->tcp, $this->resolver);

        $ref = new \ReflectionProperty($connector, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($connector);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testConstructWithoutRequiredConnectorThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new HappyEyeBallsConnector(null, null, $this->resolver);
    }

    public function testConstructWithoutRequiredResolverThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new HappyEyeBallsConnector(null, $this->tcp);
    }

    public function testHappyFlow()
    {
        $first = new Deferred();
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('example.com'), $this->anything())->willReturn($first->promise());
        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $this->tcp->expects($this->exactly(1))->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=example.com'))->willReturn(Promise\resolve($connection));

        $promise = $this->connector->connect('example.com:80');
        $first->resolve(array('1.2.3.4'));

        $resolvedConnection = Block\await($promise, $this->loop);

        self::assertSame($connection, $resolvedConnection);
    }

    public function testThatAnyOtherPendingConnectionAttemptsWillBeCanceledOnceAConnectionHasBeenEstablished()
    {
        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $lookupAttempts = array(
            Promise\reject(new \Exception('error')),
            Promise\resolve(array('1.2.3.4', '5.6.7.8', '9.10.11.12')),
        );
        $connectionAttempts = array(
            new Promise\Promise(function () {}, $this->expectCallableOnce()),
            Promise\resolve($connection),
            new Promise\Promise(function () {}, $this->expectCallableNever()),
        );
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('example.com'), $this->anything())->will($this->returnCallback(function () use (&$lookupAttempts) {
            return array_shift($lookupAttempts);
        }));
        $this->tcp->expects($this->exactly(2))->method('connect')->with($this->isType('string'))->will($this->returnCallback(function () use (&$connectionAttempts) {
            return array_shift($connectionAttempts);
        }));

        $promise = $this->connector->connect('example.com:80');

        $resolvedConnection = Block\await($promise, $this->loop);

        self::assertSame($connection, $resolvedConnection);
    }

    public function testPassByResolverIfGivenIp()
    {
        $this->resolver->expects($this->never())->method('resolveAll');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('127.0.0.1:80'))->will($this->returnValue(Promise\resolve()));

        $this->connector->connect('127.0.0.1:80');

        $this->loop->run();
    }

    public function testPassByResolverIfGivenIpv6()
    {
        $this->resolver->expects($this->never())->method('resolveAll');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('[::1]:80'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('[::1]:80');

        $this->loop->run();
    }

    public function testPassThroughResolverIfGivenHost()
    {
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('google.com'), $this->anything())->will($this->returnValue(Promise\resolve(array('1.2.3.4'))));
        $this->tcp->expects($this->exactly(2))->method('connect')->with($this->equalTo('1.2.3.4:80?hostname=google.com'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('google.com:80');

        $this->loop->run();
    }

    public function testPassThroughResolverIfGivenHostWhichResolvesToIpv6()
    {
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('google.com'), $this->anything())->will($this->returnValue(Promise\resolve(array('::1'))));
        $this->tcp->expects($this->exactly(2))->method('connect')->with($this->equalTo('[::1]:80?hostname=google.com'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('google.com:80');

        $this->loop->run();
    }

    public function testPassByResolverIfGivenCompleteUri()
    {
        $this->resolver->expects($this->never())->method('resolveAll');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('scheme://127.0.0.1:80/path?query#fragment'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://127.0.0.1:80/path?query#fragment');

        $this->loop->run();
    }

    public function testPassThroughResolverIfGivenCompleteUri()
    {
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('google.com'), $this->anything())->will($this->returnValue(Promise\resolve(array('1.2.3.4'))));
        $this->tcp->expects($this->exactly(2))->method('connect')->with($this->equalTo('scheme://1.2.3.4:80/path?query&hostname=google.com#fragment'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://google.com:80/path?query#fragment');

        $this->loop->run();
    }

    public function testPassThroughResolverIfGivenExplicitHost()
    {
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('google.com'), $this->anything())->will($this->returnValue(Promise\resolve(array('1.2.3.4'))));
        $this->tcp->expects($this->exactly(2))->method('connect')->with($this->equalTo('scheme://1.2.3.4:80/?hostname=google.de'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://google.com:80/?hostname=google.de');

        $this->loop->run();
    }

    /**
     * @dataProvider provideIpvAddresses
     */
    public function testIpv6ResolvesFirstSoIsTheFirstToConnect(array $ipv6, array $ipv4)
    {
        $deferred = new Deferred();

        $this->resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('google.com', Message::TYPE_AAAA),
            array('google.com', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            $this->returnValue(Promise\resolve($ipv6)),
            $this->returnValue($deferred->promise())
        );
        $this->tcp->expects($this->any())->method('connect')->with($this->stringContains(']:80/?hostname=google.com'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://google.com:80/?hostname=google.com');

        $this->loop->addTimer(0.07, function () use ($deferred) {
            $deferred->reject(new \RuntimeException());
        });

        $this->loop->run();
    }

    /**
     * @dataProvider provideIpvAddresses
     */
    public function testIpv6DoesntResolvesWhileIpv4DoesFirstSoIpv4Connects(array $ipv6, array $ipv4)
    {
        $deferred = new Deferred();

        $this->resolver->expects($this->exactly(2))->method('resolveAll')->withConsecutive(
            array('google.com', Message::TYPE_AAAA),
            array('google.com', Message::TYPE_A)
        )->willReturnOnConsecutiveCalls(
            $this->returnValue($deferred->promise()),
            $this->returnValue(Promise\resolve($ipv4))
        );
        $this->tcp->expects($this->any())->method('connect')->with($this->stringContains(':80/?hostname=google.com'))->will($this->returnValue(Promise\reject()));

        $this->connector->connect('scheme://google.com:80/?hostname=google.com');

        $this->loop->addTimer(0.07, function () use ($deferred) {
            $deferred->reject(new \RuntimeException());
        });

        $this->loop->run();
    }

    public function testRejectsImmediatelyIfUriIsInvalid()
    {
        $this->resolver->expects($this->never())->method('resolveAll');
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('////');

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());

        $this->loop->run();
    }

    public function testRejectsWithTcpConnectorRejectionIfGivenIp()
    {
        $that = $this;
        $promise = Promise\reject(new \RuntimeException('Connection failed'));
        $this->resolver->expects($this->never())->method('resolveAll');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80'))->willReturn($promise);

        $promise = $this->connector->connect('1.2.3.4:80');
        $this->loop->addTimer(0.5, function () use ($that, $promise) {
            $promise->cancel();

            $that->throwRejection($promise);
        });

        $this->setExpectedException('RuntimeException', 'Connection failed');
        $this->loop->run();
    }

    public function testSkipConnectionIfDnsFails()
    {
        $that = $this;
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with($this->equalTo('example.invalid'), $this->anything())->willReturn(Promise\reject(new \RuntimeException('DNS error')));
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.invalid:80');

        $this->loop->addTimer(0.5, function () use ($that, $promise) {
            $that->throwRejection($promise);
        });

        $this->setExpectedException('RuntimeException', 'Connection to example.invalid:80 failed during DNS lookup: DNS error');
        $this->loop->run();
    }

    public function testCancelDuringDnsCancelsDnsAndDoesNotStartTcpConnection()
    {
        $that = $this;
        $this->resolver->expects($this->exactly(2))->method('resolveAll')->with('example.com', $this->anything())->will($this->returnCallback(function () use ($that) {
            return new Promise\Promise(function () { }, $that->expectCallableExactly(1));
        }));
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('example.com:80');
        $this->loop->addTimer(0.05, function () use ($that, $promise) {
            $promise->cancel();

            $that->throwRejection($promise);
        });

        $this->setExpectedException('RuntimeException', 'Connection to example.com:80 cancelled during DNS lookup');
        $this->loop->run();
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnectionIfGivenIp()
    {
        $pending = new Promise\Promise(function () { }, $this->expectCallableOnce());
        $this->resolver->expects($this->never())->method('resolveAll');
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('1.2.3.4:80'))->willReturn($pending);

        $promise = $this->connector->connect('1.2.3.4:80');
        $this->loop->addTimer(0.1, function () use ($promise) {
            $promise->cancel();
        });

        $this->loop->run();
    }

    /**
     * @internal
     */
    public function throwRejection($promise)
    {
        $ex = null;
        $promise->then(null, function ($e) use (&$ex) {
            $ex = $e;
        });

        throw $ex;
    }

    public function provideIpvAddresses()
    {
        $ipv6 = array(
            array(),
            array('1:2:3:4'),
            array('1:2:3:4', '5:6:7:8'),
            array('1:2:3:4', '5:6:7:8', '9:10:11:12'),
        );
        $ipv4 = array(
            array('1.2.3.4'),
            array('1.2.3.4', '5.6.7.8'),
            array('1.2.3.4', '5.6.7.8', '9.10.11.12'),
        );

        $ips = array();

        foreach ($ipv6 as $v6) {
            foreach ($ipv4 as $v4) {
                $ips[] = array(
                    $v6,
                    $v4
                );
            }
        }

        return $ips;
    }
}
