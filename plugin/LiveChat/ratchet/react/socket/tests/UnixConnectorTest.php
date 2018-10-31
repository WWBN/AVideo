<?php

namespace React\Tests\Socket;

use React\Socket\UnixConnector;
use Clue\React\Block;
use React\Socket\ConnectionInterface;

class UnixConnectorTest extends TestCase
{
    private $loop;
    private $connector;

    public function setUp()
    {
        $this->loop = $this->getMock('React\EventLoop\LoopInterface');
        $this->connector = new UnixConnector($this->loop);
    }

    public function testInvalid()
    {
        $promise = $this->connector->connect('google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testInvalidScheme()
    {
        $promise = $this->connector->connect('tcp://google.com:80');
        $promise->then(null, $this->expectCallableOnce());
    }

    public function testValid()
    {
        // random unix domain socket path
        $path = sys_get_temp_dir() . '/test' . uniqid() . '.sock';

        // temporarily create unix domain socket server to connect to
        $server = stream_socket_server('unix://' . $path, $errno, $errstr);

        // skip test if we can not create a test server (Windows etc.)
        if (!$server) {
            $this->markTestSkipped('Unable to create socket "' . $path . '": ' . $errstr . '(' . $errno .')');
            return;
        }

        // tests succeeds if we get notified of successful connection
        $promise = $this->connector->connect($path);
        $promise->then($this->expectCallableOnce());

        // remember remote and local address of this connection and close again
        $remote = $local = false;
        $promise->then(function(ConnectionInterface $conn) use (&$remote, &$local) {
            $remote = $conn->getRemoteAddress();
            $local = $conn->getLocalAddress();
            $conn->close();
        });

        // clean up server
        fclose($server);
        unlink($path);

        $this->assertNull($local);
        $this->assertEquals('unix://' . $path, $remote);
    }
}
