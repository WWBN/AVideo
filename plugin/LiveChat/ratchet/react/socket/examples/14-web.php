<?php

use React\EventLoop\Factory;
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

$loop = Factory::create();
$connector = new Connector($loop);

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

$stdout = new WritableResourceStream(STDOUT, $loop);

$connector->connect($target)->then(function (ConnectionInterface $connection) use ($resource, $host, $stdout) {
    $connection->pipe($stdout);

    $connection->write("GET $resource HTTP/1.0\r\nHost: $host\r\n\r\n");
}, 'printf');

$loop->run();
