<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\Socket\TimeoutConnector;
use React\Promise;
use React\EventLoop\Factory;
use React\Promise\Deferred;

class TimeoutConnectorTest extends TestCase
{
    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $base = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();

        $connector = new TimeoutConnector($base, 0.01);

        $ref = new \ReflectionProperty($connector, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($connector);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testRejectsWithTimeoutReasonOnTimeout()
    {
        $promise = new Promise\Promise(function () { });

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 0.01, $loop);

        $this->setExpectedException('RuntimeException', 'Connection to google.com:80 timed out after 0.01 seconds');
        Block\await($timeout->connect('google.com:80'), $loop);
    }

    public function testRejectsWithOriginalReasonWhenConnectorRejects()
    {
        $promise = Promise\reject(new \RuntimeException('Failed'));

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 5.0, $loop);

        $this->setExpectedException('RuntimeException', 'Failed');
        Block\await($timeout->connect('google.com:80'), $loop);
    }

    public function testResolvesWhenConnectorResolves()
    {
        $promise = Promise\resolve();

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 5.0, $loop);

        $timeout->connect('google.com:80')->then(
            $this->expectCallableOnce(),
            $this->expectCallableNever()
        );

        $loop->run();
    }

    public function testRejectsAndCancelsPendingPromiseOnTimeout()
    {
        $promise = new Promise\Promise(function () { }, $this->expectCallableOnce());

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 0.01, $loop);

        $timeout->connect('google.com:80')->then(
            $this->expectCallableNever(),
            $this->expectCallableOnce()
        );

        $loop->run();
    }

    public function testCancelsPendingPromiseOnCancel()
    {
        $promise = new Promise\Promise(function () { }, function () { throw new \Exception(); });

        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 0.01, $loop);

        $out = $timeout->connect('google.com:80');
        $out->cancel();

        $out->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testRejectionDuringConnectionShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $connection = new Deferred();
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('example.com:80')->willReturn($connection->promise());

        $loop = Factory::create();
        $timeout = new TimeoutConnector($connector, 0.01, $loop);

        $promise = $timeout->connect('example.com:80');
        $connection->reject(new \RuntimeException('Connection failed'));
        unset($promise, $connection);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testRejectionDueToTimeoutShouldNotCreateAnyGarbageReferences()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $connection = new Deferred(function () {
            throw new \RuntimeException('Connection cancelled');
        });
        $connector = $this->getMockBuilder('React\Socket\ConnectorInterface')->getMock();
        $connector->expects($this->once())->method('connect')->with('example.com:80')->willReturn($connection->promise());

        $loop = Factory::create();
        $timeout = new TimeoutConnector($connector, 0, $loop);

        $promise = $timeout->connect('example.com:80');

        $loop->run();
        unset($promise, $connection);

        $this->assertEquals(0, gc_collect_cycles());
    }
}
