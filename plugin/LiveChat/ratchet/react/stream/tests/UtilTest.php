<?php

namespace React\Tests\Stream;

use React\Stream\WritableResourceStream;
use React\Stream\Util;
use React\Stream\CompositeStream;
use React\Stream\ThroughStream;

/**
 * @covers React\Stream\Util
 */
class UtilTest extends TestCase
{
    public function testPipeReturnsDestinationStream()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();

        $ret = Util::pipe($readable, $writable);

        $this->assertSame($writable, $ret);
    }

    public function testPipeNonReadableSourceShouldDoNothing()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->any())
            ->method('isReadable')
            ->willReturn(false);

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->never())
            ->method('isWritable');
        $writable
            ->expects($this->never())
            ->method('end');

        Util::pipe($readable, $writable);
    }

    public function testPipeIntoNonWritableDestinationShouldPauseSource()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->any())
            ->method('isReadable')
            ->willReturn(true);
        $readable
            ->expects($this->once())
            ->method('pause');

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->any())
            ->method('isWritable')
            ->willReturn(false);
        $writable
            ->expects($this->never())
            ->method('end');

        Util::pipe($readable, $writable);
    }

    public function testPipeClosingDestPausesSource()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->any())
            ->method('isReadable')
            ->willReturn(true);
        $readable
            ->expects($this->once())
            ->method('pause');

        $writable = new ThroughStream();

        Util::pipe($readable, $writable);

        $writable->close();
    }

    public function testPipeWithEnd()
    {
        $readable = new Stub\ReadableStreamStub();

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);
        $writable
            ->expects($this->once())
            ->method('end');

        Util::pipe($readable, $writable);

        $readable->end();
    }

    public function testPipeWithoutEnd()
    {
        $readable = new Stub\ReadableStreamStub();

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);
        $writable
            ->expects($this->never())
            ->method('end');

        Util::pipe($readable, $writable, array('end' => false));

        $readable->end();
    }

    public function testPipeWithTooSlowWritableShouldPauseReadable()
    {
        $readable = new Stub\ReadableStreamStub();

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);
        $writable
            ->expects($this->once())
            ->method('write')
            ->with('some data')
            ->will($this->returnValue(false));

        $readable->pipe($writable);

        $this->assertFalse($readable->paused);
        $readable->write('some data');
        $this->assertTrue($readable->paused);
    }

    public function testPipeWithTooSlowWritableShouldResumeOnDrain()
    {
        $readable = new Stub\ReadableStreamStub();

        $onDrain = null;

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);
        $writable
            ->expects($this->any())
            ->method('on')
            ->will($this->returnCallback(function ($name, $callback) use (&$onDrain) {
                if ($name === 'drain') {
                    $onDrain = $callback;
                }
            }));

        $readable->pipe($writable);
        $readable->pause();

        $this->assertTrue($readable->paused);
        $this->assertNotNull($onDrain);
        $onDrain();
        $this->assertFalse($readable->paused);
    }

    public function testPipeWithWritableResourceStream()
    {
        $readable = new Stub\ReadableStreamStub();

        $stream = fopen('php://temp', 'r+');
        $loop = $this->createLoopMock();
        $buffer = new WritableResourceStream($stream, $loop);

        $readable->pipe($buffer);

        $readable->write('hello, I am some ');
        $readable->write('random data');

        $buffer->handleWrite();
        rewind($stream);
        $this->assertSame('hello, I am some random data', stream_get_contents($stream));
    }

    public function testPipeSetsUpListeners()
    {
        $source = new ThroughStream();
        $dest = new ThroughStream();

        $this->assertCount(0, $source->listeners('data'));
        $this->assertCount(0, $source->listeners('end'));
        $this->assertCount(0, $dest->listeners('drain'));

        Util::pipe($source, $dest);

        $this->assertCount(1, $source->listeners('data'));
        $this->assertCount(1, $source->listeners('end'));
        $this->assertCount(1, $dest->listeners('drain'));
    }

    public function testPipeClosingSourceRemovesListeners()
    {
        $source = new ThroughStream();
        $dest = new ThroughStream();

        Util::pipe($source, $dest);

        $source->close();

        $this->assertCount(0, $source->listeners('data'));
        $this->assertCount(0, $source->listeners('end'));
        $this->assertCount(0, $dest->listeners('drain'));
    }

    public function testPipeClosingDestRemovesListeners()
    {
        $source = new ThroughStream();
        $dest = new ThroughStream();

        Util::pipe($source, $dest);

        $dest->close();

        $this->assertCount(0, $source->listeners('data'));
        $this->assertCount(0, $source->listeners('end'));
        $this->assertCount(0, $dest->listeners('drain'));
    }

    public function testPipeDuplexIntoSelfEndsOnEnd()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable->expects($this->any())->method('isReadable')->willReturn(true);
        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable->expects($this->any())->method('isWritable')->willReturn(true);
        $duplex = new CompositeStream($readable, $writable);

        Util::pipe($duplex, $duplex);

        $writable->expects($this->once())->method('end');

        $duplex->emit('end');
    }

    /** @test */
    public function forwardEventsShouldSetupForwards()
    {
        $source = new ThroughStream();
        $target = new ThroughStream();

        Util::forwardEvents($source, $target, array('data'));
        $target->on('data', $this->expectCallableOnce());
        $target->on('foo', $this->expectCallableNever());

        $source->emit('data', array('hello'));
        $source->emit('foo', array('bar'));
    }

    private function createLoopMock()
    {
        return $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
    }

    private function notEqualTo($value)
    {
        return new \PHPUnit_Framework_Constraint_Not($value);
    }
}
