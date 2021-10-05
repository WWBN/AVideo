<?php

namespace React\Tests\EventLoop;

use React\EventLoop\Factory;
use React\EventLoop\Loop;
use ReflectionClass;

final class LoopTest extends TestCase
{
    /**
     * @dataProvider numberOfTests
     */
    public function testFactoryCreateSetsEventLoopOnLoopAccessor()
    {
        $factoryLoop = Factory::create();
        $accessorLoop = Loop::get();

        self::assertSame($factoryLoop, $accessorLoop);
    }

    /**
     * @dataProvider numberOfTests
     */
    public function testCallingFactoryAfterCallingLoopGetYieldsADifferentInstanceOfTheEventLoop()
    {
        // Note that this behavior isn't wise and highly advised against. Always used Loop::get.
        $accessorLoop = Loop::get();
        $factoryLoop = Factory::create();

        self::assertNotSame($factoryLoop, $accessorLoop);
    }

    /**
     * @dataProvider numberOfTests
     */
    public function testCallingLoopGetShouldAlwaysReturnTheSameEventLoop()
    {
        self::assertSame(Loop::get(), Loop::get());
    }

    /**
     * Run several tests several times to ensure we reset the loop between tests and code is still behavior as expected.
     *
     * @return array<array>
     */
    public function numberOfTests()
    {
        return array(array(), array(), array());
    }

    public function testStaticAddReadStreamCallsAddReadStreamOnLoopInstance()
    {
        $stream = tmpfile();
        $listener = function () { };

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addReadStream')->with($stream, $listener);

        Loop::set($loop);

        Loop::addReadStream($stream, $listener);
    }

    public function testStaticAddWriteStreamCallsAddWriteStreamOnLoopInstance()
    {
        $stream = tmpfile();
        $listener = function () { };

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addWriteStream')->with($stream, $listener);

        Loop::set($loop);

        Loop::addWriteStream($stream, $listener);
    }

    public function testStaticRemoveReadStreamCallsRemoveReadStreamOnLoopInstance()
    {
        $stream = tmpfile();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('removeReadStream')->with($stream);

        Loop::set($loop);

        Loop::removeReadStream($stream);
    }

    public function testStaticRemoveWriteStreamCallsRemoveWriteStreamOnLoopInstance()
    {
        $stream = tmpfile();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('removeWriteStream')->with($stream);

        Loop::set($loop);

        Loop::removeWriteStream($stream);
    }

    public function testStaticAddTimerCallsAddTimerOnLoopInstanceAndReturnsTimerInstance()
    {
        $interval = 1.0;
        $callback = function () { };
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with($interval, $callback)->willReturn($timer);

        Loop::set($loop);

        $ret = Loop::addTimer($interval, $callback);

        $this->assertSame($timer, $ret);
    }

    public function testStaticAddPeriodicTimerCallsAddPeriodicTimerOnLoopInstanceAndReturnsTimerInstance()
    {
        $interval = 1.0;
        $callback = function () { };
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addPeriodicTimer')->with($interval, $callback)->willReturn($timer);

        Loop::set($loop);

        $ret = Loop::addPeriodicTimer($interval, $callback);

        $this->assertSame($timer, $ret);
    }

    public function testStaticCancelTimerCallsCancelTimerOnLoopInstance()
    {
        $timer = $this->getMockBuilder('React\EventLoop\TimerInterface')->getMock();

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('cancelTimer')->with($timer);

        Loop::set($loop);

        Loop::cancelTimer($timer);
    }

    public function testStaticFutureTickCallsFutureTickOnLoopInstance()
    {
        $listener = function () { };

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('futureTick')->with($listener);

        Loop::set($loop);

        Loop::futureTick($listener);
    }

    public function testStaticAddSignalCallsAddSignalOnLoopInstance()
    {
        $signal = 1;
        $listener = function () { };

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addSignal')->with($signal, $listener);

        Loop::set($loop);

        Loop::addSignal($signal, $listener);
    }

    public function testStaticRemoveSignalCallsRemoveSignalOnLoopInstance()
    {
        $signal = 1;
        $listener = function () { };

        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('removeSignal')->with($signal, $listener);

        Loop::set($loop);

        Loop::removeSignal($signal, $listener);
    }

    public function testStaticRunCallsRunOnLoopInstance()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('run')->with();

        Loop::set($loop);

        Loop::run();
    }

    public function testStaticStopCallsStopOnLoopInstance()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('stop')->with();

        Loop::set($loop);

        Loop::stop();
    }

    /**
     * @after
     * @before
     */
    public function unsetLoopFromLoopAccessor()
    {
        $ref = new ReflectionClass('\React\EventLoop\Loop');
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null);
        $prop->setAccessible(false);
    }
}
