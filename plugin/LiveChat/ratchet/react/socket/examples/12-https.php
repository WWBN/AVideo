<?php

use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;

$target = isset($argv[1]) ? $argv[1] : 'www.google.com:443';

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();
$connector = new Connector($loop);

$connector->connect('tls://' . $target)->then(function (ConnectionInterface $connection) use ($target) {
    $connection->on('data', function ($data) {
        echo $data;
    });
    $connection->on('close', function () {
        echo '[CLOSED]' . PHP_EOL;
    });

    $connection->write("GET / HTTP/1.0\r\nHost: $target\r\n\r\n");
}, 'printf');

$loop->run();
