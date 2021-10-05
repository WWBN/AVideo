<?php

namespace React\Tests\EventLoop\Timer;

use React\EventLoop\ExtUvLoop;

class ExtUvTimerTest extends AbstractTimerTest
{
    public function createLoop()
    {
        if (!function_exists('uv_loop_new')) {
            $this->markTestSkipped('uv tests skipped because ext-uv is not installed.');
        }

        return new ExtUvLoop();
    }
}
