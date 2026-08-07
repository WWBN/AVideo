<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * LiveRestreamWatchdogTest
 *
 * Focused unit tests for the pure/deterministic logic pieces of
 * plugin/Live/Objects/LiveRestreamWatchdog.php (no DB/network access), covering the bug fixed in
 * this review: restart attempts must be pruned to the configured time window before the
 * max-attempts/cooldown checks are (re-)evaluated, including when re-checked a second time after
 * acquiring the restart lock.
 *
 * Run with: vendor/bin/phpunit tests/Unit/LiveRestreamWatchdogTest.php
 */
class LiveRestreamWatchdogTest extends TestCase
{
    /**
     * @var \LiveRestreamWatchdog
     */
    private $watchdog;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists('LiveRestreamWatchdog')) {
            require_once \APP_ROOT . '/plugin/Live/Objects/LiveRestreamWatchdog.php';
        }

        $this->watchdog = new \LiveRestreamWatchdog();
    }

    /**
     * @test
     */
    public function testPruneAttemptsKeepsOnlyTimestampsWithinWindow()
    {
        $now = 1000000;
        $windowSeconds = 900;
        $attempts = [
            $now - 1000, // outside window, must be dropped
            $now - 900,  // exactly at the boundary, must be kept
            $now - 500,  // inside window, must be kept
            $now,        // now, must be kept
        ];

        $result = $this->invokePrivateMethod($this->watchdog, 'pruneAttempts', [$attempts, $windowSeconds, $now]);

        $this->assertEquals([$now - 900, $now - 500, $now], array_values($result));
    }

    /**
     * @test
     */
    public function testPruneAttemptsReturnsEmptyArrayForEmptyOrInvalidInput()
    {
        $this->assertEquals([], $this->invokePrivateMethod($this->watchdog, 'pruneAttempts', [[], 900, time()]));
        $this->assertEquals([], $this->invokePrivateMethod($this->watchdog, 'pruneAttempts', [null, 900, time()]));
    }

    /**
     * @test
     */
    public function testPruneAttemptsIsIdempotentWhenAppliedTwice()
    {
        // Regression test for the confirmed race condition: attemptRestart() re-reads state and
        // must be able to re-apply pruneAttempts()+max-attempts safely a second time (after the
        // caller already pruned once) without losing or duplicating any still-valid attempt.
        $now = 2000000;
        $windowSeconds = 900;
        $attempts = [$now - 800, $now - 100];

        $firstPass = $this->invokePrivateMethod($this->watchdog, 'pruneAttempts', [$attempts, $windowSeconds, $now]);
        $secondPass = $this->invokePrivateMethod($this->watchdog, 'pruneAttempts', [$firstPass, $windowSeconds, $now]);

        $this->assertEquals($firstPass, $secondPass);
        $this->assertCount(2, $secondPass);
    }

    /**
     * @test
     */
    public function testClassifyFailureDetectsKnownDestinationErrors()
    {
        $brokenPipe = (object) ['content' => "frame=  120 fps=30\nav_interleaved_write_frame(): Broken pipe\n"];
        $this->assertEquals('Broken pipe', $this->invokePrivateMethod($this->watchdog, 'classifyFailure', [$brokenPipe]));

        $muxError = (object) ['content' => "Error muxing a packet\n"];
        $this->assertEquals('Error muxing a packet', $this->invokePrivateMethod($this->watchdog, 'classifyFailure', [$muxError]));

        $trailerError = (object) ['content' => "Error writing trailer of file\n"];
        $this->assertEquals('Error writing trailer', $this->invokePrivateMethod($this->watchdog, 'classifyFailure', [$trailerError]));
    }

    /**
     * @test
     */
    public function testClassifyFailureFallsBackToGenericReasonWhenContentIsUnrecognizedOrEmpty()
    {
        $unknown = (object) ['content' => 'some unrelated log line'];
        $this->assertEquals(
            'FFmpeg process not running (unexpected termination)',
            $this->invokePrivateMethod($this->watchdog, 'classifyFailure', [$unknown])
        );

        $empty = (object) ['content' => ''];
        $this->assertEquals(
            'FFmpeg process not running (unexpected termination)',
            $this->invokePrivateMethod($this->watchdog, 'classifyFailure', [$empty])
        );
    }

    /**
     * Regression tests for the post-lock recheck race condition: attemptRestart() must never
     * restart (nor count an attempt) when the recheck cannot actually confirm the restream is
     * down, and must never override an explicit manual stop.
     *
     * @test
     */
    public function testEvaluateRecheckedProcessStateIsInconclusiveWhenBothChecksFail()
    {
        // fetchLogStatus() failed (timeout/network/malformed response) AND no local process was
        // found either: nothing confirms the restream is actually down, so it must not restart.
        $this->assertEquals(
            'inconclusive',
            $this->invokePrivateMethod($this->watchdog, 'evaluateRecheckedProcessState', [false, false])
        );
    }

    /**
     * @test
     */
    public function testEvaluateRecheckedProcessStateDetectsCompletedDuringLockedRecheck()
    {
        // A manual stop can land between the first check and acquiring the lock; completed=true
        // must always win over isActive, even if isActive happens to still read false.
        $status = (object) ['completed' => true, 'isActive' => false];
        $this->assertEquals(
            'completed',
            $this->invokePrivateMethod($this->watchdog, 'evaluateRecheckedProcessState', [false, $status])
        );
    }

    /**
     * @test
     */
    public function testEvaluateRecheckedProcessStateAllowsRestartWhenGenuinelyDown()
    {
        // No local process, valid status, not completed, not active: a real restart is warranted.
        $status = (object) ['completed' => false, 'isActive' => false];
        $this->assertEquals(
            'still_down',
            $this->invokePrivateMethod($this->watchdog, 'evaluateRecheckedProcessState', [false, $status])
        );
    }

    /**
     * @test
     */
    public function testEvaluateRecheckedProcessStateSkipsRestartWhenActive()
    {
        $status = (object) ['completed' => false, 'isActive' => true];
        $this->assertEquals(
            'running',
            $this->invokePrivateMethod($this->watchdog, 'evaluateRecheckedProcessState', [false, $status])
        );

        // A locally found process alone is enough to confirm "running", regardless of status.
        $this->assertEquals(
            'running',
            $this->invokePrivateMethod($this->watchdog, 'evaluateRecheckedProcessState', [['pid' => 123], false])
        );
    }
}
