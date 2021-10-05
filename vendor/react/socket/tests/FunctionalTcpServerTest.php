<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\EventLoop\Factory;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\TcpConnector;
use React\Socket\TcpServer;

class FunctionalTcpServerTest extends TestCase
{
    const TIMEOUT = 0.1;

    public function testEmitsConnectionForNewConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableOnce());

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', $resolve);
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\await($peer, $loop, self::TIMEOUT);
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

        Block\await($promise, $loop, self::TIMEOUT);
    }

    public function testConnectionForNewConnectionWhenResumedAfterPause()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->pause();
        $server->resume();

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', $resolve);
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\await($peer, $loop, self::TIMEOUT);
    }

    public function testEmitsConnectionWithRemoteIp()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve($connection->getRemoteAddress());
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $peer = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertContainsString('127.0.0.1:', $peer);
    }

    public function testEmitsConnectionWithLocalIp()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve($connection->getLocalAddress());
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $promise->then($this->expectCallableOnce());

        $local = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertContainsString('127.0.0.1:', $local);
        $this->assertEquals($server->getAddress(), $local);
    }

    public function testEmitsConnectionWithLocalIpDespiteListeningOnAll()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Skipping on Windows due to default firewall rules');
        }

        $loop = Factory::create();

        $server = new TcpServer('0.0.0.0:0', $loop);
        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve($connection->getLocalAddress());
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $local = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertContainsString('127.0.0.1:', $local);
    }

    public function testEmitsConnectionWithRemoteIpAfterConnectionIsClosedByPeer()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $connection->on('close', function () use ($connection, $resolve) {
                    $resolve($connection->getRemoteAddress());
                });
            });
        });

        $connector = new TcpConnector($loop);
        $connector->connect($server->getAddress())->then(function (ConnectionInterface $connection) {
            $connection->end();
        });

        $peer = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertContainsString('127.0.0.1:', $peer);
    }

    public function testEmitsConnectionWithRemoteNullAddressAfterConnectionIsClosedByServer()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $connection->close();
                $resolve($connection->getRemoteAddress());
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $peer = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertNull($peer);
    }

    public function testEmitsConnectionEvenIfClientConnectionIsCancelled()
    {
        if (PHP_OS !== 'Linux') {
            $this->markTestSkipped('Linux only (OS is ' . PHP_OS . ')');
        }

        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server->on('connection', $this->expectCallableOnce());

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', $resolve);
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());
        $promise->cancel();

        $promise->then(null, $this->expectCallableOnce());

        Block\await($peer, $loop, self::TIMEOUT);
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

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', $resolve);
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        Block\await($peer, $loop, self::TIMEOUT);
    }

    public function testEmitsConnectionWithRemoteIpv6()
    {
        $loop = Factory::create();

        try {
            $server = new TcpServer('[::1]:0', $loop);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Unable to start IPv6 server socket (not available on your platform?)');
        }

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve($connection->getRemoteAddress());
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $peer = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertContainsString('[::1]:', $peer);
    }

    public function testEmitsConnectionWithLocalIpv6()
    {
        $loop = Factory::create();

        try {
            $server = new TcpServer('[::1]:0', $loop);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped('Unable to start IPv6 server socket (not available on your platform?)');
        }

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve($connection->getLocalAddress());
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $local = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertContainsString('[::1]:', $local);
        $this->assertEquals($server->getAddress(), $local);
    }

    public function testServerPassesContextOptionsToSocket()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop, array(
            'backlog' => 4
        ));

        $ref = new \ReflectionProperty($server, 'master');
        $ref->setAccessible(true);
        $socket = $ref->getValue($server);

        $context = stream_context_get_options($socket);

        $this->assertEquals(array('socket' => array('backlog' => 4)), $context);
    }

    public function testServerPassesDefaultBacklogSizeViaContextOptionsToSocket()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);

        $ref = new \ReflectionProperty($server, 'master');
        $ref->setAccessible(true);
        $socket = $ref->getValue($server);

        $context = stream_context_get_options($socket);

        $this->assertEquals(array('socket' => array('backlog' => 511)), $context);
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

        $peer = new Promise(function ($resolve, $reject) use ($server) {
            $server->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve(stream_context_get_options($connection->stream));
            });
        });

        $connector = new TcpConnector($loop);
        $promise = $connector->connect($server->getAddress());

        $promise->then($this->expectCallableOnce());

        $all = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertEquals(array('socket' => array('backlog' => 4)), $all);
    }

    public function testFailsToListenOnInvalidUri()
    {
        $loop = Factory::create();

        $this->setExpectedException('InvalidArgumentException');
        new TcpServer('///', $loop);
    }

    public function testFailsToListenOnUriWithoutPort()
    {
        $loop = Factory::create();

        $this->setExpectedException('InvalidArgumentException');
        new TcpServer('127.0.0.1', $loop);
    }

    public function testFailsToListenOnUriWithWrongScheme()
    {
        $loop = Factory::create();

        $this->setExpectedException('InvalidArgumentException');
        new TcpServer('udp://127.0.0.1:0', $loop);
    }

    public function testFailsToListenOnUriWIthHostname()
    {
        $loop = Factory::create();

        $this->setExpectedException('InvalidArgumentException');
        new TcpServer('localhost:8080', $loop);
    }
}
