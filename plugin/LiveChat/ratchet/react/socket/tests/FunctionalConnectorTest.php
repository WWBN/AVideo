<?php

namespace React\Tests\Socket;

use Clue\React\Block;
use React\EventLoop\StreamSelectLoop;
use React\Socket\TcpServer;
use React\Socket\Connector;

class FunctionalConnectorTest extends TestCase
{
    const TIMEOUT = 1.0;

    /** @test */
    public function connectionToTcpServerShouldSucceedWithLocalhost()
    {
        $loop = new StreamSelectLoop();

        $server = new TcpServer(9998, $loop);
        $server->on('connection', $this->expectCallableOnce());
        $server->on('connection', array($server, 'close'));

        $connector = new Connector($loop);

        $connection = Block\await($connector->connect('localhost:9998'), $loop, self::TIMEOUT);

        $this->assertInstanceOf('React\Socket\ConnectionInterface', $connection);

        $connection->close();
        $server->close();
    }
}
