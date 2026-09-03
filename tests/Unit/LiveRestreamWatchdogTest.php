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

    /**
     * Tests for the backoff-sequence config parsing added alongside exponential backoff wiring:
     * getBackoffSequence() must never return an empty array (computeRestreamBackoffDelaySeconds()
     * has no well-defined attempt-1 delay for an empty sequence from the caller's perspective),
     * and must gracefully fall back to the built-in default on missing/malformed config.
     *
     * @test
     */
    public function testGetBackoffSequenceParsesCommaSeparatedConfigIntoIntArray()
    {
        $objLive = (object) ['restreamWatchdogBackoffSequenceSeconds' => '3,7,15'];
        $this->assertSame([3, 7, 15], $this->invokePrivateMethod($this->watchdog, 'getBackoffSequence', [$objLive]));
    }

    /**
     * @test
     */
    public function testGetBackoffSequenceFallsBackToDefaultWhenConfigMissingOrEmpty()
    {
        $this->assertSame([2, 5, 10, 20, 30], $this->invokePrivateMethod($this->watchdog, 'getBackoffSequence', [null]));

        $objLive = (object) ['restreamWatchdogBackoffSequenceSeconds' => ''];
        $this->assertSame([2, 5, 10, 20, 30], $this->invokePrivateMethod($this->watchdog, 'getBackoffSequence', [$objLive]));
    }

    /**
     * @test
     */
    public function testGetBackoffSequenceIgnoresNonPositiveOrMalformedEntries()
    {
        $objLive = (object) ['restreamWatchdogBackoffSequenceSeconds' => '3,0,-5,abc,7'];
        $this->assertSame([3, 7], $this->invokePrivateMethod($this->watchdog, 'getBackoffSequence', [$objLive]));
    }

    /**
     * @test
     */
    public function testGetConfigStringReturnsDefaultWhenMissingOrEmpty()
    {
        $this->assertSame('default', $this->invokePrivateMethod($this->watchdog, 'getConfigString', [null, 'x', 'default']));

        $objLive = (object) ['x' => ''];
        $this->assertSame('default', $this->invokePrivateMethod($this->watchdog, 'getConfigString', [$objLive, 'x', 'default']));

        $objLive = (object) ['x' => 'configured-value'];
        $this->assertSame('configured-value', $this->invokePrivateMethod($this->watchdog, 'getConfigString', [$objLive, 'x', 'default']));
    }

    // -----------------------------------------------------------------------------------------
    // Regression tests for the "restream healthy for 600s, resetting restart attempt counter"
    // bug: healthy_since must be tied to the OBSERVED PROCESS's identity (pid), never merely to
    // "the last time this function saw anything running" - otherwise a brand new process
    // (restarted by the watchdog itself, or externally) that happens to be observed within the
    // same window as a much older healthy_since silently inherits it, letting the restart-attempt
    // counter reset to zero long before restreamWatchdogHealthyResetSeconds of REAL uptime has
    // actually elapsed for that process.
    // -----------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function testIsProcessConsideredRunningTrueWhenLocalProcessFound()
    {
        $localProcess = ['pid' => 123, 'line' => 'irrelevant'];
        $status = (object) ['isActive' => false];
        $this->assertTrue($this->invokePrivateMethod($this->watchdog, 'isProcessConsideredRunning', [$localProcess, $status]));
    }

    /**
     * @test
     */
    public function testIsProcessConsideredRunningTrueWhenRemoteStatusActiveWithNoLocalProcess()
    {
        // No local ps match (e.g. the restreamer runs on a different host) but the remote status
        // reports isActive=true - this is also the exact shape observed while FIFO's own
        // output-recovery layer is internally reconnecting: the FFmpeg process (local or remote)
        // never exits during that window, so this must resolve to "running", never "not running".
        $status = (object) ['isActive' => true];
        $this->assertTrue($this->invokePrivateMethod($this->watchdog, 'isProcessConsideredRunning', [false, $status]));
    }

    /**
     * @test
     */
    public function testIsProcessConsideredRunningFalseWhenNeitherSignalConfirmsIt()
    {
        $status = (object) ['isActive' => false];
        $this->assertFalse($this->invokePrivateMethod($this->watchdog, 'isProcessConsideredRunning', [false, $status]));
        $this->assertFalse($this->invokePrivateMethod($this->watchdog, 'isProcessConsideredRunning', [false, false]));
    }

    /**
     * @test
     */
    public function testComputeHealthySinceOnObservationStartsFreshOnFirstObservation()
    {
        $now = 1000000;
        $result = $this->invokePrivateMethod($this->watchdog, 'computeHealthySinceOnObservation', [null, null, 555, null, $now]);
        $this->assertSame($now, $result);
    }

    /**
     * @test
     */
    public function testComputeHealthySinceOnObservationPrefersRealProcessStartTimeOnFirstObservation()
    {
        $now = 1000000;
        $startedAt = 999950;
        $result = $this->invokePrivateMethod($this->watchdog, 'computeHealthySinceOnObservation', [null, null, 555, $startedAt, $now]);
        $this->assertSame($startedAt, $result);
    }

    /**
     * @test
     */
    public function testComputeHealthySinceOnObservationKeepsExistingValueWhenSamePidStillRunning()
    {
        $previousHealthySince = 900000;
        $now = 1000000;
        $result = $this->invokePrivateMethod($this->watchdog, 'computeHealthySinceOnObservation', [$previousHealthySince, 555, 555, null, $now]);
        $this->assertSame($previousHealthySince, $result);
    }

    /**
     * @test
     */
    public function testComputeHealthySinceOnObservationResetsWhenPidChanges()
    {
        // This is the exact regression scenario: a stale healthy_since from a previous (now dead)
        // process must never survive once a DIFFERENT pid is observed - it must reset to the new
        // process's own start time, not silently keep counting from the old process's uptime.
        $staleHealthySince = 100; // a very old value, would already be past any healthy-reset window
        $now = 1000000;
        $newPidStartedAt = 999990;
        $result = $this->invokePrivateMethod(
            $this->watchdog,
            'computeHealthySinceOnObservation',
            [$staleHealthySince, 555, 777, $newPidStartedAt, $now]
        );
        $this->assertSame($newPidStartedAt, $result);
        $this->assertNotSame($staleHealthySince, $result);
    }

    /**
     * @test
     */
    public function testComputeHealthySinceOnObservationFallsBackToNowWhenPidChangedButStartTimeUnknown()
    {
        $now = 1000000;
        $result = $this->invokePrivateMethod(
            $this->watchdog,
            'computeHealthySinceOnObservation',
            [100, 555, 777, null, $now]
        );
        $this->assertSame($now, $result);
    }

    /**
     * @test
     */
    public function testComputeRestreamPhaseReturnsBlockedWhenMaxAttemptsReachedRegardlessOfOtherState()
    {
        $state = ['pending_validation' => true];
        $this->assertSame('blocked', $this->invokePrivateMethod($this->watchdog, 'computeRestreamPhase', [$state, true, true]));
    }

    /**
     * @test
     */
    public function testComputeRestreamPhaseReturnsRestartingWhenPendingValidation()
    {
        $state = ['pending_validation' => true];
        $this->assertSame('restarting', $this->invokePrivateMethod($this->watchdog, 'computeRestreamPhase', [$state, false, false]));
    }

    /**
     * @test
     */
    public function testComputeRestreamPhaseReturnsHealthyWhenProcessRunningAndNotPendingValidation()
    {
        $state = ['pending_validation' => false];
        $this->assertSame('healthy', $this->invokePrivateMethod($this->watchdog, 'computeRestreamPhase', [$state, true, false]));
    }

    /**
     * @test
     */
    public function testComputeRestreamPhaseReturnsDownAsFallback()
    {
        $state = ['pending_validation' => false];
        $this->assertSame('down', $this->invokePrivateMethod($this->watchdog, 'computeRestreamPhase', [$state, false, false]));
    }

    // -----------------------------------------------------------------------------------------
    // mergeFailureObservationIntoState() - models the exact race the lock-ordering fix prevents:
    // callers MUST pass in a freshly-under-lock-re-read state, and only the failure-observation
    // fields get overwritten; any attempt-bookkeeping fields another (overlapping, earlier)
    // locked cycle already wrote must survive untouched.
    // -----------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function testMergeFailureObservationIntoStatePreservesAttemptBookkeepingFromFreshState()
    {
        $freshState = [
            'restart_attempts' => [100, 200, 300],
            'last_restart_attempt_at' => 300,
            'last_known_pid' => 4321,
            'pending_validation' => true,
            'healthy_since' => 250,
            'last_failure_at' => 90,
            'last_failure_reason' => 'stale reason from an earlier cycle',
        ];

        $result = $this->invokePrivateMethod(
            $this->watchdog,
            'mergeFailureObservationIntoState',
            [$freshState, 500, 'Broken pipe']
        );

        // Attempt bookkeeping written by a concurrent, already-locked cycle must survive.
        $this->assertSame([100, 200, 300], $result['restart_attempts']);
        $this->assertSame(300, $result['last_restart_attempt_at']);
        $this->assertSame(4321, $result['last_known_pid']);

        // Failure-observation fields must be overwritten with the newly observed values.
        $this->assertNull($result['healthy_since']);
        $this->assertSame(500, $result['last_failure_at']);
        $this->assertSame('Broken pipe', $result['last_failure_reason']);
        $this->assertFalse($result['pending_validation']);
    }

    /**
     * @test
     */
    public function testMergeFailureObservationIntoStateWorksOnEmptyFreshState()
    {
        $result = $this->invokePrivateMethod(
            $this->watchdog,
            'mergeFailureObservationIntoState',
            [[], 111, 'Error muxing a packet']
        );

        $this->assertNull($result['healthy_since']);
        $this->assertSame(111, $result['last_failure_at']);
        $this->assertSame('Error muxing a packet', $result['last_failure_reason']);
        $this->assertFalse($result['pending_validation']);
    }

    // -----------------------------------------------------------------------------------------
    // Pure /proc/[pid]/stat parsing helpers (getProcessStartUnixTime() itself does real file I/O
    // and is Linux-only; these two pieces are split out specifically so they stay unit-testable
    // on any platform, including this Windows dev environment).
    // -----------------------------------------------------------------------------------------

    /**
     * @test
     */
    public function testParseProcStatStartTicksReadsField22AfterTheLastCloseParen()
    {
        // Minimal realistic /proc/[pid]/stat line: pid (comm) state ppid pgrp session tty_nr
        // tpgid flags minflt cminflt majflt cmajflt utime stime cutime cstime priority nice
        // num_threads itrealvalue starttime ...
        // Fields after ')': state=R ppid=1 pgrp=100 session=100 tty_nr=0 tpgid=-1 flags=4194560
        // minflt=100 cminflt=0 majflt=0 cmajflt=0 utime=1 stime=1 cutime=0 cstime=0 priority=20
        // nice=0 num_threads=1 itrealvalue=0 starttime=123456
        $stat = '4321 (ffmpeg -re -i) R 1 100 100 0 -1 4194560 100 0 0 0 1 1 0 0 20 0 1 0 123456 0 0';
        $this->assertSame(123456, $this->invokePrivateMethod($this->watchdog, 'parseProcStatStartTicks', [$stat]));
    }

    /**
     * @test
     */
    public function testParseProcStatStartTicksHandlesParenthesesInsideTheCommandName()
    {
        // The process name field can itself contain '(' and ')' (e.g. a renamed/wrapped command);
        // parsing must anchor on the LAST ')' in the line, not the first.
        $stat = '4321 (ffmpeg (restream)) S 1 100 100 0 -1 4194560 100 0 0 0 1 1 0 0 20 0 1 0 999 0 0';
        $this->assertSame(999, $this->invokePrivateMethod($this->watchdog, 'parseProcStatStartTicks', [$stat]));
    }

    /**
     * @test
     */
    public function testParseProcStatStartTicksReturnsNullForMalformedContent()
    {
        $this->assertNull($this->invokePrivateMethod($this->watchdog, 'parseProcStatStartTicks', ['']));
        $this->assertNull($this->invokePrivateMethod($this->watchdog, 'parseProcStatStartTicks', ['no parens here']));
        $this->assertNull($this->invokePrivateMethod($this->watchdog, 'parseProcStatStartTicks', ['4321 (ffmpeg) R']));
    }

    /**
     * @test
     */
    public function testComputeProcessStartUnixTimeFromTicksConvertsTicksSinceBootToUnixTime()
    {
        // Boot was 500 real seconds ago; the process started 100 ticks (at 100 ticks/sec = 1
        // second) after boot, i.e. 499 seconds ago.
        $now = 1000000;
        $uptimeSeconds = 500.0;
        $startTicks = 100;
        $result = $this->invokePrivateMethod(
            $this->watchdog,
            'computeProcessStartUnixTimeFromTicks',
            [$now, $uptimeSeconds, $startTicks, 100]
        );
        $this->assertSame($now - 499, $result);
    }

    /**
     * @test
     */
    public function testComputeProcessStartUnixTimeFromTicksDefaultsToOneHundredTicksPerSecondWhenInvalid()
    {
        $now = 1000000;
        $withDefault = $this->invokePrivateMethod($this->watchdog, 'computeProcessStartUnixTimeFromTicks', [$now, 500.0, 100, 100]);
        $withInvalidTicksPerSecond = $this->invokePrivateMethod($this->watchdog, 'computeProcessStartUnixTimeFromTicks', [$now, 500.0, 100, 0]);
        $this->assertSame($withDefault, $withInvalidTicksPerSecond);
    }

    /**
     * @test
     */
    public function testGetProcessStartUnixTimeReturnsNullOnWindows()
    {
        if (stripos(PHP_OS, 'WIN') === false) {
            $this->markTestSkipped('This assertion only applies on Windows; getProcessStartUnixTime() is Linux/proc-only by design.');
        }
        $this->assertNull($this->invokePrivateMethod($this->watchdog, 'getProcessStartUnixTime', [12345]));
    }

    /**
     * @test
     */
    public function testGetProcessStartUnixTimeReturnsNullForEmptyPid()
    {
        $this->assertNull($this->invokePrivateMethod($this->watchdog, 'getProcessStartUnixTime', [null]));
        $this->assertNull($this->invokePrivateMethod($this->watchdog, 'getProcessStartUnixTime', [0]));
    }
}
