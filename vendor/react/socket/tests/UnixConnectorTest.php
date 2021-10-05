<?php

namespace React\Tests\Socket;

use React\Socket\ConnectionInterface;
use React\Socket\UnixConnector;

class UnixConnectorTest extends TestCase
{
    private $loop;
    private $connector;

    /**
     * @before
     */
    public function setUpConnector()
    {
        $this->loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $this->connector = new UnixConnector($this->loop);
    }

    public function testConstructWithoutLoopAssignsLoopAutomatically()
    {
        $connector = new UnixConnector();

        $ref = new \ReflectionProperty($connector, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($connector);

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $loop);
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
        if (!in_array('unix', stream_get_transports())) {
            $this->markTestSkipped('Unix domain sockets (UDS) not supported on your platform (Windows?)');
        }

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
