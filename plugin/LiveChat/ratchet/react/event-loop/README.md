# EventLoop Component

[![Build Status](https://travis-ci.org/reactphp/event-loop.svg?branch=master)](https://travis-ci.org/reactphp/event-loop)
[![Code Climate](https://codeclimate.com/github/reactphp/event-loop/badges/gpa.svg)](https://codeclimate.com/github/reactphp/event-loop)

Event loop abstraction layer that libraries can use for evented I/O.

In order for async based libraries to be interoperable, they need to use the
same event loop. This component provides a common `LoopInterface` that any
library can target. This allows them to be used in the same loop, with one
single `run()` call that is controlled by the user.

**Table of Contents**

* [Quickstart example](#quickstart-example)
* [Usage](#usage)
* [Loop implementations](#loop-implementations)
* [Install](#install)
* [Tests](#tests)
* [License](#license)

## Quickstart example

Here is an async HTTP server built with just the event loop.

```php
$loop = React\EventLoop\Factory::create();

$server = stream_socket_server('tcp://127.0.0.1:8080');
stream_set_blocking($server, 0);

$loop->addReadStream($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server);
    $data = "HTTP/1.1 200 OK\r\nContent-Length: 3\r\n\r\nHi\n";
    $loop->addWriteStream($conn, function ($conn) use (&$data, $loop) {
        $written = fwrite($conn, $data);
        if ($written === strlen($data)) {
            fclose($conn);
            $loop->removeStream($conn);
        } else {
            $data = substr($data, $written);
        }
    });
});

$loop->addPeriodicTimer(5, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});

$loop->run();
```

## Usage

Typical applications use a single event loop which is created at the beginning
and run at the end of the program.

```php
// [1]
$loop = React\EventLoop\Factory::create();

// [2]
$loop->addPeriodicTimer(1, function () {
    echo "Tick\n";
});

$stream = new React\Stream\ReadableResourceStream(
    fopen('file.txt', 'r'),
    $loop
);

// [3]
$loop->run();
```

1. The loop instance is created at the beginning of the program. A convenience
   factory `React\EventLoop\Factory::create()` is provided by this library which
   picks the best available [loop implementation](#loop-implementations).
2. The loop instance is used directly or passed to library and application code.
   In this example, a periodic timer is registered with the event loop which
   simply outputs `Tick` every second and a
   [readable stream](https://github.com/reactphp/stream#readableresourcestream)
   is created by using ReactPHP's
   [stream component](https://github.com/reactphp/stream) for demonstration
   purposes.
3. The loop is run with a single `$loop->run()` call at the end of the program.

## Loop implementations

In addition to the interface there are the following implementations provided:

* `StreamSelectLoop`: This is the only implementation which works out of the
  box with PHP. It does a simple `select` system call. It's not the most
  performant of loops, but still does the job quite well.

* `LibEventLoop`: This uses the `libevent` pecl extension. `libevent` itself
  supports a number of system-specific backends (epoll, kqueue).

* `LibEvLoop`: This uses the `libev` pecl extension
  ([github](https://github.com/m4rw3r/php-libev)). It supports the same
  backends as libevent.

* `ExtEventLoop`: This uses the `event` pecl extension. It supports the same
  backends as libevent.

All of the loops support these features:

* File descriptor polling
* One-off timers
* Periodic timers
* Deferred execution of callbacks

## Install

The recommended way to install this library is [through Composer](http://getcomposer.org).
[New to Composer?](http://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require react/event-loop
```

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](http://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).
