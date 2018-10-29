<?php

namespace React\Tests\Stream;

use React\Stream\DuplexResourceStream;
use React\EventLoop\Factory;
use React\Stream\WritableResourceStream;

/**
 * @group internet
 */
class FunctionalInternetTest extends TestCase
{
    public function testUploadKilobytePlain()
    {
        $size = 1000;
        $stream = stream_socket_client('tcp://httpbin.org:80');

        $loop = Factory::create();
        $stream = new DuplexResourceStream($stream, $loop);

        $buffer = '';
        $stream->on('data', function ($chunk) use (&$buffer) {
            $buffer .= $chunk;
        });

        $stream->on('error', $this->expectCallableNever());

        $stream->write("POST /post HTTP/1.0\r\nHost: httpbin.org\r\nContent-Length: $size\r\n\r\n" . str_repeat('.', $size));

        $loop->run();

        $this->assertNotEquals('', $buffer);
    }

    public function testUploadBiggerBlockPlain()
    {
        $size = 1000 * 30;
        $stream = stream_socket_client('tcp://httpbin.org:80');

        $loop = Factory::create();
        $stream = new DuplexResourceStream($stream, $loop);

        $buffer = '';
        $stream->on('data', function ($chunk) use (&$buffer) {
            $buffer .= $chunk;
        });

        $stream->on('error', $this->expectCallableNever());

        $stream->write("POST /post HTTP/1.0\r\nHost: httpbin.org\r\nContent-Length: $size\r\n\r\n" . str_repeat('.', $size));

        $loop->run();

        $this->assertNotEquals('', $buffer);
    }

    public function testUploadKilobyteSecure()
    {
        $size = 1000;
        $stream = stream_socket_client('tls://httpbin.org:443');

        $loop = Factory::create();
        $stream = new DuplexResourceStream($stream, $loop);

        $buffer = '';
        $stream->on('data', function ($chunk) use (&$buffer) {
            $buffer .= $chunk;
        });

        $stream->on('error', $this->expectCallableNever());

        $stream->write("POST /post HTTP/1.0\r\nHost: httpbin.org\r\nContent-Length: $size\r\n\r\n" . str_repeat('.', $size));

        $loop->run();

        $this->assertNotEquals('', $buffer);
    }

    public function testUploadBiggerBlockSecureRequiresSmallerChunkSize()
    {
        $size = 1000 * 30000;
        $stream = stream_socket_client('tls://httpbin.org:443');

        $loop = Factory::create();
        $stream = new DuplexResourceStream(
            $stream,
            $loop,
            null,
            new WritableResourceStream($stream, $loop, null, 8192)
        );

        $buffer = '';
        $stream->on('data', function ($chunk) use (&$buffer) {
            $buffer .= $chunk;
        });

        $stream->on('error', $this->expectCallableNever());

        $stream->write("POST /post HTTP/1.0\r\nHost: httpbin.org\r\nContent-Length: $size\r\n\r\n" . str_repeat('.', $size));

        $loop->run();

        $this->assertNotEquals('', $buffer);
    }
}
