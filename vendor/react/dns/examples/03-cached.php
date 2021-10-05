<?php

use React\Dns\Config\Config;
use React\Dns\Resolver\Factory;
use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

$config = Config::loadSystemConfigBlocking();
if (!$config->nameservers) {
    $config->nameservers[] = '8.8.8.8';
}

$factory = new Factory();
$resolver = $factory->createCached($config);

$name = isset($argv[1]) ? $argv[1] : 'www.google.com';

$resolver->resolve($name)->then(function ($ip) use ($name) {
    echo 'IP for ' . $name . ': ' . $ip . PHP_EOL;
}, 'printf');

Loop::addTimer(1.0, function() use ($name, $resolver) {
    $resolver->resolve($name)->then(function ($ip) use ($name) {
        echo 'IP for ' . $name . ': ' . $ip . PHP_EOL;
    }, 'printf');
});

Loop::addTimer(2.0, function() use ($name, $resolver) {
    $resolver->resolve($name)->then(function ($ip) use ($name) {
        echo 'IP for ' . $name . ': ' . $ip . PHP_EOL;
    }, 'printf');
});

Loop::addTimer(3.0, function() use ($name, $resolver) {
    $resolver->resolve($name)->then(function ($ip) use ($name) {
        echo 'IP for ' . $name . ': ' . $ip . PHP_EOL;
    }, 'printf');
});
