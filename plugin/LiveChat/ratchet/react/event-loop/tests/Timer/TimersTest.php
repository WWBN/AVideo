<?php

namespace React\Tests\EventLoop\Timer;

use React\Tests\EventLoop\TestCase;
use React\EventLoop\Timer\Timer;
use React\EventLoop\Timer\Timers;

class TimersTest extends TestCase
{
    public function testBlockedTimer()
    {
        $loop = $this
            ->getMockBuilder('React\EventLoop\LoopInterface')
            ->getMock();
        
        $timers = new Timers();
        $timers->tick();
        
        // simulate a bunch of processing on stream events,
        // part of which schedules a future timer...
        sleep(1);
        $timers->add(new Timer($loop, 0.5, function () {
            $this->fail("Timer shouldn't be called");
        }));

        $timers->tick();
    }
}
