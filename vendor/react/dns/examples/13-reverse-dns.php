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

$ip = isset($argv[1]) ? $argv[1] : '8.8.8.8';

if (@inet_pton($ip) === false) {
    exit('Error: Given argument is not a valid IP' . PHP_EOL);
}

if (strpos($ip, ':') === false) {
    $name = inet_ntop(strrev(inet_pton($ip))) . '.in-addr.arpa';
} else {
    $name = wordwrap(strrev(bin2hex(inet_pton($ip))), 1, '.', true) . '.ip6.arpa';
}

$resolver->resolveAll($name, Message::TYPE_PTR)->then(function (array $names) {
    var_dump($names);
}, function (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
});
