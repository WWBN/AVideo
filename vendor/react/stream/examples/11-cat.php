<?php

// Simple example piping everything from STDIN to STDOUT.
// This allows you to output everything you type on your keyboard or to redirect
// the pipes to show contents of files and other streams.
//
// $ php examples/11-cat.php
// $ php examples/11-cat.php < README.md
// $ echo hello | php examples/11-cat.php

use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

require __DIR__ . '/../vendor/autoload.php';

if (DIRECTORY_SEPARATOR === '\\') {
    fwrite(STDERR, 'Non-blocking console I/O not supported on Microsoft Windows' . PHP_EOL);
    exit(1);
}

$stdout = new WritableResourceStream(STDOUT);
$stdin = new ReadableResourceStream(STDIN);
$stdin->pipe($stdout);
