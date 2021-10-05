<?php

// Simple HTTP server example (for illustration purposes only).
// This shows how a plaintext TCP/IP connection is accepted to then receive an
// application level protocol message (HTTP request) and reply with an
// application level protocol message (HTTP response) in return.
//
// This example exists for illustraion purposes only. It does not actually
// parse incoming HTTP requests, so you can actually send *anything* and will
// still respond with a valid HTTP response.
// Real applications should use react/http instead!
//
// Just start this server and send a request to it:
//
// $ php examples/03-http-server.php 127.0.0.1:8000
// $ curl -v http://localhost:8000/
// $ ab -n1000 -c10 http://localhost:8000/
// $ docker run -it --rm --net=host jordi/ab ab -n1000 -c10 http://localhost:8000/
//
// You can also run a secure HTTPS echo server like this:
//
// $ php examples/03-http-server.php tls://127.0.0.1:8000 examples/localhost.pem
// $ curl -v --insecure https://localhost:8000/
// $ ab -n1000 -c10 https://localhost:8000/
// $ docker run -it --rm --net=host jordi/ab ab -n1000 -c10 https://localhost:8000/
//
// You can also run a Unix domain socket (UDS) server like this:
//
// $ php examples/03-http-server.php unix:///tmp/server.sock
// $ nc -U /tmp/server.sock

require __DIR__ . '/../vendor/autoload.php';

$socket = new React\Socket\SocketServer(isset($argv[1]) ? $argv[1] : '127.0.0.1:0', array(
    'tls' => array(
        'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
    )
));

$socket->on('connection', function (React\Socket\ConnectionInterface $connection) {
    $connection->once('data', function () use ($connection) {
        $body = "<html><h1>Hello world!</h1></html>\r\n";
        $connection->end("HTTP/1.1 200 OK\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n" . $body);
    });
});

$socket->on('error', 'printf');

echo 'Listening on ' . strtr($socket->getAddress(), array('tcp:' => 'http:', 'tls:' => 'https:')) . PHP_EOL;
