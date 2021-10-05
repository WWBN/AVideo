<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

// connect to www.google.com:80 (blocking call!)
// for illustration purposes only, should use react/socket instead
$stream = stream_socket_client('tcp://www.google.com:80');
if (!$stream) {
    exit(1);
}
stream_set_blocking($stream, false);

// send HTTP request
fwrite($stream, "GET / HTTP/1.1\r\nHost: www.google.com\r\nConnection: close\r\n\r\n");

// wait for HTTP response
Loop::addReadStream($stream, function ($stream) {
    $chunk = fread($stream, 64 * 1024);

    // reading nothing means we reached EOF
    if ($chunk === '') {
        echo '[END]' . PHP_EOL;
        Loop::removeReadStream($stream);
        fclose($stream);
        return;
    }

    echo $chunk;
});
