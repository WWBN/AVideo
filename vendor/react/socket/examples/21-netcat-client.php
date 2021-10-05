<?php

// Simple plaintext TCP/IP and secure TLS client example that pipes console I/O.
// This shows how a plaintext TCP/IP or secure TLS connection is established and
// then everything you type on STDIN will be sent and everything the server
// sends will be piped to your STDOUT.
//
// $ php examples/21-netcat-client.php www.google.com:80
// $ php examples/21-netcat-client.php tls://www.google.com:443

use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

require __DIR__ . '/../vendor/autoload.php';

if (!defined('STDIN')) {
    echo 'STDIO streams require CLI SAPI' . PHP_EOL;
    exit(1);
}

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Microsoft Windows' . PHP_EOL);
    exit(1);
}

if (!isset($argv[1])) {
    fwrite(STDERR, 'Usage error: required argument <host:port>' . PHP_EOL);
    exit(1);
}

$connector = new Connector();

$stdin = new ReadableResourceStream(STDIN);
$stdin->pause();
$stdout = new WritableResourceStream(STDOUT);
$stderr = new WritableResourceStream(STDERR);

$stderr->write('Connecting' . PHP_EOL);

$connector->connect($argv[1])->then(function (ConnectionInterface $connection) use ($stdin, $stdout, $stderr) {
    // pipe everything from STDIN into connection
    $stdin->resume();
    $stdin->pipe($connection);

    // pipe everything from connection to STDOUT
    $connection->pipe($stdout);

    // report errors to STDERR
    $connection->on('error', function ($error) use ($stderr) {
        $stderr->write('Stream ERROR: ' . $error . PHP_EOL);
    });

    // report closing and stop reading from input
    $connection->on('close', function () use ($stderr, $stdin) {
        $stderr->write('[CLOSED]' . PHP_EOL);
        $stdin->close();
    });

    $stderr->write('Connected' . PHP_EOL);
}, function ($error) use ($stderr) {
    $stderr->write('Connection ERROR: ' . $error . PHP_EOL);
});
