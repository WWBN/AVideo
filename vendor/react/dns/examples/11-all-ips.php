<?php

use React\Dns\Config\Config;
use React\Dns\Resolver\Factory;
use React\Dns\Model\Message;

require __DIR__ . '/../vendor/autoload.php';

$config = Config::loadSystemConfigBlocking();
if (!$config->nameservers) {
    $config->nameservers[] = '8.8.8.8';
}

$factory = new Factory();
$resolver = $factory->create($config);

$name = isset($argv[1]) ? $argv[1] : 'www.google.com';

$resolver->resolveAll($name, Message::TYPE_A)->then(function (array $ips) use ($name) {
    echo 'IPv4 addresses for ' . $name . ': ' . implode(', ', $ips) . PHP_EOL;
}, function (Exception $e) use ($name) {
    echo 'No IPv4 addresses for ' . $name . ': ' . $e->getMessage() . PHP_EOL;
});

$resolver->resolveAll($name, Message::TYPE_AAAA)->then(function (array $ips) use ($name) {
    echo 'IPv6 addresses for ' . $name . ': ' . implode(', ', $ips) . PHP_EOL;
}, function (Exception $e) use ($name) {
    echo 'No IPv6 addresses for ' . $name . ': ' . $e->getMessage() . PHP_EOL;
});
