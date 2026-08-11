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

    public function testYouTubeUsesIsolatedFifoAndMapsOnlyOneVideoAndAudioTrack()
    {
        $tail = \getRestreamOutputTail('rtmps://a.rtmps.youtube.com/live2/key');
        $this->assertStringContainsString('-f tee', $tail);
        $this->assertStringContainsString('-map 0:v:0?', $tail);
        $this->assertStringContainsString('-map 0:a:0?', $tail);
        $this->assertStringNotContainsString('-map 0 ', $tail);
        $this->assertStringContainsString('[f=fifo:fifo_format=flv:', $tail);
        $this->assertStringContainsString('flvflags=no_duration_filesize', $tail);
        $this->assertStringContainsString('onfail=abort', $tail);
    }

    public function testFacebookTwitchAndGenericDestinationsKeepStandardFlvOutput()
    {
        foreach (array(
            'rtmps://live-api-s.facebook.com:443/rtmp/key',
            'rtmp://sfo.contribute.live-video.net/app/key',
            'rtmps://stream.example.com/live/key',
        ) as $url) {
            $tail = \getRestreamOutputTail($url);
            $this->assertStringContainsString('-f flv', $tail);
            $this->assertStringNotContainsString('-f tee', $tail);
        }
    }

    public function testAudioProfilesMatchProviderRequirements()
    {
        $this->assertStringContainsString('-ar 44100', \getAudioConfiguration('rtmps://a.rtmps.youtube.com/live2/key'));
        $this->assertStringContainsString('-b:a 128k', \getAudioConfiguration('rtmps://live-api-s.facebook.com/rtmp/key'));
        $this->assertStringContainsString('-ar 48000', \getAudioConfiguration('rtmp://sfo.contribute.live-video.net/app/key'));
        $this->assertStringContainsString('-b:a 160k', \getAudioConfiguration('rtmp://sfo.contribute.live-video.net/app/key'));
    }
}
