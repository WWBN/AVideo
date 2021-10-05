<?php

/**
 * Run the script indefinitely seconds with the loop from the factory and report every 2 seconds:
 * php 95-benchmark-memory.php
 * Run the script for 30 seconds with the stream_select loop and report every 10 seconds:
 * php 95-benchmark-memory.php -t 30 -l StreamSelect -r 10
 */

use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

require __DIR__ . '/../vendor/autoload.php';

$args = getopt('t:l:r:');
$t  = isset($args['t']) ? (int)$args['t'] : 0;
$loop = isset($args['l']) && class_exists('React\EventLoop\\' . $args['l'] . 'Loop') ? 'React\EventLoop\\' . $args['l'] . 'Loop' : Loop::get();

if (!($loop instanceof LoopInterface)) {
    Loop::set(new $loop());
}

$r = isset($args['r']) ? (int)$args['r'] : 2;

$runs = 0;

if (5 < $t) {
    Loop::addTimer($t, function () {
        Loop::stop();
    });

}

Loop::addPeriodicTimer(0.001, function () use (&$runs) {
    $runs++;

    Loop::addPeriodicTimer(1, function (TimerInterface $timer) {
        Loop::cancelTimer($timer);
    });
});

Loop::addPeriodicTimer($r, function () use (&$runs) {
    $kmem = round(memory_get_usage() / 1024);
    $kmemReal = round(memory_get_usage(true) / 1024);
    echo "Runs:\t\t\t$runs\n";
    echo "Memory (internal):\t$kmem KiB\n";
    echo "Memory (real):\t\t$kmemReal KiB\n";
    echo str_repeat('-', 50), "\n";
});

echo "PHP Version:\t\t", phpversion(), "\n";
echo "Loop\t\t\t", get_class(Loop::get()), "\n";
echo "Time\t\t\t", date('r'), "\n";

echo str_repeat('-', 50), "\n";

$beginTime = time();
Loop::run();
$endTime = time();
$timeTaken = $endTime - $beginTime;

echo "PHP Version:\t\t", phpversion(), "\n";
echo "Loop\t\t\t", get_class(Loop::get()), "\n";
echo "Time\t\t\t", date('r'), "\n";
echo "Time taken\t\t", $timeTaken, " seconds\n";
echo "Runs per second\t\t", round($runs / $timeTaken), "\n";
