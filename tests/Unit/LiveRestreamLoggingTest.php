<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once \APP_ROOT . '/plugin/Live/standAloneFiles/restreamLogging.php';

/**
 * LiveRestreamLoggingTest
 *
 * Unit tests for plugin/Live/standAloneFiles/restreamLogging.php - the structured, secret-safe
 * diagnostic logging module shared by restreamer.json.php and LiveRestreamWatchdog.php.
 *
 * Every test here avoids depending on real timing/process/filesystem state where possible
 * (correlation id shape, rate-limiter behavior with a unique key, graceful degradation of
 * exec-backed helpers) so the suite stays fast and deterministic.
 *
 * Run with: vendor/bin/phpunit tests/Unit/LiveRestreamLoggingTest.php
 */
class LiveRestreamLoggingTest extends TestCase
{
    public function testNewCorrelationIdIsAHexStringAndUniquePerCall()
    {
        $a = \rl_newCorrelationId();
        $b = \rl_newCorrelationId();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $a);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $b);
        $this->assertNotSame($a, $b);
    }

    public function testLogEventNeverThrowsEvenWithUnusualFieldValues()
    {
        // rl_logEvent() must be safe to call from any lifecycle point without risking an
        // uncaught exception interrupting restream processing - including with values that are
        // awkward to json_encode (e.g. nested arrays, null, empty).
        $this->expectNotToPerformAssertions();
        \rl_logEvent('unit_test_event', array(
            'nested' => array('a' => 1, 'b' => null),
            'empty' => '',
            'zero' => 0,
        ));
    }

    public function testShouldEmitSnapshotAllowsFirstCallThenThrottlesSubsequentCallsForSameKey()
    {
        $key = 'unit-test-' . bin2hex(random_bytes(6));

        $this->assertTrue(\rl_shouldEmitSnapshot($key, 300));
        // Same key, long interval: must be throttled immediately after.
        $this->assertFalse(\rl_shouldEmitSnapshot($key, 300));
    }

    public function testShouldEmitSnapshotTreatsDifferentKeysIndependently()
    {
        $keyA = 'unit-test-a-' . bin2hex(random_bytes(6));
        $keyB = 'unit-test-b-' . bin2hex(random_bytes(6));

        $this->assertTrue(\rl_shouldEmitSnapshot($keyA, 300));
        // A different destination/key failing at the same time must still get its own snapshot,
        // not be throttled by an unrelated key's marker.
        $this->assertTrue(\rl_shouldEmitSnapshot($keyB, 300));
    }

    public function testShouldEmitSnapshotAllowsImmediateReemitWithZeroInterval()
    {
        $key = 'unit-test-zero-' . bin2hex(random_bytes(6));

        $this->assertTrue(\rl_shouldEmitSnapshot($key, 0));
        $this->assertTrue(\rl_shouldEmitSnapshot($key, 0));
    }

    public function testSafeExecReturnsNullWhenExecIsUnavailableOrUnusable()
    {
        // On Windows (this dev environment) rl_safeExec() always degrades to null by design
        // (documented as Linux/proc-only tooling) - this is exactly the graceful degradation
        // path required elsewhere on any platform where these commands are unavailable.
        if (stripos(PHP_OS, 'WIN') !== false) {
            $this->assertNull(\rl_safeExec('echo test'));
        } else {
            $this->assertNull(\rl_safeExec('a-command-that-almost-certainly-does-not-exist-12345'));
        }
    }

    public function testGetDiagnosticSnapshotNeverThrowsAndAlwaysReturnsAnArray()
    {
        // Every individual metric inside rl_getDiagnosticSnapshot() is independently guarded;
        // regardless of platform/available tooling, the call itself must never throw and must
        // always yield a snapshot array (missing sections marked unavailable, not omitted
        // silently causing a fatal elsewhere).
        $snapshot = \rl_getDiagnosticSnapshot(array('pid' => 12345, 'destinationHost' => 'example.com'));

        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('loadAverage', $snapshot);
    }

    public function testTailFileReturnsEmptyStringForMissingOrUnreadablePath()
    {
        $this->assertSame('', \rl_tailFile(''));
        $this->assertSame('', \rl_tailFile('/path/does/not/exist/at/all/' . bin2hex(random_bytes(4))));
    }

    public function testTailFileReturnsBoundedTailNotWholeFile()
    {
        $path = tempnam(sys_get_temp_dir(), 'rl_test_');
        try {
            file_put_contents($path, str_repeat('A', 100) . str_repeat('B', 50));
            $tail = \rl_tailFile($path, 50);

            $this->assertSame(50, strlen($tail));
            $this->assertSame(str_repeat('B', 50), $tail);
        } finally {
            @unlink($path);
        }
    }

    public function testContainerIdReturnsNullWhenNoCgroupEvidenceAvailable()
    {
        // On this Windows dev environment /proc/self/cgroup never exists, so this must degrade
        // to null rather than error/warn.
        if (stripos(PHP_OS, 'WIN') !== false) {
            $this->assertNull(\rl_containerId());
        } else {
            // On a real Linux host we can't assert a specific value (depends on whether the
            // test runs in a container), just that it never throws and returns null|string.
            $result = \rl_containerId();
            $this->assertTrue($result === null || is_string($result));
        }
    }
}
