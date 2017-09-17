<?php

namespace React\Tests\Promise\Timer;

use React\Promise\Timer;

class FunctionRejectTest extends TestCase
{
    public function testPromiseIsPendingWithoutRunningLoop()
    {
        $promise = Timer\reject(0.01, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseExpiredIsPendingWithoutRunningLoop()
    {
        $promise = Timer\reject(-1, $this->loop);

        $this->expectPromisePending($promise);
    }

    public function testPromiseWillBeRejectedOnTimeout()
    {
        $promise = Timer\reject(0.01, $this->loop);

        $this->loop->run();

        $this->expectPromiseRejected($promise);
    }

    public function testPromiseExpiredWillBeRejectedOnTimeout()
    {
        $promise = Timer\reject(-1, $this->loop);

        $this->loop->run();

        $this->expectPromiseRejected($promise);
    }

    public function testCancelingPromiseWillRejectTimer()
    {
        $promise = Timer\reject(0.01, $this->loop);

        $promise->cancel();

        $this->expectPromiseRejected($promise);
    }
}
