<?php

namespace React\Tests\EventLoop;

use React\EventLoop\ExtEvLoop;

class ExtEvLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        if (!class_exists('EvLoop')) {
            $this->markTestSkipped('ExtEvLoop tests skipped because ext-ev extension is not installed.');
        }

        return new ExtEvLoop();
    }
}
