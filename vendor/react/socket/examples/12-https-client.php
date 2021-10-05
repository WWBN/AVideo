<?php

// Simple secure HTTPS client example (for illustration purposes only).
// This shows how a secure TLS connection is established to then send an
// application level protocol message (HTTP).
// Real applications should use react/http-client instead
//
// This simple example only accepts an optional host parameter to send the
// request to. See also example #22 for proper URI parsing.
//
// $ php examples/12-https-client.php
// $ php examples/12-https-client.php reactphp.org

use React\Socket\Connector;
use React\Socket\ConnectionInterface;

$host = isset($argv[1]) ? $argv[1] : 'www.google.com';

require __DIR__ . '/../vendor/autoload.php';

$connector = new Connector();

$connector->connect('tls://' . $host . ':443')->then(function (ConnectionInterface $connection) use ($host) {
    $connection->on('data', function ($data) {
        echo $data;
    });
    $connection->on('close', function () {
        echo '[CLOSED]' . PHP_EOL;
    });

    $connection->write("GET / HTTP/1.0\r\nHost: $host\r\n\r\n");
}, 'printf');
