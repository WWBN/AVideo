<?php

// $ php examples/12-all-types.php
// $ php examples/12-all-types.php myserverplace.de SSHFP

use React\Dns\Config\Config;
use React\Dns\Resolver\Factory;

require __DIR__ . '/../vendor/autoload.php';

$config = Config::loadSystemConfigBlocking();
if (!$config->nameservers) {
    $config->nameservers[] = '8.8.8.8';
}

$factory = new Factory();
$resolver = $factory->create($config);

$name = isset($argv[1]) ? $argv[1] : 'google.com';
$type = constant('React\Dns\Model\Message::TYPE_' . (isset($argv[2]) ? $argv[2] : 'TXT'));

$resolver->resolveAll($name, $type)->then(function (array $values) {
    var_dump($values);
}, function (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
});
