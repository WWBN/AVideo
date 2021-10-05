<?php

namespace React\Tests\EventLoop\Timer;

use React\Tests\EventLoop\TestCase;
use React\EventLoop\Timer\Timer;
use React\EventLoop\Timer\Timers;

class TimersTest extends TestCase
{
    public function testBlockedTimer()
    {
        $timers = new Timers();
        $timers->tick();

        // simulate a bunch of processing on stream events,
        // part of which schedules a future timer...
        sleep(1);
        $timers->add(new Timer(0.5, function () {
            $this->fail("Timer shouldn't be called");
        }));

        $timers->tick();

        $this->assertTrue(true);
    }

    public function testContains()
    {
        $timers = new Timers();

        $timer1 = new Timer(0.1, function () {});
        $timer2 = new Timer(0.1, function () {});

        $timers->add($timer1);

        self::assertTrue($timers->contains($timer1));
        self::assertFalse($timers->contains($timer2));
    }
}
