<?php

namespace React\Tests\EventLoop;

use React\EventLoop\ExtLibevLoop;

class ExtLibevLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        if (!class_exists('libev\EventLoop')) {
            $this->markTestSkipped('libev tests skipped because ext-libev is not installed.');
        }

        return new ExtLibevLoop();
    }

    public function testLibEvConstructor()
    {
        $loop = new ExtLibevLoop();
    }
}
