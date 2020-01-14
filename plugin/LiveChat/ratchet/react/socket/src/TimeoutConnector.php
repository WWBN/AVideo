<?php

namespace React\Socket;

use React\Socket\ConnectorInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Timer;

final class TimeoutConnector implements ConnectorInterface
{
    private $connector;
    private $timeout;
    private $loop;

    public function __construct(ConnectorInterface $connector, $timeout, LoopInterface $loop)
    {
        $this->connector = $connector;
        $this->timeout = $timeout;
        $this->loop = $loop;
    }

    public function connect($uri)
    {
        return Timer\timeout($this->connector->connect($uri), $this->timeout, $this->loop);
    }
}
