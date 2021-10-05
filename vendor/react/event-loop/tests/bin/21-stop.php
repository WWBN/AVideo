<?php

use React\EventLoop\Loop;

require __DIR__ . '/../../vendor/autoload.php';

Loop::addTimer(10.0, function () {
    echo 'never';
});

Loop::stop();
