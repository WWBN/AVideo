<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\EventLoop\Factory;
use React\Promise\Promise;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use React\Socket\TcpConnector;
use React\Socket\UnixConnector;

class SocketServerTest extends TestCase
{
    const TIMEOUT = 0.1;

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $socket = new SocketServer('127.0.0.1:0');

        $ref = new \ReflectionProperty($socket, 'server');
        $ref->setAccessible(true);
        $tcp = $ref->getValue($socket);

        $ref = new \ReflectionProperty($tcp, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($tcp);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    public function testCreateServerWithZeroPortAssignsRandomPort()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);
        $this->assertNotEquals(0, $socket->getAddress());
        $socket->close();
    }

    public function testConstructorWithInvalidUriThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new SocketServer('invalid URI');
    }

    public function testConstructorWithInvalidUriWithPortOnlyThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new SocketServer('0');
    }

    public function testConstructorWithInvalidUriWithSchemaAndPortOnlyThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        new SocketServer('tcp://0');
    }

    public function testConstructorCreatesExpectedTcpServer()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);

        $connector = new TcpConnector($loop);
        $connector->connect($socket->getAddress())
            ->then($this->expectCallableOnce(), $this->expectCallableNever());

        $connection = Block\await($connector->connect($socket->getAddress()), $loop, self::TIMEOUT);

        $connection->close();
        $socket->close();
    }

    public function testConstructorCreatesExpectedUnixServer()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }
        if (!in_array('unix', stream_get_transports())) {
            $this->markTestSkipped('Unix domain sockets (UDS) not supported on your platform (Windows?)');
        }

        $loop = Factory::create();

        $socket = new SocketServer($this->getRandomSocketUri(), array(), $loop);

        $connector = new UnixConnector($loop);
        $connector->connect($socket->getAddress())
            ->then($this->expectCallableOnce(), $this->expectCallableNever());

        $connection = Block\await($connector->connect($socket->getAddress()), $loop, self::TIMEOUT);

        $connection->close();
        $socket->close();
    }

    public function testConstructorThrowsForExistingUnixPath()
    {
        if (!in_array('unix', stream_get_transports())) {
            $this->markTestSkipped('Unix domain sockets (UDS) not supported on your platform (Windows?)');
        }

        $loop = Factory::create();

        try {
            new SocketServer('unix://' . __FILE__, array(), $loop);
            $this->fail();
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 0) {
                // Zend PHP does not currently report a sane error
                $this->assertStringEndsWith('Unknown error', $e->getMessage());
            } else {
                $this->assertEquals(SOCKET_EADDRINUSE, $e->getCode());
                $this->assertStringEndsWith('Address already in use', $e->getMessage());
            }
        }
    }

    public function testEmitsErrorWhenUnderlyingTcpServerEmitsError()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);

        $ref = new \ReflectionProperty($socket, 'server');
        $ref->setAccessible(true);
        $tcp = $ref->getvalue($socket);

        $error = new \RuntimeException();
        $socket->on('error', $this->expectCallableOnceWith($error));
        $tcp->emit('error', array($error));

        $socket->close();
    }

    public function testEmitsConnectionForNewConnection()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);
        $socket->on('connection', $this->expectCallableOnce());

        $peer = new Promise(function ($resolve, $reject) use ($socket) {
            $socket->on('connection', $resolve);
        });

        $client = stream_socket_client($socket->getAddress());

        Block\await($peer, $loop, self::TIMEOUT);
    }

    public function testDoesNotEmitConnectionForNewConnectionToPausedServer()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);
        $socket->pause();
        $socket->on('connection', $this->expectCallableNever());

        $client = stream_socket_client($socket->getAddress());

        Block\sleep(0.1, $loop);
    }

    public function testDoesEmitConnectionForNewConnectionToResumedServer()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);
        $socket->pause();
        $socket->on('connection', $this->expectCallableOnce());

        $peer = new Promise(function ($resolve, $reject) use ($socket) {
            $socket->on('connection', $resolve);
        });

        $client = stream_socket_client($socket->getAddress());

        $socket->resume();

        Block\await($peer, $loop, self::TIMEOUT);
    }

    public function testDoesNotAllowConnectionToClosedServer()
    {
        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(), $loop);
        $socket->on('connection', $this->expectCallableNever());
        $address = $socket->getAddress();
        $socket->close();

        $client = @stream_socket_client($address);

        $this->assertFalse($client);
    }

    public function testEmitsConnectionWithInheritedContextOptions()
    {
        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.13', '<')) {
            // https://3v4l.org/hB4Tc
            $this->markTestSkipped('Not supported on legacy HHVM < 3.13');
        }

        $loop = Factory::create();

        $socket = new SocketServer('127.0.0.1:0', array(
            'tcp' => array(
                'backlog' => 4
            )
        ), $loop);

        $peer = new Promise(function ($resolve, $reject) use ($socket) {
            $socket->on('connection', function (ConnectionInterface $connection) use ($resolve) {
                $resolve(stream_context_get_options($connection->stream));
            });
        });


        $client = stream_socket_client($socket->getAddress());

        $all = Block\await($peer, $loop, self::TIMEOUT);

        $this->assertEquals(array('socket' => array('backlog' => 4)), $all);
    }

    public function testDoesNotEmitSecureConnectionForNewPlaintextConnectionThatIsIdle()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported on legacy HHVM');
        }

        $loop = Factory::create();

        $socket = new SocketServer('tls://127.0.0.1:0', array(
            'tls' => array(
                'local_cert' => __DIR__ . '/../examples/localhost.pem'
            )
        ), $loop);
        $socket->on('connection', $this->expectCallableNever());

        $client = stream_socket_client(str_replace('tls://', '', $socket->getAddress()));

        Block\sleep(0.1, $loop);
    }

    private function getRandomSocketUri()
    {
        return "unix://" . sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid(rand(), true) . '.sock';
    }
}
