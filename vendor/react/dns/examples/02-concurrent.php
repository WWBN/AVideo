<?php

use React\Dns\Config\Config;
use React\Dns\Resolver\Factory;

require __DIR__ . '/../vendor/autoload.php';

$config = Config::loadSystemConfigBlocking();
if (!$config->nameservers) {
    $config->nameservers[] = '8.8.8.8';
}

$factory = new Factory();
$resolver = $factory->create($config);

$names = array_slice($argv, 1);
if (!$names) {
    $names = array('google.com', 'www.google.com', 'gmail.com');
}

foreach ($names as $name) {
    $resolver->resolve($name)->then(function ($ip) use ($name) {
        echo 'IP for ' . $name . ': ' . $ip . PHP_EOL;
    }, 'printf');
}
