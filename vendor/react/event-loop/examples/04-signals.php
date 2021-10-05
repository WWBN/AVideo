<?php

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

if (!defined('SIGINT')) {
    fwrite(STDERR, 'Not supported on your platform (ext-pcntl missing or Windows?)' . PHP_EOL);
    exit(1);
}

Loop::addSignal(SIGINT, $func = function ($signal) use (&$func) {
    echo 'Signal: ', (string)$signal, PHP_EOL;
    Loop::removeSignal(SIGINT, $func);
});

echo 'Listening for SIGINT. Use "kill -SIGINT ' . getmypid() . '" or CTRL+C' . PHP_EOL;
