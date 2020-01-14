<?php
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\RFC6455\Messaging\FrameInterface;
use Ratchet\RFC6455\Messaging\Frame;

require_once __DIR__ . "/../bootstrap.php";

$loop   = \React\EventLoop\Factory::create();

$socket = new \React\Socket\Server($loop);
$server = new \React\Http\Server($socket);

$closeFrameChecker = new \Ratchet\RFC6455\Messaging\CloseFrameChecker;
$negotiator = new \Ratchet\RFC6455\Handshake\ServerNegotiator(new \Ratchet\RFC6455\Handshake\RequestVerifier);

$uException = new \UnderflowException;

$server->on('request', function (\React\Http\Request $request, \React\Http\Response $response) use ($negotiator, $closeFrameChecker, $uException) {
    $psrRequest = new \GuzzleHttp\Psr7\Request($request->getMethod(), $request->getPath(), $request->getHeaders());

    $negotiatorResponse = $negotiator->handshake($psrRequest);

    $response->writeHead(
        $negotiatorResponse->getStatusCode(),
        array_merge(
            $negotiatorResponse->getHeaders(),
            ["Content-Length" => "0"]
        )
    );

    if ($negotiatorResponse->getStatusCode() !== 101) {
        $response->end();
        return;
    }

    $parser = new \Ratchet\RFC6455\Messaging\MessageBuffer($closeFrameChecker, function(MessageInterface $message) use ($response) {
        $response->write($message->getContents());
    }, function(FrameInterface $frame) use ($response, &$parser) {
        switch ($frame->getOpCode()) {
            case Frame::OP_CLOSE:
                $response->end($frame->getContents());
                break;
            case Frame::OP_PING:
                $response->write($parser->newFrame($frame->getPayload(), true, Frame::OP_PONG)->getContents());
                break;
        }
    }, true, function() use ($uException) {
        return $uException;
    });

    $request->on('data', [$parser, 'onData']);
});

$socket->listen(9001, '0.0.0.0');
$loop->run();
