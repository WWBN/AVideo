<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

Loop::futureTick(function () {
    echo 'b';
});
Loop::futureTick(function () {
    echo 'c';
});
echo 'a';
