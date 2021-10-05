<?php

namespace Tests\Nyholm\Psr7;

use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\ErrorHandler as SymfonyErrorHandler;

/**
 * @covers \Nyholm\Psr7\Stream
 */
class StreamTest extends TestCase
{
    public function testConstructorInitializesProperties()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');
        $stream = Stream::create($handle);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());
        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $handle = fopen('php://temp', 'r');
        $stream = Stream::create($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function testConvertsToString()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::create($handle);
        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);
        $stream->close();
    }

    public function testBuildFromString()
    {
        $stream = Stream::create('data');
        $this->assertEquals('', $stream->getContents());
        $this->assertEquals('data', $stream->__toString());
        $stream->close();
    }

    public function testGetsContents()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::create($handle);
        $this->assertEquals('', $stream->getContents());
        $stream->seek(0);
        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
    }

    public function testChecksEof()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');
        $stream = Stream::create($handle);
        $this->assertFalse($stream->eof());
        $stream->read(4);
        $this->assertTrue($stream->eof());
        $stream->close();
    }

    public function testGetSize()
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');
        $stream = Stream::create($handle);
        $this->assertEquals($size, $stream->getSize());
        // Load from cache
        $this->assertEquals($size, $stream->getSize());
        $stream->close();
    }

    public function testEnsuresSizeIsConsistent()
    {
        $h = fopen('php://temp', 'w+');
        $this->assertEquals(3, fwrite($h, 'foo'));
        $stream = Stream::create($h);
        $this->assertEquals(3, $stream->getSize());
        $this->assertEquals(4, $stream->write('test'));
        $this->assertEquals(7, $stream->getSize());
        $this->assertEquals(7, $stream->getSize());
        $stream->close();
    }

    public function testProvidesStreamPosition()
    {
        $handle = fopen('php://temp', 'w+');
        $stream = Stream::create($handle);
        $this->assertEquals(0, $stream->tell());
        $stream->write('foo');
        $this->assertEquals(3, $stream->tell());
        $stream->seek(1);
        $this->assertEquals(1, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());
        $stream->close();
    }

    public function testCanDetachStream()
    {
        $r = fopen('php://temp', 'w+');
        $stream = Stream::create($r);
        $stream->write('foo');
        $this->assertTrue($stream->isReadable());
        $this->assertSame($r, $stream->detach());
        $stream->detach();

        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());

        $throws = function (callable $fn) use ($stream) {
            try {
                $fn($stream);
                $this->fail();
            } catch (\Exception $e) {
                // Suppress the exception
            }
        };

        $throws(function ($stream) {
            $stream->read(10);
        });
        $throws(function ($stream) {
            $stream->write('bar');
        });
        $throws(function ($stream) {
            $stream->seek(10);
        });
        $throws(function ($stream) {
            $stream->tell();
        });
        $throws(function ($stream) {
            $stream->eof();
        });
        $throws(function ($stream) {
            $stream->getSize();
        });
        $throws(function ($stream) {
            $stream->getContents();
        });
        if (\PHP_VERSION_ID >= 70400) {
            $throws(function ($stream) {
                (string) $stream;
            });
        } else {
            $this->assertSame('', (string) $stream);

            SymfonyErrorHandler::register();
            $throws(function ($stream) {
                (string) $stream;
            });
            restore_error_handler();
            restore_exception_handler();
        }

        $stream->close();
    }

    public function testCloseClearProperties()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = Stream::create($handle);
        $stream->close();

        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
        $this->assertEmpty($stream->getMetadata());
    }

    public function testUnseekableStreamWrapper()
    {
        stream_wrapper_register('nyholm-psr7-test', TestStreamWrapper::class);
        $handle = fopen('nyholm-psr7-test://', 'r');
        stream_wrapper_unregister('nyholm-psr7-test');

        $stream = Stream::create($handle);
        $this->assertFalse($stream->isSeekable());
    }
}

class TestStreamWrapper
{
    public $context;

    public function stream_open()
    {
        return true;
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET)
    {
        return false;
    }

    public function stream_eof()
    {
        return true;
    }
}
