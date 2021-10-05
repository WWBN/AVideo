<?php

// Just start the server and connect to it. It will count the number of bytes
// sent for each connection and will print the average throughput once the
// connection closes.
//
// $ php examples/91-benchmark-server.php 127.0.0.1:8000
// $ telnet localhost 8000
// $ echo hello world | nc -N localhost 8000
// $ dd if=/dev/zero bs=1M count=1000 | nc -N localhost 8000
//
// You can also run a secure TLS benchmarking server like this:
//
// $ php examples/91-benchmark-server.php tls://127.0.0.1:8000 examples/localhost.pem
// $ openssl s_client -connect localhost:8000
// $ echo hello world | openssl s_client -connect localhost:8000
// $ dd if=/dev/zero bs=1M count=1000 | openssl s_client -connect localhost:8000
//
// You can also run a Unix domain socket (UDS) server benchmark like this:
//
// $ php examples/91-benchmark-server.php unix:///tmp/server.sock
// $ nc -N -U /tmp/server.sock
// $ dd if=/dev/zero bs=1M count=1000 | nc -N -U /tmp/server.sock

require __DIR__ . '/../vendor/autoload.php';

$socket = new React\Socket\SocketServer(isset($argv[1]) ? $argv[1] : '127.0.0.1:0', array(
    'tls' => array(
        'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
    )
));

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) {
    echo '[connected]' . PHP_EOL;

    // count the number of bytes received from this connection
    $bytes = 0;
    $connection->on('data', function ($chunk) use (&$bytes) {
        $bytes += strlen($chunk);
    });

    // report average throughput once client disconnects
    $t = microtime(true);
    $connection->on('close', function () use ($connection, $t, &$bytes) {
        $t = microtime(true) - $t;
        echo '[disconnected after receiving ' . $bytes . ' bytes in ' . round($t, 3) . 's => ' . round($bytes / $t / 1024 / 1024, 1) . ' MiB/s]' . PHP_EOL;
    });
});

$socket->on('error', 'printf');

echo 'Listening on ' . $socket->getAddress() . PHP_EOL;
