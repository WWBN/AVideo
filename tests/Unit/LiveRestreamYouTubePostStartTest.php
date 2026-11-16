<?php

namespace Tests\Unit\YouTubePostStart;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/plugin/Live/standAloneFiles/restreamProfiles.php';

class Fixture
{
    public static $status;
    public static $key;
    public static $checks;
    public static $keys;
    public static $stops;
    public static $sleeps;
    public static $stopped;
}
function sleep($seconds) { Fixture::$sleeps++; }
function file_exists($file) { return Fixture::$stopped; }
function getYouTubeBroadcastStatusAfterStart($token, $history, $id) { Fixture::$checks[] = [$token, $history, $id]; return Fixture::$status; }
function _getLiveKey($token, $history, $type) { Fixture::$keys[] = [$token, $history, $type]; return Fixture::$key; }
function stopAutomaticRestreamLaunch($launch, $robj) { Fixture::$stops++; return true; }
function error_log($message) {}
const AUTOMATIC_RESTREAM_INITIAL_WARMUP_SECONDS = 8;

class LiveRestreamYouTubePostStartTest extends TestCase
{
    private $savedSeparate;

    protected function setUp(): void
    {
        $this->savedSeparate = $GLOBALS['separateRestreams'] ?? null;
        $GLOBALS['separateRestreams'] = true;
        Fixture::$checks = Fixture::$keys = [];
        Fixture::$stops = Fixture::$sleeps = 0;
        Fixture::$stopped = false;
        Fixture::$status = (object) ['error' => false, 'broadcast_id' => 'oldVideo123', 'broadcast_status' => 'complete'];
        Fixture::$key = (object) ['error' => false, 'broadcast_id' => 'newVideo123', 'newRestreamsDestination' => 'rtmps://example.test/new', 'fallbackRestreamsDestination' => 'rtmp://example.test/new', 'live_url' => 'https://example.test/new'];
        if (!\function_exists(__NAMESPACE__ . '\\validateReusedYouTubeBroadcastsAfterStart')) {
            $postStartSource = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__, 2) . '/plugin/Live/standAloneFiles/restreamer.json.php'));
            preg_match('/function validateReusedYouTubeBroadcastsAfterStart\(.*?\n}\n/s', $postStartSource, $postStartMatch);
            $this->assertNotEmpty($postStartMatch);
            eval('namespace ' . __NAMESPACE__ . ';' . $postStartMatch[0]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->savedSeparate === null) { unset($GLOBALS['separateRestreams']); }
        else { $GLOBALS['separateRestreams'] = $this->savedSeparate; }
    }

    private function request()
    {
        return (object) ['live_restreams_id' => 7, 'liveTransmitionHistory_id' => 123,
            'youtubeBroadcastIdsToValidate' => [7 => 'oldVideo123'], 'restreamsToken' => [7 => 'FAKE_TOKEN'],
            'restreamsDestinations' => [7 => 'rtmps://example.test/old', 8 => 'rtmp://other.test/live'],
            'restreamsFallbackDestinations' => [], 'live_url' => []];
    }

    private function validate($request)
    {
        return validateReusedYouTubeBroadcastsAfterStart((object) ['logFile' => '/fixture/relay.log'], $request, $request->restreamsDestinations);
    }

    public function testConfirmedCompletionReplacesOnlyThatDestinationOnce(): void
    {
        $request = $this->request();
        $this->assertSame('retry', $this->validate($request));
        // The relay feeding the ended broadcast is stopped before the restart: a remote FFmpeg
        // executor refuses a new launch while the same keyword is still running.
        $this->assertSame(1, Fixture::$stops);
        $this->assertSame('rtmps://example.test/new', $request->restreamsDestinations[7]);
        $this->assertSame('rtmp://other.test/live', $request->restreamsDestinations[8]);
        $this->assertSame('https://example.test/new', $request->live_url[7]);
        $this->assertSame('continue', $this->validate($request));
        $this->assertSame(1, Fixture::$stops);
        $this->assertCount(1, Fixture::$checks);
        $this->assertCount(1, Fixture::$keys);
        $this->assertSame(1, Fixture::$sleeps);
    }

    public function testHealthyUnknownErrorAndMismatchedAnswersNeverRestart(): void
    {
        foreach (['live', 'ready', 'missing', 'unknown', null] as $status) {
            Fixture::$status->broadcast_status = $status;
            $this->assertSame('continue', $this->validate($this->request()));
        }
        Fixture::$status->broadcast_status = 'complete';
        Fixture::$status->error = true;
        $this->assertSame('continue', $this->validate($this->request()));
        Fixture::$status->error = false;
        Fixture::$status->broadcast_id = 'otherVideo123';
        $this->assertSame('continue', $this->validate($this->request()));
        Fixture::$status = null;
        $this->assertSame('continue', $this->validate($this->request()));
        $this->assertSame([], Fixture::$keys);
        $this->assertSame(0, Fixture::$stops);
    }

    public function testFailedReplacementStopsAnEndedRelayForTheWatchdog(): void
    {
        Fixture::$key->error = true;
        $this->assertSame('failed', $this->validate($this->request()));
        $this->assertSame(1, Fixture::$stops);
    }

    public function testManualStopAndLegacyResponsesDoNotTriggerValidation(): void
    {
        Fixture::$stopped = true;
        $this->assertSame('continue', $this->validate($this->request()));
        $this->assertSame([], Fixture::$checks);
        $request = $this->request();
        $request->youtubeBroadcastIdsToValidate = [];
        $this->assertSame('continue', $this->validate($request));
        $this->assertSame([], Fixture::$keys);
    }

    public function testAllInOneModePreservesOtherDestinationKeys(): void
    {
        $GLOBALS['separateRestreams'] = false;
        $request = $this->request();
        $request->live_restreams_id = 0;
        $this->assertSame('retry', $this->validate($request));
        $this->assertSame([7, 8], array_keys($request->restreamsDestinations));
        $this->assertSame('rtmp://other.test/live', $request->restreamsDestinations[8]);
    }

    public function testOnlyBothCapabilitiesAndAnActualReuseEnableValidation(): void
    {
        $answer = (object) ['error' => false, 'broadcast_id' => 'oldVideo123', 'stream_key' => 'FAKE_KEY', 'reused_broadcast' => true, 'broadcast_status_check_supported' => true];
        $this->assertSame('', \getYouTubeBroadcastIdToValidate($answer)); // Older Streamer echoes broker's marker only.
        $answer->broadcast_validation_supported = true;
        $this->assertSame('oldVideo123', \getYouTubeBroadcastIdToValidate($answer));
        $answer->reused_broadcast = false;
        $this->assertSame('', \getYouTubeBroadcastIdToValidate($answer));
    }
}
