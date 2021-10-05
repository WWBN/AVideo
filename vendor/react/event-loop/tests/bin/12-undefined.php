<?php

use React\EventLoop\Loop;

require __DIR__ . '/../../vendor/autoload.php';

Loop::get()->addTimer(10.0, function () {
    echo 'never';
});

$undefined->foo('bar');
