<?php

use React\Dns\Resolver\Factory;

require __DIR__ . '/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$factory = new Factory();
$resolver = $factory->create('8.8.8.8', $loop);

$names = array_slice($argv, 1);
if (!$names) {
    $names = array('google.com', 'www.google.com', 'gmail.com');
}

foreach ($names as $name) {
    $resolver->resolve($name)->then(function ($ip) use ($name) {
        echo 'IP for ' . $name . ': ' . $ip . PHP_EOL;
    }, 'printf');
}

$loop->run();
