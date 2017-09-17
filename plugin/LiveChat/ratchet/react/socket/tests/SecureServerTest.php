<?php

namespace React\Tests\Socket;

use React\Socket\SecureServer;
use React\Socket\TcpServer;

class SecureServerTest extends TestCase
{
    public function setUp()
    {
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->markTestSkipped('Not supported on your platform (outdated HHVM?)');
        }
    }

    public function testGetAddressWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('getAddress')->willReturn('127.0.0.1:1234');

        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $server = new SecureServer($tcp, $loop, array());

        $this->assertEquals('127.0.0.1:1234', $server->getAddress());
    }

    public function testPauseWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('pause');

        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $server = new SecureServer($tcp, $loop, array());

        $server->pause();
    }

    public function testResumeWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('resume');

        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $server = new SecureServer($tcp, $loop, array());

        $server->resume();
    }

    public function testCloseWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('close');

        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $server = new SecureServer($tcp, $loop, array());

        $server->close();
    }

    public function testConnectionWillBeEndedWithErrorIfItIsNotAStream()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $tcp = new TcpServer(0, $loop);

        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $connection->expects($this->once())->method('end');

        $server = new SecureServer($tcp, $loop, array());

        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('connection', array($connection));
    }

    public function testSocketErrorWillBeForwarded()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $tcp = new TcpServer(0, $loop);

        $server = new SecureServer($tcp, $loop, array());

        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('error', array(new \RuntimeException('test')));
    }
}
