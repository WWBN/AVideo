---
title: Processes and Threads
permalink: /processes
---
The `Process` and `Parallel` classes simplify writing and running PHP in parallel. A script written to be run in parallel must return a callable that will be run in a child process (or a thread if [`ext-parallel`](https://github.com/krakjoe/parallel) is installed). The callable receives a single argument – an instance of `Channel` that can be used to send data between the parent and child processes. Any serializable data can be sent across this channel. The `Context` object, which extends the `Channel` interface, is the other end of the communication channel.

In the example below, a child process or thread is used to call a blocking function (`file_get_contents()` is only an example of a blocking function, use [`http-client`](https://amphp.org/http-client) for non-blocking HTTP requests). The result of that function is then sent back to the parent using the `Channel` object. The return value of the child process callable is available using the `Context::join()` method.

## Child process or thread

```php
# child.php

use Amp\Parallel\Sync\Channel;

return function (Channel $channel): \Generator {
    $url = yield $channel->receive();

    $data = file_get_contents($url); // Example blocking function

    yield $channel->send($data);

    return 'Any serializable data';
};
```

## Parent Process

```php
# parent.php

use Amp\Loop;
use Amp\Parallel\Context;

Loop::run(function () {
	// Creates a context using Process, or if ext-parallel is installed, Parallel.
    $context = Context\create(__DIR__ . '/child.php');

    $pid = yield $context->start();

    $url = 'https://google.com';

    yield $context->send($url);

    $requestData = yield $context->receive();

    printf("Received %d bytes from %s\n", \strlen($requestData), $url);

    $returnValue = yield $context->join();

    printf("Child processes exited with '%s'\n", $returnValue);
});
```

Child processes are also great for CPU-intensive operations such as image manipulation or for running daemons that perform periodic tasks based on input from the parent.
