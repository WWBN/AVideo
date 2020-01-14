<?php

namespace React\Tests\Promise\Timer;

use React\Promise\Timer;

class FunctionResolveTest extends TestCase
{
    public function testPromiseIsPendingWithoutRunningLoop()
    {
        $promise = Timer\resolve(0.01, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseExpiredIsPendingWithoutRunningLoop()
    {
        $promise = Timer\resolve(-1, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseWillBeResolvedOnTimeout()
    {
        $promise = Timer\resolve(0.01, $this->loop);

        $this->loop->run();

        $this->expectPromiseResolved($promise);
    }

    public function testPromiseExpiredWillBeResolvedOnTimeout()
    {
        $promise = Timer\resolve(-1, $this->loop);

        $this->loop->run();

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

        $timer = $this->getMockBuilder('React\EventLoop\Timer\TimerInterface')->getMock();
        $loop->expects($this->once())->method('addTimer')->will($this->returnValue($timer));

        $promise = Timer\resolve(0.01, $loop);

        $loop->expects($this->once())->method('cancelTimer')->with($this->equalTo($timer));

        $promise->cancel();
    }

    public function testCancelingPromiseWillRejectTimer()
    {
        $promise = Timer\resolve(0.01, $this->loop);

        $promise->cancel();

        $this->expectPromiseRejected($promise);
    }
}
