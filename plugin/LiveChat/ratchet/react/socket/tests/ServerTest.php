<?php

namespace React\Tests\Socket;

use React\EventLoop\Factory;
use React\Socket\Server;
use Clue\React\Block;
use React\Socket\ConnectionInterface;

class ServerTest extends TestCase
{
    public function testCreateServer()
    {
        $loop = Factory::create();

        $server = new Server(0, $loop);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsForInvalidUri()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $server = new Server('invalid URI', $loop);
    }

    public function testEmitsConnectionForNewConnection()
    {
        $loop = Factory::create();

        $server = new Server(0, $loop);
        $server->on('connection', $this->expectCallableOnce());

        $client = stream_socket_client($server->getAddress());

        Block\sleep(0.1, $loop);
    }

    public function testDoesNotEmitConnectionForNewConnectionToPausedServer()
    {
        $loop = Factory::create();

        $server = new Server(0, $loop);
        $server->pause();


        $client = stream_socket_client($server->getAddress());

        Block\sleep(0.1, $loop);
    }

    public function testDoesEmitConnectionForNewConnectionToResumedServer()
    {
        $loop = Factory::create();

        $server = new Server(0, $loop);
        $server->pause();
        $server->on('connection', $this->expectCallableOnce());

        $client = stream_socket_client($server->getAddress());

        Block\sleep(0.1, $loop);

        $server->resume();
        Block\sleep(0.1, $loop);
    }

    public function testDoesNotAllowConnectionToClosedServer()
    {
        $loop = Factory::create();

        $server = new Server(0, $loop);
        $server->on('connection', $this->expectCallableNever());
        $address = $server->getAddress();
        $server->close();

        $client = @stream_socket_client($address);

        Block\sleep(0.1, $loop);

        $this->assertFalse($client);
    }

    public function testEmitsConnectionWithInheritedContextOptions()
    {
        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.13', '<')) {
            // https://3v4l.org/hB4Tc
            $this->markTestSkipped('Not supported on legacy HHVM < 3.13');
        }

        $loop = Factory::create();

        $server = new Server(0, $loop, array(
            'backlog' => 4
        ));

        $all = null;
        $server->on('connection', function (ConnectionInterface $conn) use (&$all) {
            $all = stream_context_get_options($conn->stream);
        });

        $client = stream_socket_client($server->getAddress());

        Block\sleep(0.1, $loop);

        $this->assertEquals(array('socket' => array('backlog' => 4)), $all);
    }

    public function testDoesNotEmitSecureConnectionForNewPlainConnection()
    {
        if (!function_exists('stream_socket_enable_crypto')) {
            $this->markTestSkipped('Not supported on your platform (outdated HHVM?)');
        }

        $loop = Factory::create();

        $server = new Server('tls://127.0.0.1:0', $loop, array(
            'tls' => array(
                'local_cert' => __DIR__ . '/../examples/localhost.pem'
            )
        ));
        $server->on('connection', $this->expectCallableNever());

        $client = stream_socket_client(str_replace('tls://', '', $server->getAddress()));

        Block\sleep(0.1, $loop);
    }
}
