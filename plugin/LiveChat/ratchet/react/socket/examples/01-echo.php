<?php

// Just start this server and connect to it. Everything you send to it will be
// sent back to you.
//
// $ php examples/01-echo.php 8000
// $ telnet localhost 8000
//
// You can also run a secure TLS echo server like this:
//
// $ php examples/01-echo.php tls://127.0.0.1:8000 examples/localhost.pem
// $ openssl s_client -connect localhost:8000

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$server = new Server(isset($argv[1]) ? $argv[1] : 0, $loop, array(
    'tls' => array(
        'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
    )
));

$server->on('connection', function (ConnectionInterface $conn) {
    echo '[' . $conn->getRemoteAddress() . ' connected]' . PHP_EOL;
    $conn->pipe($conn);
});

$server->on('error', 'printf');

echo 'Listening on ' . $server->getAddress() . PHP_EOL;

$loop->run();
