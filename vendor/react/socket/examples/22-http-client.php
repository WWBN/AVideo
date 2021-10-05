<?php

// Simple plaintext HTTP and secure HTTPS client example (for illustration purposes only).
// This shows how an URI parameter can parsed to decide whether to establish
// a plaintext TCP/IP or secure TLS connection and then send an
// application level protocol message (HTTP).
// Real applications should use react/http-client instead!
//
// Unlike examples #11 and #12, this example will actually take an optional URI
// parameter and parse it to connect to the correct default port and use the
// correct transport protocol.
//
// $ php examples/22-http-client.php
// $ php examples/22-http-client.php https://reactphp.org/

use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Stream\WritableResourceStream;

require __DIR__ . '/../vendor/autoload.php';

$uri = isset($argv[1]) ? $argv[1] : 'www.google.com';

if (strpos($uri, '://') === false) {
    $uri = 'http://' . $uri;
}
$parts = parse_url($uri);

if (!$parts || !isset($parts['scheme'], $parts['host'])) {
    fwrite(STDERR, 'Usage error: required argument <host:port>' . PHP_EOL);
    exit(1);
}

$connector = new Connector();

if (!isset($parts['port'])) {
    $parts['port'] = $parts['scheme'] === 'https' ? 443 : 80;
}

$host = $parts['host'];
if (($parts['scheme'] === 'http' && $parts['port'] !== 80) || ($parts['scheme'] === 'https' && $parts['port'] !== 443)) {
    $host .= ':' . $parts['port'];
}
$target = ($parts['scheme'] === 'https' ? 'tls' : 'tcp') . '://' . $parts['host'] . ':' . $parts['port'];
$resource = isset($parts['path']) ? $parts['path'] : '/';
if (isset($parts['query'])) {
    $resource .= '?' . $parts['query'];
}

$stdout = new WritableResourceStream(STDOUT);

$connector->connect($target)->then(function (ConnectionInterface $connection) use ($resource, $host, $stdout) {
    $connection->pipe($stdout);

    $connection->write("GET $resource HTTP/1.0\r\nHost: $host\r\n\r\n");
}, 'printf');
