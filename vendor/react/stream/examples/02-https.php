<?php

// Simple secure HTTPS client example (for illustration purposes only).
// This shows how a secure TLS connection is established to then send an
// application level protocol message (HTTP).
// Real applications should use react/http-client instead!
//
// This simple example only accepts an optional host parameter to send the
// request to.
//
// $ php examples/02-https.php
// $ php examples/02-https.php reactphp.org

use React\Stream\DuplexResourceStream;

require __DIR__ . '/../vendor/autoload.php';

$host = isset($argv[1]) ? $argv[1] : 'www.google.com';

// connect to tls://www.google.com:443 (blocking call!)
// for illustration purposes only, should use react/http-client or react/socket instead!
$resource = stream_socket_client('tls://' . $host . ':443');
if (!$resource) {
    exit(1);
}

$stream = new DuplexResourceStream($resource);

$stream->on('data', function ($chunk) {
    echo $chunk;
});
$stream->on('close', function () {
    echo '[CLOSED]' . PHP_EOL;
});

$stream->write("GET / HTTP/1.0\r\nHost: $host\r\n\r\n");
