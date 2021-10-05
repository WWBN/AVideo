<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

Loop::addTimer(0.8, function () {
    echo 'world!' . PHP_EOL;
});

Loop::addTimer(0.3, function () {
    echo 'hello ';
});
