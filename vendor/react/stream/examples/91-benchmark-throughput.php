<?php

// Benchmark to measure throughput performance piping an input stream to an output stream.
// This allows you to get an idea of how fast stream processing with PHP can be
// and also to play around with differnt types of input and output streams.
//
// This example accepts a number of parameters to control the timeout (-t 1),
// the input file (-i /dev/zero) and the output file (-o /dev/null).
//
// $ php examples/91-benchmark-throughput.php
// $ php examples/91-benchmark-throughput.php -t 10 -o zero.bin
// $ php examples/91-benchmark-throughput.php -t 60 -i zero.bin

use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Microsoft Windows' . PHP_EOL);
    exit(1);
}

$args = getopt('i:o:t:');
$if = isset($args['i']) ? $args['i'] : '/dev/zero';
$of = isset($args['o']) ? $args['o'] : '/dev/null';
$t  = isset($args['t']) ? $args['t'] : 1;

// passing file descriptors requires mapping paths (https://bugs.php.net/bug.php?id=53465)
$if = str_replace('/dev/fd/', 'php://fd/', $if);
$of = str_replace('/dev/fd/', 'php://fd/', $of);

// setup information stream
$info = new React\Stream\WritableResourceStream(STDERR);
if (extension_loaded('xdebug')) {
    $info->write('NOTICE: The "xdebug" extension is loaded, this has a major impact on performance.' . PHP_EOL);
}
$info->write('piping from ' . $if . ' to ' . $of . ' (for max ' . $t . ' second(s)) ...'. PHP_EOL);

// setup input and output streams and pipe inbetween
$fh = fopen($if, 'r');
$in = new React\Stream\ReadableResourceStream($fh);
$out = new React\Stream\WritableResourceStream(fopen($of, 'w'));
$in->pipe($out);

// stop input stream in $t seconds
$start = microtime(true);
$timeout = Loop::addTimer($t, function () use ($in) {
    $in->close();
});

// print stream position once stream closes
$in->on('close', function () use ($fh, $start, $timeout, $info) {
    $t = microtime(true) - $start;
    Loop::cancelTimer($timeout);

    $bytes = ftell($fh);

    $info->write('read ' . $bytes . ' byte(s) in ' . round($t, 3) . ' second(s) => ' . round($bytes / 1024 / 1024 / $t, 1) . ' MiB/s' . PHP_EOL);
    $info->write('peak memory usage of ' . round(memory_get_peak_usage(true) / 1024 / 1024, 1) . ' MiB' . PHP_EOL);
});
