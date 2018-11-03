<?php

namespace React\Tests\Socket;

use React\Socket\LimitingServer;
use React\Socket\TcpServer;
use React\EventLoop\Factory;
use Clue\React\Block;

class LimitingServerTest extends TestCase
{
    public function testGetAddressWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('getAddress')->willReturn('127.0.0.1:1234');

        $server = new LimitingServer($tcp, 100);

        $this->assertEquals('127.0.0.1:1234', $server->getAddress());
    }

    public function testPauseWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('pause');

        $server = new LimitingServer($tcp, 100);

        $server->pause();
    }

    public function testPauseTwiceWillBePassedThroughToTcpServerOnce()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('pause');

        $server = new LimitingServer($tcp, 100);

        $server->pause();
        $server->pause();
    }

    public function testResumeWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('resume');

        $server = new LimitingServer($tcp, 100);

        $server->pause();
        $server->resume();
    }

    public function testResumeTwiceWillBePassedThroughToTcpServerOnce()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('resume');

        $server = new LimitingServer($tcp, 100);

        $server->pause();
        $server->resume();
        $server->resume();
    }

    public function testCloseWillBePassedThroughToTcpServer()
    {
        $tcp = $this->getMockBuilder('React\Socket\ServerInterface')->getMock();
        $tcp->expects($this->once())->method('close');

        $server = new LimitingServer($tcp, 100);

        $server->close();
    }

    public function testSocketErrorWillBeForwarded()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $tcp = new TcpServer(0, $loop);

        $server = new LimitingServer($tcp, 100);

        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('error', array(new \RuntimeException('test')));
    }

    public function testSocketConnectionWillBeForwarded()
    {
        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();

        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $tcp = new TcpServer(0, $loop);

        $server = new LimitingServer($tcp, 100);
        $server->on('connection', $this->expectCallableOnceWith($connection));
        $server->on('error', $this->expectCallableNever());

        $tcp->emit('connection', array($connection));

        $this->assertEquals(array($connection), $server->getConnections());
    }

    public function testSocketConnectionWillBeClosedOnceLimitIsReached()
    {
        $first = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $first->expects($this->never())->method('close');
        $second = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();
        $second->expects($this->once())->method('close');

        $loop = $this->getMock('React\EventLoop\LoopInterface');

        $tcp = new TcpServer(0, $loop);

        $server = new LimitingServer($tcp, 1);
        $server->on('connection', $this->expectCallableOnceWith($first));
        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('connection', array($first));
        $tcp->emit('connection', array($second));
    }

    public function testPausingServerWillBePausedOnceLimitIsReached()
    {
        $loop = $this->getMock('React\EventLoop\LoopInterface');
        $loop->expects($this->once())->method('addReadStream');
        $loop->expects($this->once())->method('removeReadStream');

        $tcp = new TcpServer(0, $loop);

        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();

        $server = new LimitingServer($tcp, 1, true);

        $tcp->emit('connection', array($connection));
    }

    public function testSocketDisconnectionWillRemoveFromList()
    {
        $loop = Factory::create();

        $tcp = new TcpServer(0, $loop);

        $socket = stream_socket_client($tcp->getAddress());
        fclose($socket);

        $server = new LimitingServer($tcp, 100);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('error', $this->expectCallableNever());

        Block\sleep(0.1, $loop);

        $this->assertEquals(array(), $server->getConnections());
    }

    public function testPausingServerWillEmitOnlyOneButAcceptTwoConnectionsDueToOperatingSystem()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new LimitingServer($server, 1, true);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('error', $this->expectCallableNever());

        $first = stream_socket_client($server->getAddress());
        $second = stream_socket_client($server->getAddress());

        Block\sleep(0.1, $loop);

        fclose($first);
        fclose($second);
    }

    public function testPausingServerWillEmitTwoConnectionsFromBacklog()
    {
        $loop = Factory::create();

        $twice = $this->createCallableMock();
        $twice->expects($this->exactly(2))->method('__invoke');

        $server = new TcpServer(0, $loop);
        $server = new LimitingServer($server, 1, true);
        $server->on('connection', $twice);
        $server->on('error', $this->expectCallableNever());

        $first = stream_socket_client($server->getAddress());
        fclose($first);
        $second = stream_socket_client($server->getAddress());
        fclose($second);

        Block\sleep(0.1, $loop);
    }
}
