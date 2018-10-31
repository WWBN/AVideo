<?php

use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    fwrite(STDERR, 'Usage error: required argument <host:port>' . PHP_EOL);
    exit(1);
}

$loop = Factory::create();
$connector = new Connector($loop);

$stdin = new ReadableResourceStream(STDIN, $loop);
$stdin->pause();
$stdout = new WritableResourceStream(STDOUT, $loop);
$stderr = new WritableResourceStream(STDERR, $loop);

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

$loop->run();
