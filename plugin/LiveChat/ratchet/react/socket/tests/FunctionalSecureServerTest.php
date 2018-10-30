<?php

namespace React\Tests\Socket;

use React\EventLoop\Factory;
use React\Socket\SecureServer;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;
use React\Socket\TcpConnector;
use React\Socket\SecureConnector;
use Clue\React\Block;

class FunctionalSecureServerTest extends TestCase
{
    const TIMEOUT = 0.5;

    public function setUp()
    {
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->markTestSkipped('Not supported on your platform (outdated HHVM?)');
        }
    }

    public function testEmitsConnectionForNewConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        Block\await($promise, $loop, self::TIMEOUT);
    }

    public function testWritesDataToConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $server->on('connection', function (ConnectionInterface $conn) {
            $conn->write('foo');
        });

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $local = Block\await($promise, $loop, self::TIMEOUT);
        /* @var $local ConnectionInterface */

        $local->on('data', $this->expectCallableOnceWith('foo'));

        Block\sleep(self::TIMEOUT, $loop);
    }

    public function testWritesDataInMultipleChunksToConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $server->on('connection', function (ConnectionInterface $conn) {
            $conn->write(str_repeat('*', 400000));
        });

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $local = Block\await($promise, $loop, self::TIMEOUT);
        /* @var $local React\Stream\Stream */

        $received = 0;
        $local->on('data', function ($chunk) use (&$received) {
            $received += strlen($chunk);
        });

        Block\sleep(self::TIMEOUT, $loop);

        $this->assertEquals(400000, $received);
    }

    public function testWritesMoreDataInMultipleChunksToConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $server->on('connection', function (ConnectionInterface $conn) {
            $conn->write(str_repeat('*', 2000000));
        });

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $local = Block\await($promise, $loop, self::TIMEOUT);
        /* @var $local React\Stream\Stream */

        $received = 0;
        $local->on('data', function ($chunk) use (&$received) {
            $received += strlen($chunk);
        });

        Block\sleep(self::TIMEOUT, $loop);

        $this->assertEquals(2000000, $received);
    }

    public function testEmitsDataFromConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $once = $this->expectCallableOnceWith('foo');
        $server->on('connection', function (ConnectionInterface $conn) use ($once) {
            $conn->on('data', $once);
        });

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $local = Block\await($promise, $loop, self::TIMEOUT);
        /* @var $local React\Stream\Stream */

        $local->write("foo");

        Block\sleep(self::TIMEOUT, $loop);
    }

    public function testEmitsDataInMultipleChunksFromConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $received = 0;
        $server->on('connection', function (ConnectionInterface $conn) use (&$received) {
            $conn->on('data', function ($chunk) use (&$received) {
                $received += strlen($chunk);
            });
        });

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $local = Block\await($promise, $loop, self::TIMEOUT);
        /* @var $local React\Stream\Stream */

        $local->write(str_repeat('*', 400000));

        Block\sleep(self::TIMEOUT, $loop);

        $this->assertEquals(400000, $received);
    }

    public function testPipesDataBackInMultipleChunksFromConnection()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $server->on('connection', function (ConnectionInterface $conn) use (&$received) {
            $conn->pipe($conn);
        });

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $local = Block\await($promise, $loop, self::TIMEOUT);
        /* @var $local React\Stream\Stream */

        $received = 0;
        $local->on('data', function ($chunk) use (&$received) {
            $received += strlen($chunk);
        });

        $local->write(str_repeat('*', 400000));

        Block\sleep(self::TIMEOUT, $loop);

        $this->assertEquals(400000, $received);
    }

    public function testEmitsConnectionForNewConnectionWithEncryptedCertificate()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost_swordfish.pem',
            'passphrase' => 'swordfish'
        ));
        $server->on('connection', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        Block\await($promise, $loop, self::TIMEOUT);
    }

    public function testEmitsErrorForServerWithInvalidCertificate()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => 'invalid.pem'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $this->setExpectedException('RuntimeException', 'handshake');
        Block\await($promise, $loop, self::TIMEOUT);
    }

    public function testEmitsErrorForServerWithEncryptedCertificateMissingPassphrase()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost_swordfish.pem'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $this->setExpectedException('RuntimeException', 'handshake');
        Block\await($promise, $loop, self::TIMEOUT);
    }

    public function testEmitsErrorForServerWithEncryptedCertificateWithInvalidPassphrase()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost_swordfish.pem',
            'passphrase' => 'nope'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());

        $this->setExpectedException('RuntimeException', 'handshake');
        Block\await($promise, $loop, self::TIMEOUT);
    }

    public function testEmitsErrorForConnectionWithPeerVerification()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => true
        ));
        $promise = $connector->connect($server->getAddress());

        $promise->then(null, $this->expectCallableOnce());
        Block\sleep(self::TIMEOUT, $loop);
    }

    public function testEmitsErrorIfConnectionIsCancelled()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableOnce());

        $connector = new SecureConnector(new TcpConnector($loop), $loop, array(
            'verify_peer' => false
        ));
        $promise = $connector->connect($server->getAddress());
        $promise->cancel();

        $promise->then(null, $this->expectCallableOnce());
        Block\sleep(self::TIMEOUT, $loop);
    }

    public function testEmitsNothingIfConnectionIsIdle()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableNever());

        $connector = new TcpConnector($loop);
        $promise = $connector->connect(str_replace('tls://', '', $server->getAddress()));

        $promise->then($this->expectCallableOnce());
        Block\sleep(self::TIMEOUT, $loop);
    }

    public function testEmitsErrorIfConnectionIsNotSecureHandshake()
    {
        $loop = Factory::create();

        $server = new TcpServer(0, $loop);
        $server = new SecureServer($server, $loop, array(
            'local_cert' => __DIR__ . '/../examples/localhost.pem'
        ));
        $server->on('connection', $this->expectCallableNever());
        $server->on('error', $this->expectCallableOnce());

        $connector = new TcpConnector($loop);
        $promise = $connector->connect(str_replace('tls://', '', $server->getAddress()));

        $promise->then(function (ConnectionInterface $stream) {
            $stream->write("GET / HTTP/1.0\r\n\r\n");
        });

        Block\sleep(self::TIMEOUT, $loop);
    }
}
