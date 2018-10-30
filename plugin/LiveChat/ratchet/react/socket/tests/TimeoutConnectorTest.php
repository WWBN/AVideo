<?php

namespace React\Tests\Socket;

use React\Socket\TimeoutConnector;
use React\Promise;
use React\EventLoop\Factory;

class TimeoutConnectorTest extends TestCase
{
    public function testRejectsOnTimeout()
    {
        $promise = new Promise\Promise(function () { });

        $connector = $this->getMock('React\Socket\ConnectorInterface');
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 0.01, $loop);

        $timeout->connect('google.com:80')->then(
            $this->expectCallableNever(),
            $this->expectCallableOnce()
        );

        $loop->run();
    }

    public function testRejectsWhenConnectorRejects()
    {
        $promise = Promise\reject(new \RuntimeException());

        $connector = $this->getMock('React\Socket\ConnectorInterface');
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 5.0, $loop);

        $timeout->connect('google.com:80')->then(
            $this->expectCallableNever(),
            $this->expectCallableOnce()
        );

        $loop->run();
    }

    public function testResolvesWhenConnectorResolves()
    {
        $promise = Promise\resolve();

        $connector = $this->getMock('React\Socket\ConnectorInterface');
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

        $connector = $this->getMock('React\Socket\ConnectorInterface');
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

        $connector = $this->getMock('React\Socket\ConnectorInterface');
        $connector->expects($this->once())->method('connect')->with('google.com:80')->will($this->returnValue($promise));

        $loop = Factory::create();

        $timeout = new TimeoutConnector($connector, 0.01, $loop);

        $out = $timeout->connect('google.com:80');
        $out->cancel();

        $out->then($this->expectCallableNever(), $this->expectCallableOnce());
    }
}
