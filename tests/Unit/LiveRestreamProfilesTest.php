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
}
