<?php

declare(strict_types=1);

namespace Tests\Nyholm\Psr7\Factory;

use Nyholm\Psr7\Factory\HttplugFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HttplugFactoryTest extends TestCase
{
    public function testCreateRequest()
    {
        $factory = new HttplugFactory();
        $r = $factory->createRequest('POST', 'https://nyholm.tech', ['Content-Type' => 'text/html'], 'foobar', '2.0');

        $this->assertEquals('POST', $r->getMethod());
        $this->assertEquals('https://nyholm.tech', $r->getUri()->__toString());
        $this->assertEquals('2.0', $r->getProtocolVersion());
        $this->assertEquals('foobar', $r->getBody()->__toString());

        $headers = $r->getHeaders();
        $this->assertCount(2, $headers); // Including HOST
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('text/html', $headers['Content-Type'][0]);
    }

    public function testCreateResponse()
    {
        $factory = new HttplugFactory();
        $r = $factory->createResponse(217, 'Perfect', ['Content-Type' => 'text/html'], 'foobar', '2.0');

        $this->assertEquals(217, $r->getStatusCode());
        $this->assertEquals('Perfect', $r->getReasonPhrase());
        $this->assertEquals('2.0', $r->getProtocolVersion());
        $this->assertEquals('foobar', $r->getBody()->__toString());

        $headers = $r->getHeaders();
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('text/html', $headers['Content-Type'][0]);
    }

    public function testCreateStream()
    {
        $factory = new HttplugFactory();
        $stream = $factory->createStream('foobar');

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertEquals('foobar', $stream->__toString());
    }

    public function testCreateUri()
    {
        $factory = new HttplugFactory();
        $uri = $factory->createUri('https://nyholm.tech/foo');

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertEquals('https://nyholm.tech/foo', $uri->__toString());
    }
}
