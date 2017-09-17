<?php

use React\EventLoop\Factory;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$stdout = new WritableResourceStream(STDOUT, $loop);
$stdin = new ReadableResourceStream(STDIN, $loop);
$stdin->pipe($stdout);

$loop->run();
