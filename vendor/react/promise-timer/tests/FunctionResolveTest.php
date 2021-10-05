<?php

namespace React\Tests\Promise\Timer;

use React\EventLoop\Loop;
use React\Promise\Timer;

class FunctionResolveTest extends TestCase
{
    public function testPromiseIsPendingWithoutRunningLoop()
    {
        $promise = Timer\resolve(0.01);

        $this->expectPromisePending($promise);
    }

    public function testPromiseExpiredIsPendingWithoutRunningLoop()
    {
        $promise = Timer\resolve(-1);

        $this->expectPromisePending($promise);
    }

    public function testPromiseWillBeResolvedOnTimeout()
    {
        $promise = Timer\resolve(0.01);

        Loop::run();

        $this->expectPromiseResolved($promise);
    }

    public function testPromiseExpiredWillBeResolvedOnTimeout()
    {
        $promise = Timer\resolve(-1);

        Loop::run();

        $this->expectPromiseResolved($promise);
    }

    public function testWillStartLoopTimer()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->with($this->equalTo(0.01));

        Timer\resolve(0.01, $loop);
    }

    public function testCancellingPromiseWillCancelLoopTimer()
    {
        $loop = $this->getMockBuilder('React\EventLoop\LoopInterface')->getMock();

        $timer = $this->getMockBuilder(interface_exists('React\EventLoop\TimerInterface') ? 'React\EventLoop\TimerInterface' : 'React\EventLoop\Timer\TimerInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->will($this->returnValue($timer));

        $promise = Timer\resolve(0.01, $loop);

        $loop->expects($this->once())->method('cancelTimer')->with($this->equalTo($timer));

        $promise->cancel();
    }

    public function testCancellingPromiseWillRejectTimer()
    {
        $promise = Timer\resolve(0.01);

        $promise->cancel();

        $this->expectPromiseRejected($promise);
    }

    public function testWaitingForPromiseToResolveDoesNotLeaveGarbageCycles()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $promise = Timer\resolve(0.01);
        Loop::run();
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }

    public function testCancellingPromiseDoesNotLeaveGarbageCycles()
    {
        if (class_exists('React\Promise\When')) {
            $this->markTestSkipped('Not supported on legacy Promise v1 API');
        }

        gc_collect_cycles();

        $promise = Timer\resolve(0.01);
        $promise->cancel();
        unset($promise);

        $this->assertEquals(0, gc_collect_cycles());
    }
}
