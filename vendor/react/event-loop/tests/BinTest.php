<?php

namespace React\Tests\EventLoop;

class BinTest extends TestCase
{
    /**
     * @before
     */
    public function setUpBin()
    {
        if (!defined('PHP_BINARY') || defined('HHVM_VERSION')) {
            $this->markTestSkipped('Tests not supported on legacy PHP 5.3 or HHVM');
        }

        chdir(__DIR__ . '/bin/');
    }

    public function testExecuteExampleWithoutLoopRunRunsLoopAndExecutesTicks()
    {
        $output = exec(escapeshellarg(PHP_BINARY) . ' 01-ticks-loop-class.php');

        $this->assertEquals('abc', $output);
    }

    public function testExecuteExampleWithExplicitLoopRunRunsLoopAndExecutesTicks()
    {
        $output = exec(escapeshellarg(PHP_BINARY) . ' 02-ticks-loop-instance.php');

        $this->assertEquals('abc', $output);
    }

    public function testExecuteExampleWithExplicitLoopRunAndStopRunsLoopAndExecutesTicksUntilStopped()
    {
        $output = exec(escapeshellarg(PHP_BINARY) . ' 03-ticks-loop-stop.php');

        $this->assertEquals('abc', $output);
    }

    public function testExecuteExampleWithUncaughtExceptionShouldNotRunLoop()
    {
        $time = microtime(true);
        exec(escapeshellarg(PHP_BINARY) . ' 11-uncaught.php 2>/dev/null');
        $time = microtime(true) - $time;

        $this->assertLessThan(1.0, $time);
    }

    public function testExecuteExampleWithUndefinedVariableShouldNotRunLoop()
    {
        $time = microtime(true);
        exec(escapeshellarg(PHP_BINARY) . ' 12-undefined.php 2>/dev/null');
        $time = microtime(true) - $time;

        $this->assertLessThan(1.0, $time);
    }

    public function testExecuteExampleWithExplicitStopShouldNotRunLoop()
    {
        $time = microtime(true);
        exec(escapeshellarg(PHP_BINARY) . ' 21-stop.php 2>/dev/null');
        $time = microtime(true) - $time;

        $this->assertLessThan(1.0, $time);
    }

    public function testExecuteExampleWithExplicitStopInExceptionHandlerShouldNotRunLoop()
    {
        $time = microtime(true);
        exec(escapeshellarg(PHP_BINARY) . ' 22-uncaught-stop.php 2>/dev/null');
        $time = microtime(true) - $time;

        $this->assertLessThan(1.0, $time);
    }
}
