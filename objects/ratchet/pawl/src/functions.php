<?php
namespace Ratchet\Client;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as ReactFactory;
use React\EventLoop\Timer\Timer;

/**
 * @param string             $url
 * @param array              $subProtocols
 * @param array              $headers
 * @param LoopInterface|null $loop
 * @return \React\Promise\PromiseInterface<\Ratchet\Client\WebSocket>
 */
function connect($url, array $subProtocols = [], $headers = [], LoopInterface $loop = null) {
    $loop = $loop ?: ReactFactory::create();

    $connector = new Connector($loop);
    $connection = $connector($url, $subProtocols, $headers);

    $runHasBeenCalled = false;

    $loop->addTimer(Timer::MIN_INTERVAL, function () use (&$runHasBeenCalled) {
        $runHasBeenCalled = true;
    });

    register_shutdown_function(function() use ($loop, &$runHasBeenCalled) {
        if (!$runHasBeenCalled) {
            $loop->run();
        }
    });

    return $connection;
}
