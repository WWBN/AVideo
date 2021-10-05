<?php

use React\EventLoop\Loop;

require __DIR__ . '/../../vendor/autoload.php';

Loop::addTimer(10.0, function () {
    echo 'never';
});

set_exception_handler(function (Exception $e) {
    echo 'Uncaught error occured' . PHP_EOL;
    Loop::stop();
});

throw new RuntimeException();
