<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\EventLoop\Factory;
use React\Socket\TcpServer;
use React\Stream\DuplexResourceStream;
use React\Promise\Promise;

class TcpServerTest extends TestCase
{
    const TIMEOUT = 5.0;

    private $loop;
    private $server;
    private $port;

    private function createLoop()
    {
        return Factory::create();
    }

    /**
     * @before
     * @covers React\Socket\TcpServer::__construct
     * @covers React\Socket\TcpServer::getAddress
     */
    public function setUpServer()
    {
        $this->loop = $this->createLoop();
        $this->server = new TcpServer(0, $this->loop);

        $this->port = parse_url($this->server->getAddress(), PHP_URL_PORT);
    }

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $server = new TcpServer(0);

        $ref = new \ReflectionProperty($server, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($server);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
    }

    /**
     * @covers React\Socket\TcpServer::handleConnection
     */
    public function testServerEmitsConnectionEventForNewConnection()
    {
        $client = stream_socket_client('tcp://localhost:'.$this->port);

        $server = $this->server;
        $promise = new Promise(function ($resolve) use ($server) {
            $server->on('connection', $resolve);
        });

        $connection = Block\await($promise, $this->loop, self::TIMEOUT);

        $this->assertInstanceOf('React\Socket\ConnectionInterface', $connection);
    }

    /**
     * @covers React\Socket\TcpServer::handleConnection
     */
    public function testConnectionWithManyClients()
    {
        $client1 = stream_socket_client('tcp://localhost:'.$this->port);
        $client2 = stream_socket_client('tcp://localhost:'.$this->port);
        $client3 = stream_socket_client('tcp://localhost:'.$this->port);

        $this->server->on('connection', $this->expectCallableExactly(3));
        $this->tick();
        $this->tick();
        $this->tick();
    }

    public function testDataEventWillNotBeEmittedWhenClientSendsNoData()
    {
        $client = stream_socket_client('tcp://localhost:'.$this->port);

        $mock = $this->expectCallableNever();

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('data', $mock);
        });
        $this->tick();
        $this->tick();
    }

    public function testDataWillBeEmittedWithDataClientSends()
    {
        $client = stream_socket_client('tcp://localhost:'.$this->port);

        fwrite($client, "foo\n");

        $mock = $this->expectCallableOnceWith("foo\n");

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('data', $mock);
        });
        $this->tick();
        $this->tick();
    }

    public function testDataWillBeEmittedEvenWhenClientShutsDownAfterSending()
    {
        $client = stream_socket_client('tcp://localhost:' . $this->port);
        fwrite($client, "foo\n");
        stream_socket_shutdown($client, STREAM_SHUT_WR);

        $mock = $this->expectCallableOnceWith("foo\n");

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('data', $mock);
        });
        $this->tick();
        $this->tick();
    }

    public function testLoopWillEndWhenServerIsClosed()
    {
        // explicitly unset server because we already call close()
        $this->server->close();
        $this->server = null;

        $this->loop->run();

        // if we reach this, then everything is good
        $this->assertNull(null);
    }

    public function testCloseTwiceIsNoOp()
    {
        $this->server->close();
        $this->server->close();

        // if we reach this, then everything is good
        $this->assertNull(null);
    }

    public function testGetAddressAfterCloseReturnsNull()
    {
        $this->server->close();
        $this->assertNull($this->server->getAddress());
    }

    public function testLoopWillEndWhenServerIsClosedAfterSingleConnection()
    {
        $client = stream_socket_client('tcp://localhost:' . $this->port);

        // explicitly unset server because we only accept a single connection
        // and then already call close()
        $server = $this->server;
        $this->server = null;

        $server->on('connection', function ($conn) use ($server) {
            $conn->close();
            $server->close();
        });

        $this->loop->run();

        // if we reach this, then everything is good
        $this->assertNull(null);
    }

    public function testDataWillBeEmittedInMultipleChunksWhenClientSendsExcessiveAmounts()
    {
        $client = stream_socket_client('tcp://localhost:' . $this->port);
        $stream = new DuplexResourceStream($client, $this->loop);

        $bytes = 1024 * 1024;
        $stream->end(str_repeat('*', $bytes));

        $mock = $this->expectCallableOnce();

        // explicitly unset server because we only accept a single connection
        // and then already call close()
        $server = $this->server;
        $this->server = null;

        $received = 0;
        $server->on('connection', function ($conn) use ($mock, &$received, $server) {
            // count number of bytes received
            $conn->on('data', function ($data) use (&$received) {
                $received += strlen($data);
            });

            $conn->on('end', $mock);

            // do not await any further connections in order to let the loop terminate
            $server->close();
        });

        $this->loop->run();

        $this->assertEquals($bytes, $received);
    }

    public function testConnectionDoesNotEndWhenClientDoesNotClose()
    {
        $client = stream_socket_client('tcp://localhost:'.$this->port);

        $mock = $this->expectCallableNever();

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('end', $mock);
        });
        $this->tick();
        $this->tick();
    }

    /**
     * @covers React\Socket\Connection::end
     */
    public function testConnectionDoesEndWhenClientCloses()
    {
        $client = stream_socket_client('tcp://localhost:'.$this->port);

        fclose($client);

        $mock = $this->expectCallableOnce();

        $this->server->on('connection', function ($conn) use ($mock) {
            $conn->on('end', $mock);
        });
        $this->tick();
        $this->tick();
    }

    public function testCtorAddsResourceToLoop()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addReadStream');

        $server = new TcpServer(0, $loop);
    }

    public function testResumeWithoutPauseIsNoOp()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addReadStream');

        $server = new TcpServer(0, $loop);
        $server->resume();
    }

    public function testPauseRemovesResourceFromLoop()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('removeReadStream');

        $server = new TcpServer(0, $loop);
        $server->pause();
    }

    public function testPauseAfterPauseIsNoOp()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('removeReadStream');

        $server = new TcpServer(0, $loop);
        $server->pause();
        $server->pause();
    }

    public function testCloseRemovesResourceFromLoop()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('removeReadStream');

        $server = new TcpServer(0, $loop);
        $server->close();
    }

    public function testEmitsErrorWhenAcceptListenerFails()
    {
        $listener = null;
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addReadStream')->with($this->anything(), $this->callback(function ($cb) use (&$listener) {
            $listener = $cb;
            return true;
        }));

        $server = new TcpServer(0, $loop);

        $server->on('error', $this->expectCallableOnceWith($this->isInstanceOf('RuntimeException')));

        $this->assertNotNull($listener);
        $socket = stream_socket_server('tcp://127.0.0.1:0');

        $time = microtime(true);
        $listener($socket);
        $time = microtime(true) - $time;

        $this->assertLessThan(1, $time);
    }

    public function testListenOnBusyPortThrows()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Windows supports listening on same port multiple times');
        }

        $this->setExpectedException('RuntimeException');
        $another = new TcpServer($this->port, $this->loop);
    }

    /**
     * @after
     * @covers React\Socket\TcpServer::close
     */
    public function tearDownServer()
    {
        if ($this->server) {
            $this->server->close();
        }
    }

    /**
     * This methods runs the loop for "one tick"
     *
     * This is prone to race conditions and as such somewhat unreliable across
     * different operating systems. Running the loop until the expected events
     * fire is the preferred alternative.
     *
     * @deprecated
     */
    private function tick()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Not supported on Windows');
        }

        Block\sleep(0, $this->loop);
    }
}
