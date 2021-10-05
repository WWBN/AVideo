<?php

namespace React\Tests\EventLoop;

use React\EventLoop\SignalsHandler;

final class SignalsHandlerTest extends TestCase
{
    /**
     * @requires extension pcntl
     */
    public function testEmittedEventsAndCallHandling()
    {
        $callCount = 0;
        $func = function () use (&$callCount) {
            $callCount++;
        };
        $signals = new SignalsHandler();

        $this->assertSame(0, $callCount);

        $signals->add(SIGUSR1, $func);
        $this->assertSame(0, $callCount);

        $signals->add(SIGUSR1, $func);
        $this->assertSame(0, $callCount);

        $signals->add(SIGUSR1, $func);
        $this->assertSame(0, $callCount);

        $signals->call(SIGUSR1);
        $this->assertSame(1, $callCount);

        $signals->add(SIGUSR2, $func);
        $this->assertSame(1, $callCount);

        $signals->add(SIGUSR2, $func);
        $this->assertSame(1, $callCount);

        $signals->call(SIGUSR2);
        $this->assertSame(2, $callCount);

        $signals->remove(SIGUSR2, $func);
        $this->assertSame(2, $callCount);

        $signals->remove(SIGUSR2, $func);
        $this->assertSame(2, $callCount);

        $signals->call(SIGUSR2);
        $this->assertSame(2, $callCount);

        $signals->remove(SIGUSR1, $func);
        $this->assertSame(2, $callCount);

        $signals->call(SIGUSR1);
        $this->assertSame(2, $callCount);
    }
}
