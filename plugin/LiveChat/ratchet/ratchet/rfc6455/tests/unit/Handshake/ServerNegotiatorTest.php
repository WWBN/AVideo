<?php

namespace Ratchet\RFC6455\Test\Unit\Handshake;

use Ratchet\RFC6455\Handshake\RequestVerifier;
use Ratchet\RFC6455\Handshake\ServerNegotiator;

class ServerNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    public function testNoUpgradeRequested() {
        $negotiator = new ServerNegotiator(new RequestVerifier());

        $requestText = 'GET / HTTP/1.1
Host: 127.0.0.1:6789
Connection: keep-alive
Pragma: no-cache
Cache-Control: no-cache
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch, br
Accept-Language: en-US,en;q=0.8';

        $request = \GuzzleHttp\Psr7\parse_request($requestText);

        $response = $negotiator->handshake($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(426, $response->getStatusCode());
        $this->assertEquals('Upgrade header MUST be provided', $response->getReasonPhrase());
        $this->assertEquals('Upgrade', $response->getHeaderLine('Connection'));
        $this->assertEquals('websocket', $response->getHeaderLine('Upgrade'));
        $this->assertEquals('13', $response->getHeaderLine('Sec-WebSocket-Version'));
    }

    public function testNoConnectionUpgradeRequested() {
        $negotiator = new ServerNegotiator(new RequestVerifier());

        $requestText = 'GET / HTTP/1.1
Host: 127.0.0.1:6789
Connection: keep-alive
Pragma: no-cache
Cache-Control: no-cache
Upgrade: websocket
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch, br
Accept-Language: en-US,en;q=0.8';

        $request = \GuzzleHttp\Psr7\parse_request($requestText);

        $response = $negotiator->handshake($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Connection Upgrade MUST be requested', $response->getReasonPhrase());
    }

    public function testInvalidSecWebsocketKey() {
        $negotiator = new ServerNegotiator(new RequestVerifier());

        $requestText = 'GET / HTTP/1.1
Host: 127.0.0.1:6789
Connection: Upgrade
Pragma: no-cache
Cache-Control: no-cache
Upgrade: websocket
Sec-WebSocket-Key: 12345
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch, br
Accept-Language: en-US,en;q=0.8';

        $request = \GuzzleHttp\Psr7\parse_request($requestText);

        $response = $negotiator->handshake($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Invalid Sec-WebSocket-Key', $response->getReasonPhrase());
    }

    public function testInvalidSecWebsocketVersion() {
        $negotiator = new ServerNegotiator(new RequestVerifier());

        $requestText = 'GET / HTTP/1.1
Host: 127.0.0.1:6789
Connection: Upgrade
Pragma: no-cache
Cache-Control: no-cache
Upgrade: websocket
Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch, br
Accept-Language: en-US,en;q=0.8';

        $request = \GuzzleHttp\Psr7\parse_request($requestText);

        $response = $negotiator->handshake($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(426, $response->getStatusCode());
        $this->assertEquals('Upgrade Required', $response->getReasonPhrase());
        $this->assertEquals('Upgrade', $response->getHeaderLine('Connection'));
        $this->assertEquals('websocket', $response->getHeaderLine('Upgrade'));
        $this->assertEquals('13', $response->getHeaderLine('Sec-WebSocket-Version'));
    }

    public function testBadSubprotocolResponse() {
        $negotiator = new ServerNegotiator(new RequestVerifier());
        $negotiator->setStrictSubProtocolCheck(true);
        $negotiator->setSupportedSubProtocols([]);

        $requestText = 'GET / HTTP/1.1
Host: 127.0.0.1:6789
Connection: Upgrade
Pragma: no-cache
Cache-Control: no-cache
Upgrade: websocket
Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
Sec-WebSocket-Version: 13
Sec-WebSocket-Protocol: someprotocol
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch, br
Accept-Language: en-US,en;q=0.8';

        $request = \GuzzleHttp\Psr7\parse_request($requestText);

        $response = $negotiator->handshake($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(426, $response->getStatusCode());
        $this->assertEquals('No Sec-WebSocket-Protocols requested supported', $response->getReasonPhrase());
        $this->assertEquals('Upgrade', $response->getHeaderLine('Connection'));
        $this->assertEquals('websocket', $response->getHeaderLine('Upgrade'));
        $this->assertEquals('13', $response->getHeaderLine('Sec-WebSocket-Version'));
    }

    public function testNonStrictSubprotocolDoesNotIncludeHeaderWhenNoneAgreedOn() {
        $negotiator = new ServerNegotiator(new RequestVerifier());
        $negotiator->setStrictSubProtocolCheck(false);
        $negotiator->setSupportedSubProtocols(['someproto']);

        $requestText = 'GET / HTTP/1.1
Host: 127.0.0.1:6789
Connection: Upgrade
Pragma: no-cache
Cache-Control: no-cache
Upgrade: websocket
Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
Sec-WebSocket-Version: 13
Sec-WebSocket-Protocol: someotherproto
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch, br
Accept-Language: en-US,en;q=0.8';

        $request = \GuzzleHttp\Psr7\parse_request($requestText);

        $response = $negotiator->handshake($request);

        $this->assertEquals('1.1', $response->getProtocolVersion());
        $this->assertEquals(101, $response->getStatusCode());
        $this->assertEquals('Upgrade', $response->getHeaderLine('Connection'));
        $this->assertEquals('websocket', $response->getHeaderLine('Upgrade'));
        $this->assertFalse($response->hasHeader('Sec-WebSocket-Protocol'));
    }
}