<?php

namespace React\Tests\Stream;

use React\Stream\CompositeStream;
use React\Stream\ThroughStream;

/**
 * @covers React\Stream\CompositeStream
 */
class CompositeStreamTest extends TestCase
{
    /** @test */
    public function itShouldCloseReadableIfNotWritable()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $readable
            ->expects($this->once())
            ->method('close');

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->once())
            ->method('isWritable')
            ->willReturn(false);

        $composite = new CompositeStream($readable, $writable);

        $composite->on('close', $this->expectCallableNever());
        $composite->close();
    }

    /** @test */
    public function itShouldCloseWritableIfNotReadable()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(false);

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->once())
            ->method('close');

        $composite = new CompositeStream($readable, $writable);

        $composite->on('close', $this->expectCallableNever());
        $composite->close();
    }

    /** @test */
    public function itShouldForwardWritableCallsToWritableStream()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->once())
            ->method('write')
            ->with('foo');
        $writable
            ->expects($this->exactly(2))
            ->method('isWritable')
            ->willReturn(true);

        $composite = new CompositeStream($readable, $writable);
        $composite->write('foo');
        $composite->isWritable();
    }

    /** @test */
    public function itShouldForwardReadableCallsToReadableStream()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->exactly(2))
            ->method('isReadable')
            ->willReturn(true);
        $readable
            ->expects($this->once())
            ->method('pause');
        $readable
            ->expects($this->once())
            ->method('resume');

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->any())
            ->method('isWritable')
            ->willReturn(true);

        $composite = new CompositeStream($readable, $writable);
        $composite->isReadable();
        $composite->pause();
        $composite->resume();
    }

    /** @test */
    public function itShouldNotForwardResumeIfStreamIsNotWritable()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $readable
            ->expects($this->never())
            ->method('resume');

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->exactly(2))
            ->method('isWritable')
            ->willReturnOnConsecutiveCalls(true, false);

        $composite = new CompositeStream($readable, $writable);
        $composite->resume();
    }

    /** @test */
    public function endShouldDelegateToWritableWithData()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->once())
            ->method('isWritable')
            ->willReturn(true);
        $writable
            ->expects($this->once())
            ->method('end')
            ->with('foo');

        $composite = new CompositeStream($readable, $writable);
        $composite->end('foo');
    }

    /** @test */
    public function closeShouldCloseBothStreams()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $readable
            ->expects($this->once())
            ->method('close');

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable
            ->expects($this->once())
            ->method('isWritable')
            ->willReturn(true);
        $writable
            ->expects($this->once())
            ->method('close');

        $composite = new CompositeStream($readable, $writable);
        $composite->close();
    }

    /** @test */
    public function itShouldForwardCloseOnlyOnce()
    {
        $readable = new ThroughStream();
        $writable = new ThroughStream();

        $composite = new CompositeStream($readable, $writable);
        $composite->on('close', $this->expectCallableOnce());

        $readable->close();
        $writable->close();
    }

    /** @test */
    public function itShouldForwardCloseAndRemoveAllListeners()
    {
        $in = new ThroughStream();

        $composite = new CompositeStream($in, $in);
        $composite->on('close', $this->expectCallableOnce());

        $this->assertTrue($composite->isReadable());
        $this->assertTrue($composite->isWritable());
        $this->assertCount(1, $composite->listeners('close'));

        $composite->close();

        $this->assertFalse($composite->isReadable());
        $this->assertFalse($composite->isWritable());
        $this->assertCount(0, $composite->listeners('close'));
    }

    /** @test */
    public function itShouldReceiveForwardedEvents()
    {
        $readable = new ThroughStream();
        $writable = new ThroughStream();

        $composite = new CompositeStream($readable, $writable);
        $composite->on('data', $this->expectCallableOnce());
        $composite->on('drain', $this->expectCallableOnce());

        $readable->emit('data', array('foo'));
        $writable->emit('drain');
    }

    /** @test */
    public function itShouldHandlePipingCorrectly()
    {
        $readable = $this->getMockBuilder('React\Stream\ReadableStreamInterface')->getMock();
        $readable
            ->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable->expects($this->any())->method('isWritable')->willReturn(True);
        $writable
            ->expects($this->once())
            ->method('write')
            ->with('foo');

        $composite = new CompositeStream($readable, $writable);

        $input = new ThroughStream();
        $input->pipe($composite);
        $input->emit('data', array('foo'));
    }

    /** @test */
    public function itShouldForwardPipeCallsToReadableStream()
    {
        $readable = new ThroughStream();

        $writable = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $writable->expects($this->any())->method('isWritable')->willReturn(True);

        $composite = new CompositeStream($readable, $writable);

        $output = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $output->expects($this->any())->method('isWritable')->willReturn(True);
        $output
            ->expects($this->once())
            ->method('write')
            ->with('foo');

        $composite->pipe($output);
        $readable->emit('data', array('foo'));
    }
}
