<?php

use React\EventLoop\Loop;

require __DIR__ . '/../../vendor/autoload.php';

$loop = Loop::get();

$loop->futureTick(function () use ($loop) {
    echo 'b';

    $loop->stop();

    $loop->futureTick(function () {
        echo 'never';
    });
});

echo 'a';

$loop->run();

echo 'c';
