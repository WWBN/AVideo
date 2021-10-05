<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\EventLoop\Factory;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\LimitingServer;
use React\Socket\TcpServer;

class LimitingServerTest extends TestCase
{
    const TIMEOUT = 0.1;

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
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = new TcpServer(0, $loop);

        $server = new LimitingServer($tcp, 100);

        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('error', array(new \RuntimeException('test')));
    }

    public function testSocketConnectionWillBeForwarded()
    {
        $connection = $this->getMockBuilder('React\Socket\ConnectionInterface')->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

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

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $tcp = new TcpServer(0, $loop);

        $server = new LimitingServer($tcp, 1);
        $server->on('connection', $this->expectCallableOnceWith($first));
        $server->on('error', $this->expectCallableOnce());

        $tcp->emit('connection', array($first));
        $tcp->emit('connection', array($second));
    }

    public function testPausingServerWillBePausedOnceLimitIsReached()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
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

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $connection->on('close', $resolve);
            });
        });

        Block\await($peer, $loop, self::TIMEOUT);

        $this->assertEquals(array(), $server->getConnections());
    }

    public function testPausingServerWillEmitOnlyOneButAcceptTwoConnectionsDueToOperatingSystem()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new LimitingServer($server, 1, true);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('error', $this->expectCallableNever());

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', $resolve);
        });

        $first = stream_socket_client($server->getAddress());
        $second = stream_socket_client($server->getAddress());

        Block\await($peer, $loop, self::TIMEOUT);

        fclose($first);
        fclose($second);
    }

    public function testPausingServerWillEmitTwoConnectionsFromBacklog()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new LimitingServer($server, 1, true);
        $server->on('error', $this->expectCallableNever());

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $connections = 0;
            $server->on('connection', function (ConnectionInterface $connection) use (&$connections, $resolve) {
                ++$connections;

                if ($connections >= 2) {
                    $resolve();
                }
            });
        });

        $first = stream_socket_client($server->getAddress());
        fclose($first);
        $second = stream_socket_client($server->getAddress());
        fclose($second);

        Block\await($peer, $loop, self::TIMEOUT);
    }
}
