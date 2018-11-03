<?php
use GuzzleHttp\Psr7\Uri;
use React\Promise\Deferred;
use Ratchet\RFC6455\Messaging\Frame;

require __DIR__ . '/../bootstrap.php';

define('AGENT', 'RatchetRFC/0.0.0');

$testServer = "127.0.0.1";

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new \React\SocketClient\Connector($loop, $dnsResolver);

function echoStreamerFactory($conn)
{
    return new \Ratchet\RFC6455\Messaging\MessageBuffer(
        new \Ratchet\RFC6455\Messaging\CloseFrameChecker,
        function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn) {
            /** @var Frame $frame */
            foreach ($msg as $frame) {
                $frame->maskPayload();
            }
            $conn->write($msg->getContents());
        },
        function (\Ratchet\RFC6455\Messaging\FrameInterface $frame) use ($conn) {
            switch ($frame->getOpcode()) {
                case Frame::OP_PING:
                    return $conn->write((new Frame($frame->getPayload(), true, Frame::OP_PONG))->maskPayload()->getContents());
                    break;
                case Frame::OP_CLOSE:
                    return $conn->end((new Frame($frame->getPayload(), true, Frame::OP_CLOSE))->maskPayload()->getContents());
                    break;
            }
        },
        false
    );
}

function getTestCases() {
    global $factory;
    global $testServer;

    $deferred = new Deferred();

    $factory->create($testServer, 9001)->then(function (\React\Stream\Stream $stream) use ($deferred) {
        $cn = new \Ratchet\RFC6455\Handshake\ClientNegotiator();
        $cnRequest = $cn->generateRequest(new Uri('ws://127.0.0.1:9001/getCaseCount'));

        $rawResponse = "";
        $response = null;

        /** @var \Ratchet\RFC6455\Messaging\Streaming\MessageBuffer $ms */
        $ms = null;

        $stream->on('data', function ($data) use ($stream, &$rawResponse, &$response, &$ms, $cn, $deferred, &$context, $cnRequest) {
            if ($response === null) {
                $rawResponse .= $data;
                $pos = strpos($rawResponse, "\r\n\r\n");
                if ($pos) {
                    $data = substr($rawResponse, $pos + 4);
                    $rawResponse = substr($rawResponse, 0, $pos + 4);
                    $response = \GuzzleHttp\Psr7\parse_response($rawResponse);

                    if (!$cn->validateResponse($cnRequest, $response)) {
                        $stream->end();
                        $deferred->reject();
                    } else {
                        $ms = new \Ratchet\RFC6455\Messaging\MessageBuffer(
                            new \Ratchet\RFC6455\Messaging\CloseFrameChecker,
                            function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($deferred, $stream) {
                                $deferred->resolve($msg->getPayload());
                                $stream->close();
                            },
                            null,
                            false
                        );
                    }
                }
            }

            // feed the message streamer
            if ($ms) {
                $ms->onData($data);
            }
        });

        $stream->write(\GuzzleHttp\Psr7\str($cnRequest));
    });

    return $deferred->promise();
}

function runTest($case)
{
    global $factory;
    global $testServer;

    $casePath = "/runCase?case={$case}&agent=" . AGENT;

    $deferred = new Deferred();

    $factory->create($testServer, 9001)->then(function (\React\Stream\Stream $stream) use ($deferred, $casePath, $case) {
        $cn = new \Ratchet\RFC6455\Handshake\ClientNegotiator();
        $cnRequest = $cn->generateRequest(new Uri('ws://127.0.0.1:9001' . $casePath));

        $rawResponse = "";
        $response = null;

        $ms = null;

        $stream->on('data', function ($data) use ($stream, &$rawResponse, &$response, &$ms, $cn, $deferred, &$context, $cnRequest) {
            if ($response === null) {
                $rawResponse .= $data;
                $pos = strpos($rawResponse, "\r\n\r\n");
                if ($pos) {
                    $data = substr($rawResponse, $pos + 4);
                    $rawResponse = substr($rawResponse, 0, $pos + 4);
                    $response = \GuzzleHttp\Psr7\parse_response($rawResponse);

                    if (!$cn->validateResponse($cnRequest, $response)) {
                        $stream->end();
                        $deferred->reject();
                    } else {
                        $ms = echoStreamerFactory($stream);
                    }
                }
            }

            // feed the message streamer
            if ($ms) {
                $ms->onData($data);
            }
        });

        $stream->on('close', function () use ($deferred) {
            $deferred->resolve();
        });

        $stream->write(\GuzzleHttp\Psr7\str($cnRequest));
    });

    return $deferred->promise();
}

function createReport() {
    global $factory;
    global $testServer;

    $deferred = new Deferred();

    $factory->create($testServer, 9001)->then(function (\React\Stream\Stream $stream) use ($deferred) {
        $reportPath = "/updateReports?agent=" . AGENT . "&shutdownOnComplete=true";
        $cn = new \Ratchet\RFC6455\Handshake\ClientNegotiator();
        $cnRequest = $cn->generateRequest(new Uri('ws://127.0.0.1:9001' . $reportPath));

        $rawResponse = "";
        $response = null;

        /** @var \Ratchet\RFC6455\Messaging\MessageBuffer $ms */
        $ms = null;

        $stream->on('data', function ($data) use ($stream, &$rawResponse, &$response, &$ms, $cn, $deferred, &$context, $cnRequest) {
            if ($response === null) {
                $rawResponse .= $data;
                $pos = strpos($rawResponse, "\r\n\r\n");
                if ($pos) {
                    $data = substr($rawResponse, $pos + 4);
                    $rawResponse = substr($rawResponse, 0, $pos + 4);
                    $response = \GuzzleHttp\Psr7\parse_response($rawResponse);

                    if (!$cn->validateResponse($cnRequest, $response)) {
                        $stream->end();
                        $deferred->reject();
                    } else {
                        $ms = new \Ratchet\RFC6455\Messaging\MessageBuffer(
                            new \Ratchet\RFC6455\Messaging\CloseFrameChecker,
                            function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($deferred, $stream) {
                                $deferred->resolve($msg->getPayload());
                                $stream->close();
                            },
                            null,
                            false
                        );
                    }
                }
            }

            // feed the message streamer
            if ($ms) {
                $ms->onData($data);
            }
        });

        $stream->write(\GuzzleHttp\Psr7\str($cnRequest));
    });

    return $deferred->promise();
}


$testPromises = [];

getTestCases()->then(function ($count) use ($loop) {
    $allDeferred = new Deferred();

    $runNextCase = function () use (&$i, &$runNextCase, $count, $allDeferred) {
        $i++;
        if ($i > $count) {
            $allDeferred->resolve();
            return;
        }
        runTest($i)->then($runNextCase);
    };

    $i = 0;
    $runNextCase();

    $allDeferred->promise()->then(function () {
        createReport();
    });
});

$loop->run();
