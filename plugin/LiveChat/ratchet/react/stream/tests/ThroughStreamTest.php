<?php

namespace React\Tests\Stream;

use React\Stream\ThroughStream;

/**
 * @covers React\Stream\ThroughStream
 */
class ThroughStreamTest extends TestCase
{
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function itShouldRejectInvalidCallback()
    {
        new ThroughStream(123);
    }

    /** @test */
    public function itShouldReturnTrueForAnyDataWrittenToIt()
    {
        $through = new ThroughStream();
        $ret = $through->write('foo');

        $this->assertTrue($ret);
    }

    /** @test */
    public function itShouldEmitAnyDataWrittenToIt()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnceWith('foo'));
        $through->write('foo');
    }

    /** @test */
    public function itShouldEmitAnyDataWrittenToItPassedThruFunction()
    {
        $through = new ThroughStream('strtoupper');
        $through->on('data', $this->expectCallableOnceWith('FOO'));
        $through->write('foo');
    }

    /** @test */
    public function itShouldEmitAnyDataWrittenToItPassedThruCallback()
    {
        $through = new ThroughStream('strtoupper');
        $through->on('data', $this->expectCallableOnceWith('FOO'));
        $through->write('foo');
    }

    /** @test */
    public function itShouldEmitErrorAndCloseIfCallbackThrowsException()
    {
        $through = new ThroughStream(function () {
            throw new \RuntimeException();
        });
        $through->on('error', $this->expectCallableOnce());
        $through->on('close', $this->expectCallableOnce());
        $through->on('data', $this->expectCallableNever());
        $through->on('end', $this->expectCallableNever());

        $through->write('foo');

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function itShouldEmitErrorAndCloseIfCallbackThrowsExceptionOnEnd()
    {
        $through = new ThroughStream(function () {
            throw new \RuntimeException();
        });
        $through->on('error', $this->expectCallableOnce());
        $through->on('close', $this->expectCallableOnce());
        $through->on('data', $this->expectCallableNever());
        $through->on('end', $this->expectCallableNever());

        $through->end('foo');

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function itShouldReturnFalseForAnyDataWrittenToItWhenPaused()
    {
        $through = new ThroughStream();
        $through->pause();
        $ret = $through->write('foo');

        $this->assertFalse($ret);
    }

    /** @test */
    public function itShouldEmitDrainOnResumeAfterReturnFalseForAnyDataWrittenToItWhenPaused()
    {
        $through = new ThroughStream();
        $through->pause();
        $through->write('foo');

        $through->on('drain', $this->expectCallableOnce());
        $through->resume();
    }

    /** @test */
    public function itShouldReturnTrueForAnyDataWrittenToItWhenResumedAfterPause()
    {
        $through = new ThroughStream();
        $through->on('drain', $this->expectCallableNever());
        $through->pause();
        $through->resume();
        $ret = $through->write('foo');

        $this->assertTrue($ret);
    }

    /** @test */
    public function pipingStuffIntoItShouldWork()
    {
        $readable = new ThroughStream();

        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnceWith('foo'));

        $readable->pipe($through);
        $readable->emit('data', array('foo'));
    }

    /** @test */
    public function endShouldEmitEndAndClose()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableNever());
        $through->on('end', $this->expectCallableOnce());
        $through->on('close', $this->expectCallableOnce());
        $through->end();
    }

    /** @test */
    public function endShouldCloseTheStream()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableNever());
        $through->end();

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function endShouldWriteDataBeforeClosing()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnceWith('foo'));
        $through->end('foo');

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function endTwiceShouldOnlyEmitOnce()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableOnce('first'));
        $through->end('first');
        $through->end('ignored');
    }

    /** @test */
    public function writeAfterEndShouldReturnFalse()
    {
        $through = new ThroughStream();
        $through->on('data', $this->expectCallableNever());
        $through->end();

        $this->assertFalse($through->write('foo'));
    }

    /** @test */
    public function writeDataWillCloseStreamShouldReturnFalse()
    {
        $through = new ThroughStream();
        $through->on('data', array($through, 'close'));

        $this->assertFalse($through->write('foo'));
    }

    /** @test */
    public function writeDataToPausedShouldReturnFalse()
    {
        $through = new ThroughStream();
        $through->pause();

        $this->assertFalse($through->write('foo'));
    }

    /** @test */
    public function writeDataToResumedShouldReturnTrue()
    {
        $through = new ThroughStream();
        $through->pause();
        $through->resume();

        $this->assertTrue($through->write('foo'));
    }

    /** @test */
    public function itShouldBeReadableByDefault()
    {
        $through = new ThroughStream();
        $this->assertTrue($through->isReadable());
    }

    /** @test */
    public function itShouldBeWritableByDefault()
    {
        $through = new ThroughStream();
        $this->assertTrue($through->isWritable());
    }

    /** @test */
    public function closeShouldCloseOnce()
    {
        $through = new ThroughStream();

        $through->on('close', $this->expectCallableOnce());

        $through->close();

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function doubleCloseShouldCloseOnce()
    {
        $through = new ThroughStream();

        $through->on('close', $this->expectCallableOnce());

        $through->close();
        $through->close();

        $this->assertFalse($through->isReadable());
        $this->assertFalse($through->isWritable());
    }

    /** @test */
    public function pipeShouldPipeCorrectly()
    {
        $output = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();
        $output->expects($this->any())->method('isWritable')->willReturn(True);
        $output
            ->expects($this->once())
            ->method('write')
            ->with('foo');

        $through = new ThroughStream();
        $through->pipe($output);
        $through->write('foo');
    }
}
