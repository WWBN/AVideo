<?php

namespace React\Tests\EventLoop;

use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\EventLoop\Timer\Timer;

class StreamSelectLoopTest extends AbstractLoopTest
{
    protected function tearDown()
    {
        parent::tearDown();
        if (strncmp($this->getName(false), 'testSignal', 10) === 0 && extension_loaded('pcntl')) {
            $this->resetSignalHandlers();
        }
    }

    public function createLoop()
    {
        return new StreamSelectLoop();
    }

    public function testStreamSelectTimeoutEmulation()
    {
        $this->loop->addTimer(
            0.05,
            $this->expectCallableOnce()
        );

        $start = microtime(true);

        $this->loop->run();

        $end = microtime(true);
        $interval = $end - $start;

        $this->assertGreaterThan(0.04, $interval);
    }

    public function signalProvider()
    {
        return [
            ['SIGUSR1'],
            ['SIGHUP'],
            ['SIGTERM'],
        ];
    }

    private $_signalHandled = false;

    /**
     * Test signal interrupt when no stream is attached to the loop
     * @dataProvider signalProvider
     */
    public function testSignalInterruptNoStream($signal)
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('"pcntl" extension is required to run this test.');
        }

        // dispatch signal handler once before signal is sent and once after
        $this->loop->addTimer(0.01, function() { pcntl_signal_dispatch(); });
        $this->loop->addTimer(0.03, function() { pcntl_signal_dispatch(); });
        if (defined('HHVM_VERSION')) {
            // hhvm startup is slow so we need to add another handler much later
            $this->loop->addTimer(0.5, function() { pcntl_signal_dispatch(); });
        }

        $this->setUpSignalHandler($signal);

        // spawn external process to send signal to current process id
        $this->forkSendSignal($signal);
        $this->loop->run();
        $this->assertTrue($this->_signalHandled);
    }

    /**
     * Test signal interrupt when a stream is attached to the loop
     * @dataProvider signalProvider
     */
    public function testSignalInterruptWithStream($signal)
    {
        if (!extension_loaded('pcntl')) {
            $this->markTestSkipped('"pcntl" extension is required to run this test.');
        }

        // dispatch signal handler every 10ms
        $this->loop->addPeriodicTimer(0.01, function() { pcntl_signal_dispatch(); });

        // add stream to the loop
        list($writeStream, $readStream) = $this->createSocketPair();
        $this->loop->addReadStream($readStream, function($stream, $loop) {
            /** @var $loop LoopInterface */
            $read = fgets($stream);
            if ($read === "end loop\n") {
                $loop->stop();
            }
        });
        $this->loop->addTimer(0.05, function() use ($writeStream) {
            fwrite($writeStream, "end loop\n");
        });

        $this->setUpSignalHandler($signal);

        // spawn external process to send signal to current process id
        $this->forkSendSignal($signal);

        $this->loop->run();

        $this->assertTrue($this->_signalHandled);
    }

    /**
     * add signal handler for signal
     */
    protected function setUpSignalHandler($signal)
    {
        $this->_signalHandled = false;
        $this->assertTrue(pcntl_signal(constant($signal), function() { $this->_signalHandled = true; }));
    }

    /**
     * reset all signal handlers to default
     */
    protected function resetSignalHandlers()
    {
        foreach($this->signalProvider() as $signal) {
            pcntl_signal(constant($signal[0]), SIG_DFL);
        }
    }

    /**
     * fork child process to send signal to current process id
     */
    protected function forkSendSignal($signal)
    {
        $currentPid = posix_getpid();
        $childPid = pcntl_fork();
        if ($childPid == -1) {
            $this->fail("Failed to fork child process!");
        } else if ($childPid === 0) {
            // this is executed in the child process
            usleep(20000);
            posix_kill($currentPid, constant($signal));
            die();
        }
    }

    /**
     * https://github.com/reactphp/event-loop/issues/48
     *
     * Tests that timer with very small interval uses at least 1 microsecond
     * timeout.
     */
    public function testSmallTimerInterval()
    {
        /** @var StreamSelectLoop|\PHPUnit_Framework_MockObject_MockObject $loop */
        $loop = $this->getMock('React\EventLoop\StreamSelectLoop', ['streamSelect']);
        $loop
            ->expects($this->at(0))
            ->method('streamSelect')
            ->with([], [], 1);
        $loop
            ->expects($this->at(1))
            ->method('streamSelect')
            ->with([], [], 0);

        $callsCount = 0;
        $loop->addPeriodicTimer(Timer::MIN_INTERVAL, function() use (&$loop, &$callsCount) {
            $callsCount++;
            if ($callsCount == 2) {
                $loop->stop();
            }
        });

        $loop->run();
    }
}
