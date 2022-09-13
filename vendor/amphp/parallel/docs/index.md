---
title: Parallel processing for PHP
permalink: /
---
This package provides *true parallel processing* for PHP using multiple processes or native threads, *without blocking and no extensions required*.

## Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require amphp/parallel
```

## Usage

The basic usage of this library is to submit blocking tasks to be executed by a worker pool in order to avoid blocking the main event loop.

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Amp\Parallel\Worker;
use Amp\Promise;

$urls = [
    'https://secure.php.net',
    'https://amphp.org',
    'https://github.com',
];

$promises = [];
foreach ($urls as $url) {
    $promises[$url] = Worker\enqueueCallable('file_get_contents', $url);
}

$responses = Promise\wait(Promise\all($promises));

foreach ($responses as $url => $response) {
    \printf("Read %d bytes from %s\n", \strlen($response), $url);
}
```

[`file_get_contents`](https://secure.php.net/file_get_contents) is just used as an example for a blocking function here.
If you just want to fetch multiple HTTP resources concurrently, it's better to use [`amphp/http-client`](https://amphp.org/http-client/), our non-blocking HTTP client.

The functions you call must be predefined or autoloadable by Composer so they also exist in the worker processes.
Instead of simple callables, you can also [enqueue `Task` instances](./workers#task) with `Amp\Parallel\Worker\enqueue()`.
