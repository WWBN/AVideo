<?php

namespace React\Tests\Stream;

use React\Stream\DuplexResourceStream;
use React\EventLoop as rel;
use React\Stream\ReadableResourceStream;

class DuplexResourceStreamIntegrationTest extends TestCase
{
    public function loopProvider()
    {
        return array(
            array(function() { return true; }, function() { return new rel\StreamSelectLoop; }),
            array(function() { return function_exists('event_base_new'); }, function() { return new rel\LibEventLoop; }),
            array(function() { return class_exists('libev\EventLoop'); }, function() { return new rel\LibEvLoop; }),
            array(function() { return class_exists('EventBase'); }, function() { return new rel\ExtEventLoop; })
        );
    }

    /**
     * @dataProvider loopProvider
     */
    public function testBufferReadsLargeChunks($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        list($sockA, $sockB) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

        $bufferSize = 4096;
        $streamA = new DuplexResourceStream($sockA, $loop, $bufferSize);
        $streamB = new DuplexResourceStream($sockB, $loop, $bufferSize);

        $testString = str_repeat("*", $bufferSize + 1);

        $buffer = "";
        $streamB->on('data', function ($data) use (&$buffer) {
            $buffer .= $data;
        });

        $streamA->write($testString);

        $loop->tick();
        $loop->tick();
        $loop->tick();

        $streamA->close();
        $streamB->close();

        $this->assertEquals($testString, $buffer);
    }

    /**
     * @dataProvider loopProvider
     */
    public function testWriteLargeChunk($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        list($sockA, $sockB) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

        $streamA = new DuplexResourceStream($sockA, $loop);
        $streamB = new DuplexResourceStream($sockB, $loop);

        // limit seems to be 192 KiB
        $size = 256 * 1024;

        // sending side sends and expects clean close with no errors
        $streamA->end(str_repeat('*', $size));
        $streamA->on('close', $this->expectCallableOnce());
        $streamA->on('error', $this->expectCallableNever());

        // receiving side counts bytes and expects clean close with no errors
        $received = 0;
        $streamB->on('data', function ($chunk) use (&$received) {
            $received += strlen($chunk);
        });
        $streamB->on('close', $this->expectCallableOnce());
        $streamB->on('error', $this->expectCallableNever());

        $loop->run();

        $streamA->close();
        $streamB->close();

        $this->assertEquals($size, $received);
    }

    /**
     * @dataProvider loopProvider
     */
    public function testDoesNotEmitDataIfNothingHasBeenWritten($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        list($sockA, $sockB) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

        $streamA = new DuplexResourceStream($sockA, $loop);
        $streamB = new DuplexResourceStream($sockB, $loop);

        // end streamA without writing any data
        $streamA->end();

        // streamB should not emit any data
        $streamB->on('data', $this->expectCallableNever());

        $loop->run();

        $streamA->close();
        $streamB->close();
    }

    /**
     * @dataProvider loopProvider
     */
    public function testDoesNotWriteDataIfRemoteSideFromPairHasBeenClosed($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        list($sockA, $sockB) = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);

        $streamA = new DuplexResourceStream($sockA, $loop);
        $streamB = new DuplexResourceStream($sockB, $loop);

        // end streamA without writing any data
        $streamA->pause();
        $streamA->write('hello');
        $streamA->on('close', $this->expectCallableOnce());

        $streamB->on('data', $this->expectCallableNever());
        $streamB->close();

        $loop->run();

        $streamA->close();
        $streamB->close();
    }

    /**
     * @dataProvider loopProvider
     */
    public function testDoesNotWriteDataIfServerSideHasBeenClosed($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        $server = stream_socket_server('tcp://127.0.0.1:0');

        $client = stream_socket_client(stream_socket_get_name($server, false));
        $peer = stream_socket_accept($server);

        $streamA = new DuplexResourceStream($client, $loop);
        $streamB = new DuplexResourceStream($peer, $loop);

        // end streamA without writing any data
        $streamA->pause();
        $streamA->write('hello');
        $streamA->on('close', $this->expectCallableOnce());

        $streamB->on('data', $this->expectCallableNever());
        $streamB->close();

        $loop->run();

        $streamA->close();
        $streamB->close();
    }

    /**
     * @dataProvider loopProvider
     */
    public function testDoesNotWriteDataIfClientSideHasBeenClosed($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        $server = stream_socket_server('tcp://127.0.0.1:0');

        $client = stream_socket_client(stream_socket_get_name($server, false));
        $peer = stream_socket_accept($server);

        $streamA = new DuplexResourceStream($peer, $loop);
        $streamB = new DuplexResourceStream($client, $loop);

        // end streamA without writing any data
        $streamA->pause();
        $streamA->write('hello');
        $streamA->on('close', $this->expectCallableOnce());

        $streamB->on('data', $this->expectCallableNever());
        $streamB->close();

        $loop->run();

        $streamA->close();
        $streamB->close();
    }

    /**
     * @dataProvider loopProvider
     */
    public function testReadsSingleChunkFromProcessPipe($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        $stream = new ReadableResourceStream(popen('echo test', 'r'), $loop);
        $stream->on('data', $this->expectCallableOnceWith("test\n"));
        $stream->on('end', $this->expectCallableOnce());
        $stream->on('error', $this->expectCallableNever());

        $loop->run();
    }

    /**
     * @dataProvider loopProvider
     */
    public function testReadsMultipleChunksFromProcessPipe($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        $stream = new ReadableResourceStream(popen('echo -n a;sleep 0.1;echo -n b;sleep 0.1;echo -n c', 'r'), $loop);

        $buffer = '';
        $stream->on('data', function ($chunk) use (&$buffer) {
            $buffer .= $chunk;
        });

        $stream->on('end', $this->expectCallableOnce());
        $stream->on('error', $this->expectCallableNever());

        $loop->run();

        $this->assertEquals('abc', $buffer);
    }

    /**
     * @dataProvider loopProvider
     */
    public function testReadsLongChunksFromProcessPipe($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        $stream = new ReadableResourceStream(popen('dd if=/dev/zero bs=12345 count=1234 2>&-', 'r'), $loop);

        $bytes = 0;
        $stream->on('data', function ($chunk) use (&$bytes) {
            $bytes += strlen($chunk);
        });

        $stream->on('end', $this->expectCallableOnce());
        $stream->on('error', $this->expectCallableNever());

        $loop->run();

        $this->assertEquals(12345 * 1234, $bytes);
    }

    /**
     * @dataProvider loopProvider
     */
    public function testReadsNothingFromProcessPipeWithNoOutput($condition, $loopFactory)
    {
        if (true !== $condition()) {
            return $this->markTestSkipped('Loop implementation not available');
        }

        $loop = $loopFactory();

        $stream = new ReadableResourceStream(popen('true', 'r'), $loop);
        $stream->on('data', $this->expectCallableNever());
        $stream->on('end', $this->expectCallableOnce());
        $stream->on('error', $this->expectCallableNever());

        $loop->run();
    }
}
