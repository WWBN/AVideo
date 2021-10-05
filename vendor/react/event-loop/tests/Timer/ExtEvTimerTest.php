<?php

namespace React\Tests\EventLoop\Timer;

use React\EventLoop\ExtEvLoop;

class ExtEvTimerTest extends AbstractTimerTest
{
    public function createLoop()
    {
        if (!class_exists('EvLoop')) {
            $this->markTestSkipped('ExtEvLoop tests skipped because ext-ev extension is not installed.');
        }

        return new ExtEvLoop();
    }
}
