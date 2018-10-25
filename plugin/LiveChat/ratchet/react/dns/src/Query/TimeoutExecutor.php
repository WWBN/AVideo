<?php

namespace React\Dns\Query;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\CancellablePromiseInterface;
use React\Promise\Timer;

class TimeoutExecutor implements ExecutorInterface
{
    private $executor;
    private $loop;
    private $timeout;

    public function __construct(ExecutorInterface $executor, $timeout, LoopInterface $loop)
    {
        $this->executor = $executor;
        $this->loop = $loop;
        $this->timeout = $timeout;
    }

    public function query($nameserver, Query $query)
    {
        return Timer\timeout($this->executor->query($nameserver, $query), $this->timeout, $this->loop)->then(null, function ($e) use ($query) {
            if ($e instanceof Timer\TimeoutException) {
                $e = new TimeoutException(sprintf("DNS query for %s timed out", $query->name), 0, $e);
            }
            throw $e;
        });
    }
}
