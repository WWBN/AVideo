<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once \APP_ROOT . '/plugin/Live/standAloneFiles/restreamProfiles.php';

class LiveRestreamProfilesTest extends TestCase
{
    /**
     * @dataProvider providerUrls
     */
    public function testDetectsProviderFromRealIngestHost($expected, $url)
    {
        $this->assertSame($expected, \getRestreamProvider($url));
    }

    public function providerUrls()
    {
        return [
            ['youtube', 'rtmps://a.rtmps.youtube.com/live2/key'],
            ['facebook', 'rtmps://live-api-s.facebook.com:443/rtmp/key'],
            ['twitch', 'rtmp://sfo.contribute.live-video.net/app/live_key'],
            ['twitch', 'rtmp://atl.contribute.video.net/app/live_key'],
            ['twitch', 'rtmp://live.twitch.tv/app/live_key'],
            ['linkedin', 'rtmps://live-ingest.linkedin.com:443/live/live_key'],
            ['generic', 'rtmps://stream.example.com/live/key'],
            ['generic', 'rtmp://notyoutube.com/live/key'],
            ['generic', 'rtmp://unrelated.video.net/live/key'],
        ];
    }

    public function testUrlSanitizerPreservesValidProviderKeyCharacters()
    {
        $url = 'rtmp://sfo.contribute.live-video.net/app/live_user%2Bfoo+bar@example?bandwidthtest=true';
        $this->assertSame($url, \clearCommandURL($url));
    }

    public function testAutomaticYouTubeResponsePrefersRtmpsAndKeepsExactRtmpFallback()
    {
        $response = (object) [
            'stream_key' => 'abcd-efgh-ijkl-mnop',
            'stream_url' => 'rtmp://a.rtmp.youtube.com/live2',
            'rtmps_stream_url' => 'rtmps://a.rtmps.youtube.com/live2',
        ];

        $destinations = \getAutomaticRestreamDestinationPair($response);

        $this->assertSame('rtmps://a.rtmps.youtube.com/live2/abcd-efgh-ijkl-mnop', $destinations['primary']);
        $this->assertSame('rtmp://a.rtmp.youtube.com/live2/abcd-efgh-ijkl-mnop', $destinations['fallback']);
        $this->assertTrue(\shouldAttemptAutomaticRestreamFallback(
            $destinations['primary'],
            $destinations['fallback'],
            true
        ));
        $this->assertFalse(\shouldAttemptAutomaticRestreamFallback(
            $destinations['primary'],
            $destinations['fallback'],
            false
        ));
    }

    public function testLegacyAutomaticResponseRemainsCompatibleWithoutFallback()
    {
        $response = (object) [
            'stream_key' => 'legacy-key',
            'stream_url' => 'rtmp://a.rtmp.youtube.com/live2',
        ];

        $destinations = \getAutomaticRestreamDestinationPair($response);

        $this->assertSame('rtmp://a.rtmp.youtube.com/live2/legacy-key', $destinations['primary']);
        $this->assertSame('', $destinations['fallback']);
    }

    public function testInvalidSecureAutomaticUrlFallsBackToLegacyPrimary()
    {
        $response = (object) [
            'stream_key' => 'key',
            'stream_url' => 'rtmp://a.rtmp.youtube.com/live2',
            'rtmps_stream_url' => 'rtmp://a.rtmps.youtube.com/live2',
        ];

        $destinations = \getAutomaticRestreamDestinationPair($response);

        $this->assertSame('rtmp://a.rtmp.youtube.com/live2/key', $destinations['primary']);
        $this->assertSame('', $destinations['fallback']);
    }

    public function testFallbackRequiresMatchingYouTubePathAndKey()
    {
        $this->assertFalse(\shouldAttemptAutomaticRestreamFallback(
            'rtmps://a.rtmps.youtube.com/live2/secure-key',
            'rtmp://a.rtmp.youtube.com/live2/different-key',
            true
        ));
        $this->assertFalse(\shouldAttemptAutomaticRestreamFallback(
            'rtmps://stream.example.com/live/key',
            'rtmp://stream.example.com/live/key',
            true
        ));
    }

    public function testInitialConnectionSamplesRequireAStoppedOrStalledProcessBeforeFallback()
    {
        $progressing = \automaticRestreamLaunchSamplesIndicateFailure(
            ['known' => true, 'running' => true, 'modified' => 100],
            ['known' => true, 'running' => true, 'modified' => 104]
        );
        $stalled = \automaticRestreamLaunchSamplesIndicateFailure(
            ['known' => true, 'running' => true, 'modified' => 100],
            ['known' => true, 'running' => true, 'modified' => 100]
        );
        $stopped = \automaticRestreamLaunchSamplesIndicateFailure(
            ['known' => true, 'running' => true, 'modified' => 100],
            ['known' => true, 'running' => false, 'modified' => 101]
        );
        $unknown = \automaticRestreamLaunchSamplesIndicateFailure(
            ['known' => false, 'running' => false, 'modified' => 0],
            ['known' => false, 'running' => false, 'modified' => 0]
        );

        $this->assertFalse($progressing);
        $this->assertTrue($stalled);
        $this->assertTrue($stopped);
        $this->assertFalse($unknown);
    }

    /**
     * @dataProvider unsafeUrls
     */
    public function testUrlSanitizerRejectsInvalidOrShellUnsafeUrls($url)
    {
        $this->assertSame('', \clearCommandURL($url));
    }

    public function unsafeUrls()
    {
        return [
            ['ftp://example.com/live/key'],
            ['rtmp://example.com/live/$(whoami)'],
            ['rtmp://example.com/live/"key"'],
            ['rtmp://example.com/live/key with space'],
        ];
    }

    public function testFacebookTwitchAndGenericDestinationsKeepStandardFlvOutput()
    {
        foreach (array(
            'rtmps://a.rtmps.youtube.com/live2/key',
            'rtmps://live-api-s.facebook.com:443/rtmp/key',
            'rtmp://sfo.contribute.live-video.net/app/key',
            'rtmps://stream.example.com/live/key',
        ) as $url) {
            $tail = \getRestreamOutputTail($url);
            $this->assertStringContainsString('-f flv', $tail);
            $this->assertStringNotContainsString('-f tee', $tail);
        }
    }

    public function testRtmpsEnablesPeerVerificationAndRtmpDoesNotAddTlsOptions()
    {
        $secureOptions = \getRestreamTlsOptions(
            'rtmps://a.rtmps.youtube.com/live2/key',
            'rtmps://a.rtmps.youtube.com/live2/'
        );

        $this->assertStringContainsString('-tls_verify 0', $secureOptions);
        $this->assertStringContainsString('-rtmp_tcurl "rtmps://a.rtmps.youtube.com/live2/"', $secureOptions);
        $this->assertSame('', \getRestreamTlsOptions(
            'rtmp://a.rtmp.youtube.com/live2/key',
            'rtmp://a.rtmp.youtube.com/live2/'
        ));
    }

    public function testAudioProfilesMatchProviderRequirements()
    {
        $this->assertStringContainsString('-ar 44100', \getAudioConfiguration('rtmps://a.rtmps.youtube.com/live2/key'));
        $this->assertStringContainsString('-b:a 128k', \getAudioConfiguration('rtmps://live-api-s.facebook.com/rtmp/key'));
        $this->assertStringContainsString('-ar 48000', \getAudioConfiguration('rtmp://sfo.contribute.live-video.net/app/key'));
        $this->assertStringContainsString('-b:a 160k', \getAudioConfiguration('rtmp://sfo.contribute.live-video.net/app/key'));
    }

    /**
     * @dataProvider videoProfiles
     */
    public function testVideoProfilesAreTailoredToDestination($url, $resolution, $provider, $bitrateKbps, $profileName)
    {
        $profile = \getRestreamVideoProfile($url, $resolution);

        $this->assertSame($provider, $profile['provider']);
        $this->assertSame($resolution, $profile['resolution']);
        $this->assertSame($bitrateKbps, $profile['bitrateKbps']);
        $this->assertSame((int) round($bitrateKbps * 1.5), $profile['bufsizeKbps']);
        $this->assertSame($profileName, $profile['videoProfile']);
        $this->assertSame(60, $profile['gop']);
    }

    public function videoProfiles()
    {
        return [
            ['rtmps://a.rtmps.youtube.com/live2/key', 720, 'youtube', 3000, 'high'],
            ['rtmps://live-api-s.facebook.com:443/rtmp/key', 720, 'facebook', 3000, 'main'],
            ['rtmp://sfo.contribute.live-video.net/app/key', 720, 'twitch', 3000, 'high'],
            ['rtmps://stream.example.com/live/key', 720, 'generic', 2800, 'main'],
            ['rtmps://live-ingest.linkedin.com:443/live/key', 720, 'linkedin', 3000, 'baseline'],
            ['rtmps://a.rtmps.youtube.com/live2/key', 1080, 'youtube', 6000, 'high'],
            ['rtmp://sfo.contribute.live-video.net/app/key', 1080, 'twitch', 4500, 'high'],
        ];
    }

    public function testVideoConfigurationUsesStableCbrAndTwoSecondGopWithoutZeroLatencyTune()
    {
        $config = \getRestreamVideoConfiguration('rtmp://sfo.contribute.live-video.net/app/key', 720);

        $this->assertStringContainsString('-b:v 3000k', $config);
        $this->assertStringContainsString('-minrate 3000k', $config);
        $this->assertStringContainsString('-maxrate 3000k', $config);
        $this->assertStringContainsString('-g 60', $config);
        $this->assertStringContainsString('-bf 2', $config);
        $this->assertStringContainsString('-profile:v high', $config);
        $this->assertStringNotContainsString('zerolatency', $config);
    }

    public function testInvalidResolutionFallsBackTo720pGenericProfile()
    {
        $profile = \getRestreamVideoProfile('rtmps://stream.example.com/live/key', 999);

        $this->assertSame(720, $profile['resolution']);
        $this->assertSame(1280, $profile['width']);
        $this->assertSame(720, $profile['height']);
        $this->assertSame(2800, $profile['bitrateKbps']);
    }

    public function testLinkedInUsesBaselineWithoutBFramesAnd48KhzAudio()
    {
        $url = 'rtmps://live-ingest.linkedin.com:443/live/key';
        $config = \getRestreamVideoConfiguration($url, 720);
        $audio = \getAudioConfiguration($url);

        $this->assertStringContainsString('-profile:v baseline', $config);
        $this->assertStringContainsString('-bf 0', $config);
        $this->assertStringContainsString('-b:v 3000k', $config);
        $this->assertStringContainsString('-b:a 128k', $audio);
        $this->assertStringContainsString('-ar 48000', $audio);
    }

    public function testProtocolOverrideIgnoredWhenNotRtmpOrRtmps()
    {
        $destinations = array('primary' => 'rtmps://a.rtmps.youtube.com/live2/key', 'fallback' => 'rtmp://a.rtmp.youtube.com/live2/key');
        $this->assertSame($destinations, \applyRestreamProtocolTestOverride($destinations, ''));
        $this->assertSame($destinations, \applyRestreamProtocolTestOverride($destinations, 'bogus'));
    }

    public function testProtocolOverrideNoOpWhenPrimaryAlreadyMatchesRequestedProtocol()
    {
        $destinations = array('primary' => 'rtmps://a.rtmps.youtube.com/live2/key', 'fallback' => 'rtmp://a.rtmp.youtube.com/live2/key');
        $this->assertSame($destinations, \applyRestreamProtocolTestOverride($destinations, 'rtmps'));
    }

    public function testProtocolOverrideSwapsToRtmpFallbackWhenForced()
    {
        $destinations = array('primary' => 'rtmps://a.rtmps.youtube.com/live2/key', 'fallback' => 'rtmp://a.rtmp.youtube.com/live2/key');
        $result = \applyRestreamProtocolTestOverride($destinations, 'rtmp');

        $this->assertSame('rtmp://a.rtmp.youtube.com/live2/key', $result['primary']);
        $this->assertSame('', $result['fallback']);
    }

    public function testProtocolOverrideKeepsOriginalWhenNoMatchingFallbackAvailable()
    {
        $destinations = array('primary' => 'rtmp://a.rtmp.youtube.com/live2/key', 'fallback' => '');
        $result = \applyRestreamProtocolTestOverride($destinations, 'rtmps');

        $this->assertSame($destinations, $result);
    }

    public function testGetDestinationHostPortExtractsHostPortAndDefaultsPortByScheme()
    {
        $this->assertSame(
            array('protocol' => 'rtmps', 'host' => 'a.rtmps.youtube.com', 'port' => 443),
            \getDestinationHostPort('rtmps://a.rtmps.youtube.com/live2/secret-key')
        );
        $this->assertSame(
            array('protocol' => 'rtmp', 'host' => 'sfo.contribute.live-video.net', 'port' => 1935),
            \getDestinationHostPort('rtmp://sfo.contribute.live-video.net/app/secret-key')
        );
        $this->assertSame(
            array('protocol' => 'rtmp', 'host' => 'example.com', 'port' => 1942),
            \getDestinationHostPort('rtmp://example.com:1942/app/secret-key')
        );
    }

    public function testGetDestinationHostPortNeverReturnsPathOrKey()
    {
        $result = \getDestinationHostPort('rtmps://a.rtmps.youtube.com/live2/super-secret-stream-key');
        $this->assertArrayNotHasKey('path', $result);
        foreach ($result as $value) {
            $this->assertStringNotContainsString('super-secret-stream-key', (string) $value);
        }
    }

    public function testRedactSecretsInTextRedactsUrlPathButKeepsSchemeAndHost()
    {
        $command = 'ffmpeg -i "rtmp://source.example.com/live/inputkey" -c copy -f flv "rtmps://a.rtmps.youtube.com/live2/super-secret-key"';
        $redacted = \redactSecretsInText($command);

        $this->assertStringNotContainsString('super-secret-key', $redacted);
        $this->assertStringNotContainsString('inputkey', $redacted);
        // redactDestinationForLog() only ever keeps a short visible prefix of the URL (see its
        // own doc comment) - assert on that prefix + the redaction marker, not the full host.
        $this->assertStringContainsString('rtmps://a.rtmps.youtube', $redacted);
        $this->assertStringContainsString('[REDACTED,total_len=', $redacted);
        $this->assertStringContainsString('ffmpeg -i', $redacted);
    }

    public function testRedactSecretsInTextRedactsKeyValuePairsAndAuthHeaders()
    {
        $text = "token=abc123XYZ posted with Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.secret&password=hunter2";
        $redacted = \redactSecretsInText($text);

        $this->assertStringNotContainsString('abc123XYZ', $redacted);
        $this->assertStringNotContainsString('hunter2', $redacted);
        $this->assertStringNotContainsString('eyJhbGciOiJIUzI1NiJ9.secret', $redacted);
        $this->assertStringContainsString('[REDACTED]', $redacted);
    }

    public function testRedactSecretsInTextHandlesNonStringGracefully()
    {
        $this->assertSame('(non-string)', \redactSecretsInText(null));
        $this->assertSame('(non-string)', \redactSecretsInText(array('x')));
    }

    public function testRedactDestinationForLogAlwaysRedactsRegardlessOfUrlLength()
    {
        // A previous revision only appended the redaction marker when strlen($url) > 24, which
        // meant any URL 24 characters or shorter (short host, or the key living immediately
        // after the host) was returned 100% unredacted with no marker at all.
        $short = 'rtmp://a.co/k';
        $this->assertSame(13, strlen($short));
        $redacted = \redactDestinationForLog($short);

        $this->assertStringNotContainsString('/k', $redacted);
        $this->assertStringContainsString('rtmp://a.co', $redacted);
        $this->assertStringContainsString('[REDACTED,total_len=13]', $redacted);
    }

    public function testRedactDestinationForLogNeverRevealsRawPrefixOfUnrecognizedString()
    {
        // A bare secret (no recognizable scheme://host) must never fall back to showing a raw
        // prefix of the original value.
        $bareSecret = 'super-secret-stream-key-value';
        $redacted = \redactDestinationForLog($bareSecret);

        $this->assertStringNotContainsString($bareSecret, $redacted);
        $this->assertStringNotContainsString('super-secret', $redacted);
        $this->assertStringContainsString('[REDACTED,total_len=' . strlen($bareSecret) . ']', $redacted);
    }

    public function testRedactSecretsInTextRedactsExactJsonEncodedRestreamPayloadShape()
    {
        // Reproduces the exact shape Live.php's sendRestream() builds (json_encode($obj)) before
        // this fix routed the actual log line through the structural, allow-listed $summary
        // instead. json_encode() escapes "/" as "\/" and quotes every "key":"value" pair, both
        // of which defeated the original regex-only redactSecretsInText() implementation - this
        // is a defense-in-depth regression test for any OTHER caller that still passes raw JSON
        // text through this generic text redactor.
        $obj = array(
            'key' => 'SOURCE-SECRET-KEY-123',
            'm3u8' => 'https://cdn.example.com/hls/SOURCE-SECRET-KEY-123.m3u8',
            'restreamsToken' => array('3' => 'DEST-TOKEN-ABC', '5' => 'DEST-TOKEN-XYZ'),
            'restreamsDestinations' => array('3' => 'rtmps://a.rtmps.youtube.com/live2/super-secret-key'),
            'responseToken' => 'RESPONSE-TOKEN-VALUE',
            'users_id' => 42,
        );
        $json = json_encode($obj);
        // Sanity check this actually reproduces the vulnerable shape (escaped slashes present).
        $this->assertStringContainsString('\\/', $json);

        $redacted = \redactSecretsInText($json);

        $this->assertStringNotContainsString('SOURCE-SECRET-KEY-123', $redacted);
        $this->assertStringNotContainsString('DEST-TOKEN-ABC', $redacted);
        $this->assertStringNotContainsString('DEST-TOKEN-XYZ', $redacted);
        $this->assertStringNotContainsString('super-secret-key', $redacted);
        $this->assertStringNotContainsString('RESPONSE-TOKEN-VALUE', $redacted);
    }

    public function testParseFfmpegProgressLineExtractsAllFieldsFromLastMatchingLine()
    {
        $text = "frame=  100 fps= 25 q=-1.0 size=    512kB time=00:00:04.00 bitrate=1024.5kbits/s speed=1.0x drop=1 dup=2\n"
            . "frame=  200 fps= 30 q=-1.0 size=   1024kB time=00:00:08.00 bitrate=2048.0kbits/s speed=1.1x drop=3 dup=4";

        $result = \parseFfmpegProgressLine($text);

        $this->assertSame(200, $result['frame']);
        $this->assertSame(30.0, $result['fps']);
        $this->assertSame(2048.0, $result['bitrateKbps']);
        $this->assertSame('00:00:08.00', $result['time']);
        $this->assertSame(1.1, $result['speed']);
        $this->assertSame(3, $result['drop']);
        $this->assertSame(4, $result['dup']);
    }

    public function testParseFfmpegProgressLineReturnsNullWhenNoProgressLineFound()
    {
        $this->assertNull(\parseFfmpegProgressLine(''));
        $this->assertNull(\parseFfmpegProgressLine(null));
        $this->assertNull(\parseFfmpegProgressLine("Connecting to server...\nStream mapping:\n"));
    }

    /**
     * @dataProvider ffmpegFailureSamples
     */
    public function testClassifyFfmpegFailureMapsKnownStderrPatternsToTaxonomy($expected, $stderr, $context = array())
    {
        $this->assertSame($expected, \classifyFfmpegFailure($stderr, $context));
    }

    public function ffmpegFailureSamples()
    {
        return [
            'dns' => ['dns_failure', 'Could not resolve host: a.rtmps.youtube.com'],
            'tls' => ['tls_failure', 'error:1416F086:SSL routines:tls_process_server_certificate:certificate verify failed'],
            'timeout' => ['timeout', 'Connection timed out'],
            'broken_pipe' => ['output_broken_pipe', 'av_interleaved_write_frame(): Broken pipe'],
            'muxing_error' => ['output_broken_pipe', 'Error muxing a packet'],
            'trailer_error' => ['output_broken_pipe', 'Error writing trailer of rtmp://...: Broken pipe'],
            'input_failure' => ['input_failure', 'Server returned 404 Not Found for m3u8 playlist'],
            'oom_text' => ['resource_exhaustion', 'Killed process 1234 (ffmpeg)'],
            'unknown' => ['unknown', 'some unrelated ffmpeg chatter'],
            'intentional_stop_overrides_text' => ['killed_by_application', 'Broken pipe', array('intentionalStop' => true)],
            'oom_context_overrides_text' => ['resource_exhaustion', 'some unrelated chatter', array('oomEvidence' => true)],
            'dns_context_overrides_text' => ['dns_failure', 'some unrelated chatter', array('dnsFailed' => true)],
        ];
    }

    public function testComputeRestreamBackoffDelaySecondsFollowsSequenceWithoutJitter()
    {
        $sequence = array(2, 5, 10, 20, 30);

        $this->assertSame(2, \computeRestreamBackoffDelaySeconds(1, $sequence, 0));
        $this->assertSame(5, \computeRestreamBackoffDelaySeconds(2, $sequence, 0));
        $this->assertSame(10, \computeRestreamBackoffDelaySeconds(3, $sequence, 0));
        $this->assertSame(30, \computeRestreamBackoffDelaySeconds(5, $sequence, 0));
    }

    public function testComputeRestreamBackoffDelaySecondsClampsToLastSequenceValueBeyondItsLength()
    {
        $sequence = array(2, 5, 10);

        $this->assertSame(10, \computeRestreamBackoffDelaySeconds(3, $sequence, 0));
        $this->assertSame(10, \computeRestreamBackoffDelaySeconds(10, $sequence, 0));
    }

    public function testComputeRestreamBackoffDelaySecondsAppliesDeterministicInjectedJitter()
    {
        $sequence = array(10);
        // Injected random function always returns its max bound, so the result is fully
        // deterministic: base=10, jitterPercent=20% -> jitterRange=2 -> 10+2=12.
        $alwaysMax = function ($min, $max) {
            return $max;
        };
        $this->assertSame(12, \computeRestreamBackoffDelaySeconds(1, $sequence, 20, $alwaysMax));

        $alwaysMin = function ($min, $max) {
            return $min;
        };
        $this->assertSame(8, \computeRestreamBackoffDelaySeconds(1, $sequence, 20, $alwaysMin));
    }

    public function testComputeRestreamBackoffDelaySecondsNeverReturnsLessThanOne()
    {
        $sequence = array(1);
        $alwaysMin = function ($min, $max) {
            return $min;
        };
        // base=1, jitterPercent=100% -> jitterRange=1 -> could go to 0, must clamp to >= 1.
        $this->assertGreaterThanOrEqual(1, \computeRestreamBackoffDelaySeconds(1, $sequence, 100, $alwaysMin));
    }

    public function testComputeRestreamBackoffDelaySecondsFallsBackToDefaultSequenceWhenEmpty()
    {
        $this->assertSame(2, \computeRestreamBackoffDelaySeconds(1, array(), 0));
        $this->assertSame(30, \computeRestreamBackoffDelaySeconds(99, array(), 0));
    }

    /**
     * Regression test: the FIFO output-recovery layer's default max_recovery_attempts must give
     * FFmpeg a realistic (~60s) internal recovery window before giving up and exiting the
     * process, so that a transient destination hiccup is absorbed by FIFO alone in the common
     * case, without ever needing LiveRestreamWatchdog's own (much slower) detect+restart cycle.
     */
    public function testMaxRecoveryAttemptsDefaultsToThirtyForARoughlySixtySecondRecoveryWindow()
    {
        $bounds = \getRestreamFifoConfigBounds();
        $this->assertSame(30, $bounds['maxRecoveryAttempts']['default']);
        $this->assertSame(2, $bounds['recoveryWaitTime']['default']);
    }

    public function testIsValidRecoveryRestreamRequestAllowsAnyValueWhenNotRecoveryMode()
    {
        $this->assertTrue(\isValidRecoveryRestreamRequest(0, false));
        $this->assertTrue(\isValidRecoveryRestreamRequest(null, false));
        $this->assertTrue(\isValidRecoveryRestreamRequest(5, false));
    }

    public function testIsValidRecoveryRestreamRequestRequiresASpecificDestinationWhenRecoveryMode()
    {
        // The hard invariant: an automated recovery must never be allowed to fall back to
        // "(re)start every destination" - only Live::restream()'s $live_restreams_id=0 broad
        // behavior is refused, never a specific existing destination id.
        $this->assertFalse(\isValidRecoveryRestreamRequest(0, true));
        $this->assertFalse(\isValidRecoveryRestreamRequest(null, true));
        $this->assertFalse(\isValidRecoveryRestreamRequest('', true));
        $this->assertTrue(\isValidRecoveryRestreamRequest(5, true));
        $this->assertTrue(\isValidRecoveryRestreamRequest('5', true));
    }
}
