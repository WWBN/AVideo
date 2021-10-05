<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

$ticks = isset($argv[1]) ? (int)$argv[1] : 1000 * 100;
$tick = function () use (&$tick, &$ticks) {
    if ($ticks > 0) {
        --$ticks;
        //$loop->addTimer(0, $tick);
        Loop::futureTick($tick);
    } else {
        echo 'done';
    }
};

$tick();
