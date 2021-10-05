<?php

namespace Tests\Nyholm\Psr7;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Nyholm\Psr7\Request
 */
class RequestTest extends TestCase
{
    public function testRequestUriMayBeString()
    {
        $r = new Request('GET', '/');
        $this->assertEquals('/', (string) $r->getUri());
    }

    public function testRequestUriMayBeUri()
    {
        $uri = new Uri('/');
        $r = new Request('GET', $uri);
        $this->assertSame($uri, $r->getUri());
    }

    public function testValidateRequestUri()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse URI: "///"');

        new Request('GET', '///');
    }

    public function testCanConstructWithBody()
    {
        $r = new Request('GET', '/', [], 'baz');
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertEquals('baz', (string) $r->getBody());
    }

    public function testNullBody()
    {
        $r = new Request('GET', '/', [], null);
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('', (string) $r->getBody());
    }

    public function testFalseyBody()
    {
        $r = new Request('GET', '/', [], '0');
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('0', (string) $r->getBody());
    }

    public function testConstructorDoesNotReadStreamBody()
    {
        $body = $this->getMockBuilder(StreamInterface::class)->getMock();
        $body->expects($this->never())
            ->method('__toString');

        $r = new Request('GET', '/', [], $body);
        $this->assertSame($body, $r->getBody());
    }

    public function testWithUri()
    {
        $r1 = new Request('GET', '/');
        $u1 = $r1->getUri();
        $u2 = new Uri('http://www.example.com');
        $r2 = $r1->withUri($u2);
        $this->assertNotSame($r1, $r2);
        $this->assertSame($u2, $r2->getUri());
        $this->assertSame($u1, $r1->getUri());

        $r3 = new Request('GET', '/');
        $u3 = $r3->getUri();
        $r4 = $r3->withUri($u3);
        $this->assertSame($r3, $r4, 'If the Request did not change, then there is no need to create a new request object');

        $u4 = new Uri('/');
        $r5 = $r3->withUri($u4);
        $this->assertNotSame($r3, $r5);
    }

    public function testSameInstanceWhenSameUri()
    {
        $r1 = new Request('GET', 'http://foo.com');
        $r2 = $r1->withUri($r1->getUri());
        $this->assertSame($r1, $r2);
    }

    public function testWithRequestTarget()
    {
        $r1 = new Request('GET', '/');
        $r2 = $r1->withRequestTarget('*');
        $this->assertEquals('*', $r2->getRequestTarget());
        $this->assertEquals('/', $r1->getRequestTarget());
    }

    public function testWithInvalidRequestTarget()
    {
        $r = new Request('GET', '/');
        $this->expectException(\InvalidArgumentException::class);
        $r->withRequestTarget('foo bar');
    }

    public function testGetRequestTarget()
    {
        $r = new Request('GET', 'https://nyholm.tech');
        $this->assertEquals('/', $r->getRequestTarget());

        $r = new Request('GET', 'https://nyholm.tech/foo?bar=baz');
        $this->assertEquals('/foo?bar=baz', $r->getRequestTarget());

        $r = new Request('GET', 'https://nyholm.tech?bar=baz');
        $this->assertEquals('/?bar=baz', $r->getRequestTarget());
    }

    public function testRequestTargetDoesNotAllowSpaces()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target provided; cannot contain whitespace');

        $r1 = new Request('GET', '/');
        $r1->withRequestTarget('/foo bar');
    }

    public function testRequestTargetDefaultsToSlash()
    {
        $r1 = new Request('GET', '');
        $this->assertEquals('/', $r1->getRequestTarget());
        $r2 = new Request('GET', '*');
        $this->assertEquals('*', $r2->getRequestTarget());
        $r3 = new Request('GET', 'http://foo.com/bar baz/');
        $this->assertEquals('/bar%20baz/', $r3->getRequestTarget());
    }

    public function testBuildsRequestTarget()
    {
        $r1 = new Request('GET', 'http://foo.com/baz?bar=bam');
        $this->assertEquals('/baz?bar=bam', $r1->getRequestTarget());
    }

    public function testBuildsRequestTargetWithFalseyQuery()
    {
        $r1 = new Request('GET', 'http://foo.com/baz?0');
        $this->assertEquals('/baz?0', $r1->getRequestTarget());
    }

    public function testHostIsAddedFirst()
    {
        $r = new Request('GET', 'http://foo.com/baz?bar=bam', ['Foo' => 'Bar']);
        $this->assertEquals([
            'Host' => ['foo.com'],
            'Foo' => ['Bar'],
        ], $r->getHeaders());
    }

    public function testCanGetHeaderAsCsv()
    {
        $r = new Request('GET', 'http://foo.com/baz?bar=bam', [
            'Foo' => ['a', 'b', 'c'],
        ]);
        $this->assertEquals('a, b, c', $r->getHeaderLine('Foo'));
        $this->assertEquals('', $r->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwrittenWhenPreservingHost()
    {
        $r = new Request('GET', 'http://foo.com/baz?bar=bam', ['Host' => 'a.com']);
        $this->assertEquals(['Host' => ['a.com']], $r->getHeaders());
        $r2 = $r->withUri(new Uri('http://www.foo.com/bar'), true);
        $this->assertEquals('a.com', $r2->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri()
    {
        $r = new Request('GET', 'http://foo.com/baz?bar=bam');
        $this->assertEquals(['Host' => ['foo.com']], $r->getHeaders());
        $r2 = $r->withUri(new Uri('http://www.baz.com/bar'));
        $this->assertEquals('www.baz.com', $r2->getHeaderLine('Host'));
    }

    public function testAggregatesHeaders()
    {
        $r = new Request('GET', '', [
            'ZOO' => 'zoobar',
            'zoo' => ['foobar', 'zoobar'],
        ]);
        $this->assertEquals(['ZOO' => ['zoobar', 'foobar', 'zoobar']], $r->getHeaders());
        $this->assertEquals('zoobar, foobar, zoobar', $r->getHeaderLine('zoo'));
    }

    public function testSupportNumericHeaders()
    {
        $r = new Request('GET', '', [
            'Content-Length' => 200,
        ]);
        $this->assertSame(['Content-Length' => ['200']], $r->getHeaders());
        $this->assertSame('200', $r->getHeaderLine('Content-Length'));
    }

    public function testSupportNumericHeaderNames()
    {
        $r = new Request(
            'GET', '', [
                     '200' => 'NumericHeaderValue',
                     '0'   => 'NumericHeaderValueZero',
                 ]
        );

        $this->assertSame(
            [
                '200' => ['NumericHeaderValue'],
                '0'   => ['NumericHeaderValueZero'],
            ],
            $r->getHeaders()
        );

        $this->assertSame(['NumericHeaderValue'], $r->getHeader('200'));
        $this->assertSame('NumericHeaderValue', $r->getHeaderLine('200'));

        $this->assertSame(['NumericHeaderValueZero'], $r->getHeader('0'));
        $this->assertSame('NumericHeaderValueZero', $r->getHeaderLine('0'));

        $r = $r->withHeader('300', 'NumericHeaderValue2')
               ->withAddedHeader('200', ['A', 'B']);

        $this->assertSame(
            [
                '200' => ['NumericHeaderValue', 'A', 'B'],
                '0'   => ['NumericHeaderValueZero'],
                '300' => ['NumericHeaderValue2'],
            ],
            $r->getHeaders()
        );

        $r = $r->withoutHeader('300');
        $this->assertSame(
            [
                '200' => ['NumericHeaderValue', 'A', 'B'],
                '0'   => ['NumericHeaderValueZero'],
            ],
            $r->getHeaders()
        );
    }

    public function testAddsPortToHeader()
    {
        $r = new Request('GET', 'http://foo.com:8124/bar');
        $this->assertEquals('foo.com:8124', $r->getHeaderLine('host'));
    }

    public function testAddsPortToHeaderAndReplacePreviousPort()
    {
        $r = new Request('GET', 'http://foo.com:8124/bar');
        $r = $r->withUri(new Uri('http://foo.com:8125/bar'));
        $this->assertEquals('foo.com:8125', $r->getHeaderLine('host'));
    }

    public function testCannotHaveHeaderWithEmptyName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Header name must be an RFC 7230 compatible string.');
        $r = new Request('GET', 'https://example.com/');
        $r->withHeader('', 'Bar');
    }

    public function testCanHaveHeaderWithEmptyValue()
    {
        $r = new Request('GET', 'https://example.com/');
        $r = $r->withHeader('Foo', '');
        $this->assertEquals([''], $r->getHeader('Foo'));
    }

    public function testUpdateHostFromUri()
    {
        $request = new Request('GET', '/');
        $request = $request->withUri(new Uri('https://nyholm.tech'));
        $this->assertEquals('nyholm.tech', $request->getHeaderLine('Host'));

        $request = new Request('GET', 'https://example.com/');
        $this->assertEquals('example.com', $request->getHeaderLine('Host'));
        $request = $request->withUri(new Uri('https://nyholm.tech'));
        $this->assertEquals('nyholm.tech', $request->getHeaderLine('Host'));

        $request = new Request('GET', '/');
        $request = $request->withUri(new Uri('https://nyholm.tech:8080'));
        $this->assertEquals('nyholm.tech:8080', $request->getHeaderLine('Host'));

        $request = new Request('GET', '/');
        $request = $request->withUri(new Uri('https://nyholm.tech:443'));
        $this->assertEquals('nyholm.tech', $request->getHeaderLine('Host'));
    }
}
