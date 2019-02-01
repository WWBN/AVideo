<?php

namespace React\Tests\Socket;

use React\Promise;
use React\Socket\SecureConnector;

class SecureConnectorTest extends TestCase
{
    private $loop;
    private $tcp;
    private $connector;

    public function setUp()
    {
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->markTestSkipped('Not supported on your platform (outdated HHVM?)');
        }

        $this->loop = $this->getMock('React\EventLoop\LoopInterface');
        $this->tcp = $this->getMock('React\Socket\ConnectorInterface');
        $this->connector = new SecureConnector($this->tcp, $this->loop);
    }

    public function testConnectionWillWaitForTcpConnection()
    {
        $pending = new Promise\Promise(function () { });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->will($this->returnValue($pending));

        $promise = $this->connector->connect('example.com:80');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);
    }

    public function testConnectionWithCompleteUriWillBePassedThroughExpectForScheme()
    {
        $pending = new Promise\Promise(function () { });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80/path?query#fragment'))->will($this->returnValue($pending));

        $this->connector->connect('tls://example.com:80/path?query#fragment');
    }

    public function testConnectionToInvalidSchemeWillReject()
    {
        $this->tcp->expects($this->never())->method('connect');

        $promise = $this->connector->connect('tcp://example.com:80');

        $promise->then(null, $this->expectCallableOnce());
    }

    public function testCancelDuringTcpConnectionCancelsTcpConnection()
    {
        $pending = new Promise\Promise(function () { }, function () { throw new \Exception(); });
        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->will($this->returnValue($pending));

        $promise = $this->connector->connect('example.com:80');
        $promise->cancel();

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }

    public function testConnectionWillBeClosedAndRejectedIfConnectioIsNoStream()
    {
        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $connection->expects($this->once())->method('close');

        $this->tcp->expects($this->once())->method('connect')->with($this->equalTo('example.com:80'))->willReturn(Promise\resolve($connection));

        $promise = $this->connector->connect('example.com:80');

        $promise->then($this->expectCallableNever(), $this->expectCallableOnce());
    }
}
