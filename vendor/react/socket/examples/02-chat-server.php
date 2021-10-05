<?php

// Just start this server and connect with any number of clients to it.
// Everything a client sends will be broadcasted to all connected clients.
//
// $ php examples/02-chat-server.php 127.0.0.1:8000
// $ telnet localhost 8000
//
// You can also run a secure TLS chat server like this:
//
// $ php examples/02-chat-server.php tls://127.0.0.1:8000 examples/localhost.pem
// $ openssl s_client -connect localhost:8000
//
// You can also run a Unix domain socket (UDS) server like this:
//
// $ php examples/02-chat-server.php unix:///tmp/server.sock
// $ nc -U /tmp/server.sock

require __DIR__ . '/../vendor/autoload.php';

$socket = new React\Socket\SocketServer(isset($argv[1]) ? $argv[1] : '127.0.0.1:0', array(
    'tls' => array(
        'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
    )
));

$socket = new React\Socket\LimitingServer($socket, null);

$socket->on('connection', function (React\Socket\ConnectionInterface $client) use ($socket) {
    // whenever a new message comes in
    $client->on('data', function ($data) use ($client, $socket) {
        // remove any non-word characters (just for the demo)
        $data = trim(preg_replace('/[^\w\d \.\,\-\!\?]/u', '', $data));

        // ignore empty messages
        if ($data === '') {
            return;
        }

        // prefix with client IP and broadcast to all connected clients
        $data = trim(parse_url($client->getRemoteAddress(), PHP_URL_HOST), '[]') . ': ' . $data . PHP_EOL;
        foreach ($socket->getConnections() as $connection) {
            $connection->write($data);
        }
    });
});

$socket->on('error', 'printf');

echo 'Listening on ' . $socket->getAddress() . PHP_EOL;
