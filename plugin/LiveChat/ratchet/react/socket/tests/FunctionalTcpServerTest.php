<?php

namespace React\Tests\Socket;

use React\EventLoop\Factory;
use React\Socket\TcpServer;
use React\Socket\ConnectionInterface;
use React\Socket\TcpConnector;
use Clue\React\Block;

class FunctionalTcpServerTest extends TestCase
{
    public function testEmitsConnectionForNewConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableOnce());

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);
    }

    public function testEmitsNoConnectionForNewConnectionWhenPaused()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableNever());
        $server->pause();

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);
    }

    public function testEmitsConnectionForNewConnectionWhenResumedAfterPause()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->pause();
        $server->resume();

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);
    }

    public function testEmitsConnectionWithRemoteIp()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$peer) {
            $peer = $conn->getRemoteAddress();
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertContains('127.0.0.1:', $peer);
    }

    public function testEmitsConnectionWithLocalIp()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $local = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$local) {
            $local = $conn->getLocalAddress();
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertContains('127.0.0.1:', $local);
        $this->assertEquals($server->getAddress(), $local);
    }

    public function testEmitsConnectionWithLocalIpDespiteListeningOnAll()
    {
        $loop = Factory::create();

        $server = new TcpServer('0.0.0.0:0', $loop);
        $local = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$local) {
            $local = $conn->getLocalAddress();
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertContains('127.0.0.1:', $local);
    }

    public function testEmitsConnectionWithRemoteIpAfterConnectionIsClosedByPeer()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$peer) {
            $conn->on('close', function () use ($conn, &$peer) {
                $peer = $conn->getRemoteAddress();
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $client = Block\await($promise, $loop, 0.1);
        $client->end();

        Block\sleep(0.1, $loop);

        $this->assertContains('127.0.0.1:', $peer);
    }

    public function testEmitsConnectionWithRemoteNullAddressAfterConnectionIsClosedLocally()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$peer) {
            $conn->close();
            $peer = $conn->getRemoteAddress();
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertNull($peer);
    }

    public function testEmitsConnectionEvenIfConnectionIsCancelled()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableOnce());

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());
        $promise->cancel();

        $promise->then(null, $this->expectCallableOnce());

        Block\sleep(0.1, $loop);
    }

    public function testEmitsConnectionForNewIpv6Connection()
    {
        $loop = Factory::create();

        try {
            $server = new TcpServer('[::1]:0', $loop);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Unable to start IPv6 server socket (not available on your platform?)');
        }

        $server->on('connection', $this->expectCallableOnce());

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);
    }

    public function testEmitsConnectionWithRemoteIpv6()
    {
        $loop = Factory::create();

        try {
            $server = new TcpServer('[::1]:0', $loop);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Unable to start IPv6 server socket (not available on your platform?)');
        }

        $peer = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$peer) {
            $peer = $conn->getRemoteAddress();
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertContains('[::1]:', $peer);
    }

    public function testEmitsConnectionWithLocalIpv6()
    {
        $loop = Factory::create();

        try {
            $server = new TcpServer('[::1]:0', $loop);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Unable to start IPv6 server socket (not available on your platform?)');
        }

        $local = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$local) {
            $local = $conn->getLocalAddress();
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertContains('[::1]:', $local);
        $this->assertEquals($server->getAddress(), $local);
    }

    public function testEmitsConnectionWithInheritedContextOptions()
    {
        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.13', '<')) {
            // https://3v4l.org/hB4Tc
            $this->markTestSkipped('Not supported on legacy HHVM < 3.13');
        }

        $loop = Factory::create();

        $server = new TcpServer(0, $loop, array(
            'backlog' => 4
        ));

        $all = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$all) {
            $all = stream_context_get_options($conn->stream);
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\sleep(0.1, $loop);

        $this->assertEquals(array('socket' => array('backlog' => 4)), $all);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsToListenOnInvalidUri()
    {
        $loop = Factory::create();

        new TcpServer('///', $loop);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsToListenOnUriWithoutPort()
    {
        $loop = Factory::create();

        new TcpServer('127.0.0.1', $loop);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsToListenOnUriWithWrongScheme()
    {
        $loop = Factory::create();

        new TcpServer('udp://127.0.0.1:0', $loop);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFailsToListenOnUriWIthHostname()
    {
        $loop = Factory::create();

        new TcpServer('localhost:8080', $loop);
    }
}
