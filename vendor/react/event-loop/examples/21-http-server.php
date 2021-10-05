<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

// start TCP/IP server on localhost:8080
// for illustration purposes only, should use react/socket instead
$server = stream_socket_server('tcp://127.0.0.1:8080');
if (!$server) {
    exit(1);
}
stream_set_blocking($server, false);

// wait for incoming connections on server socket
Loop::addReadStream($server, function ($server) {
    $conn = stream_socket_accept($server);
    $data = "HTTP/1.1 200 OK\r\nContent-Length: 3\r\n\r\nHi\n";
    Loop::addWriteStream($conn, function ($conn) use (&$data) {
        $written = fwrite($conn, $data);
        if ($written === strlen($data)) {
            fclose($conn);
            Loop::removeWriteStream($conn);
        } else {
            $data = substr($data, $written);
        }
    });
});

Loop::addPeriodicTimer(5, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});
