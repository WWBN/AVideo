<?php

use React\EventLoop\Loop;

require __DIR__ . '/../../vendor/autoload.php';

$loop = Loop::get();

$loop->futureTick(function () {
    echo 'b';
});

$loop->futureTick(function () {
    echo 'c';
});

echo 'a';

$loop->run();
