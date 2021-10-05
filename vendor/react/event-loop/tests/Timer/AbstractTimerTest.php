<?php

namespace React\Tests\EventLoop\Timer;

use React\EventLoop\LoopInterface;
use React\Tests\EventLoop\TestCase;

abstract class AbstractTimerTest extends TestCase
{
    /**
     * @return LoopInterface
     */
    abstract public function createLoop();

    public function testAddTimerReturnsNonPeriodicTimerInstance()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(0.001, $this->expectCallableNever());

        $this->assertInstanceOf('React\EventLoop\TimerInterface', $timer);
        $this->assertFalse($timer->isPeriodic());
    }

    public function testAddTimerWillBeInvokedOnceAndBlocksLoopWhenRunning()
    {
        $loop = $this->createLoop();

        $loop->addTimer(0.005, $this->expectCallableOnce());

        $start = microtime(true);
        $loop->run();
        $end = microtime(true);

        // 1 invocation should take 5ms (± a few milliseconds due to timer inaccuracies)
        // make no strict assumptions about time interval, must at least take 1ms
        // and should not take longer than 0.1s for slower loops.
        $this->assertGreaterThanOrEqual(0.001, $end - $start);
        $this->assertLessThan(0.1, $end - $start);
    }

    public function testAddPeriodicTimerReturnsPeriodicTimerInstance()
    {
        $loop = $this->createLoop();

        $periodic = $loop->addPeriodicTimer(0.1, $this->expectCallableNever());

        $this->assertInstanceOf('React\EventLoop\TimerInterface', $periodic);
        $this->assertTrue($periodic->isPeriodic());
    }

    public function testAddPeriodicTimerWillBeInvokedUntilItIsCancelled()
    {
        $loop = $this->createLoop();

        $periodic = $loop->addPeriodicTimer(0.1, $this->expectCallableExactly(3));

        // make no strict assumptions about actual time interval.
        // leave some room to ensure this ticks exactly 3 times.
        $loop->addTimer(0.350, function () use ($loop, $periodic) {
            $loop->cancelTimer($periodic);
        });

        $loop->run();
    }

    public function testAddPeriodicTimerWillBeInvokedWithMaximumAccuracyUntilItIsCancelled()
    {
        $loop = $this->createLoop();

        $i = 0;
        $periodic = $loop->addPeriodicTimer(0.001, function () use (&$i) {
            ++$i;
        });

        $loop->addTimer(0.1, function () use ($loop, $periodic) {
            $loop->cancelTimer($periodic);
        });

        $loop->run();

        // make no strict assumptions about number of invocations.
        // we know it must be no more than 100 times and should at least be
        // invoked 4 times for really slow loops
        $this->assertLessThanOrEqual(100, $i);
        $this->assertGreaterThanOrEqual(4, $i);
    }

    public function testAddPeriodicTimerCancelsItself()
    {
        $loop = $this->createLoop();

        $i = 0;
        $loop->addPeriodicTimer(0.001, function ($timer) use (&$i, $loop) {
            $i++;

            if ($i === 5) {
                $loop->cancelTimer($timer);
            }
        });

        $start = microtime(true);
        $loop->run();
        $end = microtime(true);

        $this->assertEquals(5, $i);

        // 5 invocations should take 5ms (± 1ms due to timer inaccuracies)
        // make no strict assumptions about time interval, must at least take 4ms
        // and should not take longer than 0.2s for slower loops.
        $this->assertGreaterThanOrEqual(0.004, $end - $start);
        $this->assertLessThan(0.2, $end - $start);
    }

    public function testMinimumIntervalOneMicrosecond()
    {
        $loop = $this->createLoop();

        $timer = $loop->addTimer(0, function () {});

        $this->assertEquals(0.000001, $timer->getInterval());
    }

    public function testTimerIntervalBelowZeroRunsImmediately()
    {
        $loop = $this->createLoop();
        $start = 0;
        $loop->addTimer(
            -1,
            function () use (&$start) {
                $start = \microtime(true);
            }
        );

        $loop->run();
        $end = \microtime(true);

        // 1ms should be enough even on slow machines (± 1ms due to timer inaccuracies)
        $this->assertLessThan(0.002, $end - $start);
    }
}
