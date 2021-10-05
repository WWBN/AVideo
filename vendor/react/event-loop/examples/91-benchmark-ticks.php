<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

$n = isset($argv[1]) ? (int)$argv[1] : 1000 * 100;

for ($i = 0; $i < $n; ++$i) {
    Loop::futureTick(function () { });
}
