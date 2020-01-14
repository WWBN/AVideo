<?php

namespace React\Tests\Stream;

use Clue\StreamFilter as Filter;
use React\Stream\WritableResourceStream;

class WritableResourceStreamTest extends TestCase
{
    /**
     * @covers React\Stream\WritableResourceStream::__construct
     */
    public function testConstructor()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());
    }

    /**
     * @covers React\Stream\WritableResourceStream::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsIfNotAValidStreamResource()
    {
        $stream = null;
        $loop = $this->createLoopMock();

        new WritableResourceStream($stream, $loop);
    }

    /**
     * @covers React\Stream\WritableResourceStream::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnReadOnlyStream()
    {
        $stream = fopen('php://temp', 'r');
        $loop = $this->createLoopMock();

        new WritableResourceStream($stream, $loop);
    }

    /**
     * @covers React\Stream\WritableResourceStream::__construct
     */
    public function testConstructorThrowsExceptionIfStreamDoesNotSupportNonBlocking()
    {
        if (!in_array('blocking', stream_get_wrappers())) {
            stream_wrapper_register('blocking', 'React\Tests\Stream\EnforceBlockingWrapper');
        }

        $stream = fopen('blocking://test', 'r+');
        $loop = $this->createLoopMock();

        $this->setExpectedException('RuntimeException');
        new WritableResourceStream($stream, $loop);
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testWrite()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createWriteableLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());

        $buffer->write("foobar\n");
        rewind($stream);
        $this->assertSame("foobar\n", fread($stream, 1024));
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     */
    public function testWriteWithDataDoesAddResourceToLoop()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $loop->expects($this->once())->method('addWriteStream')->with($this->equalTo($stream));

        $buffer = new WritableResourceStream($stream, $loop);

        $buffer->write("foobar\n");
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testEmptyWriteDoesNotAddToLoop()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $loop->expects($this->never())->method('addWriteStream');

        $buffer = new WritableResourceStream($stream, $loop);

        $buffer->write("");
        $buffer->write(null);
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testWriteReturnsFalseWhenWritableResourceStreamIsFull()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createWriteableLoopMock();
        $loop->preventWrites = true;

        $buffer = new WritableResourceStream($stream, $loop, 4);
        $buffer->on('error', $this->expectCallableNever());

        $this->assertTrue($buffer->write("foo"));
        $loop->preventWrites = false;
        $this->assertFalse($buffer->write("bar\n"));
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     */
    public function testWriteReturnsFalseWhenWritableResourceStreamIsExactlyFull()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop, 3);

        $this->assertFalse($buffer->write("foo"));
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testWriteDetectsWhenOtherSideIsClosed()
    {
        list($a, $b) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        $loop = $this->createWriteableLoopMock();

        $buffer = new WritableResourceStream($a, $loop, 4);
        $buffer->on('error', $this->expectCallableOnce());

        fclose($b);

        $buffer->write("foo");
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testEmitsDrainAfterWriteWhichExceedsBuffer()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop, 2);
        $buffer->on('error', $this->expectCallableNever());
        $buffer->on('drain', $this->expectCallableOnce());

        $buffer->write("foo");
        $buffer->handleWrite();
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testWriteInDrain()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop, 2);
        $buffer->on('error', $this->expectCallableNever());

        $buffer->once('drain', function () use ($buffer) {
            $buffer->write("bar\n");
            $buffer->handleWrite();
        });

        $this->assertFalse($buffer->write("foo\n"));
        $buffer->handleWrite();

        fseek($stream, 0);
        $this->assertSame("foo\nbar\n", stream_get_contents($stream));
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testDrainAfterWrite()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop, 2);

        $buffer->on('drain', $this->expectCallableOnce());

        $buffer->write("foo");
        $buffer->handleWrite();
    }

    /**
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testDrainAfterWriteWillRemoveResourceFromLoopWithoutClosing()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $loop->expects($this->once())->method('removeWriteStream')->with($stream);

        $buffer = new WritableResourceStream($stream, $loop, 2);

        $buffer->on('drain', $this->expectCallableOnce());

        $buffer->on('close', $this->expectCallableNever());

        $buffer->write("foo");
        $buffer->handleWrite();
    }

    /**
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testClosingDuringDrainAfterWriteWillRemoveResourceFromLoopOnceAndClose()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $loop->expects($this->once())->method('removeWriteStream')->with($stream);

        $buffer = new WritableResourceStream($stream, $loop, 2);

        $buffer->on('drain', function () use ($buffer) {
            $buffer->close();
        });

        $buffer->on('close', $this->expectCallableOnce());

        $buffer->write("foo");
        $buffer->handleWrite();
    }

    /**
     * @covers React\Stream\WritableResourceStream::end
     */
    public function testEndWithoutDataClosesImmediatelyIfWritableResourceStreamIsEmpty()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());
        $buffer->on('close', $this->expectCallableOnce());

        $this->assertTrue($buffer->isWritable());
        $buffer->end();
        $this->assertFalse($buffer->isWritable());
    }

    /**
     * @covers React\Stream\WritableResourceStream::end
     */
    public function testEndWithoutDataDoesNotCloseIfWritableResourceStreamIsFull()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());
        $buffer->on('close', $this->expectCallableNever());

        $buffer->write('foo');

        $this->assertTrue($buffer->isWritable());
        $buffer->end();
        $this->assertFalse($buffer->isWritable());
    }

    /**
     * @covers React\Stream\WritableResourceStream::end
     */
    public function testEndWithDataClosesImmediatelyIfWritableResourceStreamFlushes()
    {
        $stream = fopen('php://temp', 'r+');
        $filterBuffer = '';
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());
        $buffer->on('close', $this->expectCallableOnce());

        Filter\append($stream, function ($chunk) use (&$filterBuffer) {
            $filterBuffer .= $chunk;
            return $chunk;
        });

        $this->assertTrue($buffer->isWritable());
        $buffer->end('final words');
        $this->assertFalse($buffer->isWritable());

        $buffer->handleWrite();
        $this->assertSame('final words', $filterBuffer);
    }

    /**
     * @covers React\Stream\WritableResourceStream::end
     */
    public function testEndWithDataDoesNotCloseImmediatelyIfWritableResourceStreamIsFull()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());
        $buffer->on('close', $this->expectCallableNever());

        $buffer->write('foo');

        $this->assertTrue($buffer->isWritable());
        $buffer->end('final words');
        $this->assertFalse($buffer->isWritable());

        rewind($stream);
        $this->assertSame('', stream_get_contents($stream));
    }

    /**
     * @covers React\Stream\WritableResourceStream::isWritable
     * @covers React\Stream\WritableResourceStream::close
     */
    public function testClose()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', $this->expectCallableNever());
        $buffer->on('close', $this->expectCallableOnce());

        $this->assertTrue($buffer->isWritable());
        $buffer->close();
        $this->assertFalse($buffer->isWritable());

        $this->assertEquals(array(), $buffer->listeners('close'));
    }

    /**
     * @covers React\Stream\WritableResourceStream::close
     */
    public function testClosingAfterWriteRemovesStreamFromLoop()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $buffer = new WritableResourceStream($stream, $loop);

        $loop->expects($this->once())->method('removeWriteStream')->with($stream);

        $buffer->write('foo');
        $buffer->close();
    }

    /**
     * @covers React\Stream\WritableResourceStream::close
     */
    public function testClosingWithoutWritingDoesNotRemoveStreamFromLoop()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $buffer = new WritableResourceStream($stream, $loop);

        $loop->expects($this->never())->method('removeWriteStream');

        $buffer->close();
    }

    /**
     * @covers React\Stream\WritableResourceStream::close
     */
    public function testDoubleCloseWillEmitOnlyOnce()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('close', $this->expectCallableOnce());

        $buffer->close();
        $buffer->close();
    }

    /**
     * @covers React\Stream\WritableResourceStream::write
     * @covers React\Stream\WritableResourceStream::close
     */
    public function testWritingToClosedWritableResourceStreamShouldNotWriteToStream()
    {
        $stream = fopen('php://temp', 'r+');
        $filterBuffer = '';
        $loop = $this->createLoopMock();

        $buffer = new WritableResourceStream($stream, $loop);

        Filter\append($stream, function ($chunk) use (&$filterBuffer) {
            $filterBuffer .= $chunk;
            return $chunk;
        });

        $buffer->close();

        $buffer->write('foo');

        $buffer->handleWrite();
        $this->assertSame('', $filterBuffer);
    }

    /**
     * @covers React\Stream\WritableResourceStream::handleWrite
     */
    public function testErrorWhenStreamResourceIsInvalid()
    {
        $stream = fopen('php://temp', 'r+');
        $loop = $this->createWriteableLoopMock();

        $error = null;

        $buffer = new WritableResourceStream($stream, $loop);
        $buffer->on('error', function ($message) use (&$error) {
            $error = $message;
        });

        // invalidate stream resource
        fclose($stream);

        $buffer->write('Attempting to write to bad stream');

        $this->assertInstanceOf('Exception', $error);

        // the error messages differ between PHP versions, let's just check substrings
        $this->assertContains('Unable to write to stream: ', $error->getMessage());
        $this->assertContains(' not a valid stream resource', $error->getMessage(), '', true);
    }

    public function testWritingToClosedStream()
    {
        if ('Darwin' === PHP_OS) {
            $this->markTestSkipped('OS X issue with shutting down pair for writing');
        }

        list($a, $b) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $loop = $this->createLoopMock();

        $error = null;

        $buffer = new WritableResourceStream($a, $loop);
        $buffer->on('error', function($message) use (&$error) {
            $error = $message;
        });

        $buffer->write('foo');
        $buffer->handleWrite();
        stream_socket_shutdown($b, STREAM_SHUT_RD);
        stream_socket_shutdown($a, STREAM_SHUT_RD);
        $buffer->write('bar');
        $buffer->handleWrite();

        $this->assertInstanceOf('Exception', $error);
        $this->assertSame('Unable to write to stream: fwrite(): send of 3 bytes failed with errno=32 Broken pipe', $error->getMessage());
    }

    private function createWriteableLoopMock()
    {
        $loop = $this->createLoopMock();
        $loop->preventWrites = false;
        $loop
            ->expects($this->any())
            ->method('addWriteStream')
            ->will($this->returnCallback(function ($stream, $listener) use ($loop) {
                if (!$loop->preventWrites) {
                    call_user_func($listener, $stream);
                }
            }));

        return $loop;
    }

    private function createLoopMock()
    {
        return $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
    }
}
