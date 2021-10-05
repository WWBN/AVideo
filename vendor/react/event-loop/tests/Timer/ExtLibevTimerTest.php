<?php

namespace React\Tests\EventLoop\Timer;

use React\EventLoop\ExtLibevLoop;

class ExtLibevTimerTest extends AbstractTimerTest
{
    public function createLoop()
    {
        if (!class_exists('libev\EventLoop')) {
            $this->markTestSkipped('libev tests skipped because ext-libev is not installed.');
        }

        return new ExtLibevLoop();
    }
}
