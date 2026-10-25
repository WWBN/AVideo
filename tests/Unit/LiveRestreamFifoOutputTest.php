<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once \APP_ROOT . '/plugin/Live/standAloneFiles/restreamProfiles.php';
require_once \APP_ROOT . '/plugin/Live/standAloneFiles/ffmpegCapabilities.php';

/**
 * FIFO output recovery layer. The shapes below were validated end-to-end with FFmpeg 6.1.1
 * (the production build) against an adaptive 3-variant HLS source and RTMP/RTMPS destinations
 * that were dropped and brought back mid-stream.
 */
class LiveRestreamFifoOutputTest extends TestCase
{
    private function supportedCapabilities()
    {
        return array(
            'available' => true, 'hasFifoMuxer' => true, 'hasFlvMuxer' => true,
            'hasRtmpProtocol' => true, 'hasRtmpsProtocol' => true, 'tlsBackends' => array('openssl'),
            'fifoMuxerOptions' => array_fill_keys(array(
                'fifo_format', 'format_opts', 'queue_size', 'drop_pkts_on_overflow',
                'attempt_recovery', 'recovery_wait_time', 'recovery_wait_streamtime',
                'recover_any_error', 'restart_with_keyframe', 'max_recovery_attempts',
            ), true),
        );
    }

    /** @dataProvider destinationEligibility */
    public function testDefaultAllowListKeepsOtherProvidersOnTheirLegacyOutput($url, $expected)
    {
        $config = \sanitizeRestreamFifoConfig(array('enabled' => true));
        $this->assertSame($expected, \shouldUseRestreamFifoForDestination($url, $config, $this->supportedCapabilities()));
    }

    public function destinationEligibility()
    {
        return array(
            array('rtmps://a.rtmps.youtube.com/live2/test', true),
            array('rtmp://a.rtmp.youtube.com/live2/test', true),
            array('rtmps://live-api-s.facebook.com:443/rtmp/test', false),
            array('rtmp://live.twitch.tv/app/test', false),
            array('rtmps://live-ingest.linkedin.com/live/test', false),
            array('rtmp://stream.example.test/live/test', false),
        );
    }

    public function testDisabledOrUnsupportedFifoFallsBackToLegacyOutput()
    {
        $url = 'rtmps://a.rtmps.youtube.com/live2/test';
        $config = \sanitizeRestreamFifoConfig(array('enabled' => true));
        $this->assertFalse(\shouldUseRestreamFifoForDestination($url, $config, null));
        $caps = $this->supportedCapabilities();
        unset($caps['fifoMuxerOptions']['restart_with_keyframe']);
        $this->assertFalse(\shouldUseRestreamFifoForDestination($url, $config, $caps));
        $caps = $this->supportedCapabilities();
        $caps['tlsBackends'] = array();
        $this->assertFalse(\shouldUseRestreamFifoForDestination($url, $config, $caps));
        $config['enabled'] = false;
        $this->assertFalse(\shouldUseRestreamFifoForDestination($url, $config, $this->supportedCapabilities()));
    }

    public function testFacebookRequiresExplicitAllowListOptIn()
    {
        $config = \sanitizeRestreamFifoConfig(array('enabled' => true, 'allowedProviders' => 'youtube,facebook'));
        $this->assertTrue(\shouldUseRestreamFifoForDestination(
            'rtmps://live-api-s.facebook.com:443/rtmp/test', $config, $this->supportedCapabilities()
        ));
    }

    private function adaptiveHlsProbe()
    {
        // ffprobe -show_entries stream=index,codec_type,width,height,channels of an AVideo
        // adaptive master playlist: low/mid/hi programs, each with its own audio.
        return array('streams' => array(
            array('index' => 0, 'codec_type' => 'video', 'width' => 854, 'height' => 480),
            array('index' => 1, 'codec_type' => 'audio', 'channels' => 1),
            array('index' => 2, 'codec_type' => 'video', 'width' => 1280, 'height' => 720),
            array('index' => 3, 'codec_type' => 'audio', 'channels' => 1),
            array('index' => 4, 'codec_type' => 'video', 'width' => 1920, 'height' => 1080),
            array('index' => 5, 'codec_type' => 'audio', 'channels' => 2),
        ));
    }

    public function testStreamMapPicksLargestVideoAndAudioWithMostChannelsLikeFfmpegDefaultSelection()
    {
        $this->assertSame('-map 0:4 -map 0:5', \getRestreamFifoStreamMapFromProbe($this->adaptiveHlsProbe()));
    }

    public function testStreamMapTiesKeepLowestIndexAndAudioIsOptional()
    {
        $probe = array('streams' => array(
            array('index' => 0, 'codec_type' => 'video', 'width' => 1280, 'height' => 720),
            array('index' => 1, 'codec_type' => 'audio', 'channels' => 2),
            array('index' => 2, 'codec_type' => 'video', 'width' => 1280, 'height' => 720),
            array('index' => 3, 'codec_type' => 'audio', 'channels' => 2),
        ));
        $this->assertSame('-map 0:0 -map 0:1', \getRestreamFifoStreamMapFromProbe($probe));

        $videoOnly = array('streams' => array(array('index' => 0, 'codec_type' => 'video', 'width' => 640, 'height' => 360)));
        $this->assertSame('-map 0:0', \getRestreamFifoStreamMapFromProbe($videoOnly));
    }

    public function testStreamMapIgnoresAttachedPicturesAndRejectsUnusableProbes()
    {
        $probe = array('streams' => array(
            array('index' => 0, 'codec_type' => 'video', 'width' => 3000, 'height' => 3000, 'disposition' => array('attached_pic' => 1)),
            array('index' => 1, 'codec_type' => 'video', 'width' => 1280, 'height' => 720),
            array('index' => 2, 'codec_type' => 'audio', 'channels' => 2),
        ));
        $this->assertSame('-map 0:1 -map 0:2', \getRestreamFifoStreamMapFromProbe($probe));
        $this->assertSame('', \getRestreamFifoStreamMapFromProbe(null));
        $this->assertSame('', \getRestreamFifoStreamMapFromProbe(array('streams' => array(array('index' => 0, 'codec_type' => 'audio', 'channels' => 2)))));
        $this->assertSame('', \getRestreamFifoStreamMapFromProbe(array('streams' => array(array('codec_type' => 'video', 'width' => 1, 'height' => 1)))));
    }

    public function testStreamMapPreservesDefaultTracksLikeTheLegacyFlvOutput()
    {
        $probe = array('streams' => array(
            array('index' => 0, 'codec_type' => 'video', 'width' => 320, 'height' => 180, 'disposition' => array('default' => 1)),
            array('index' => 1, 'codec_type' => 'video', 'width' => 640, 'height' => 360),
            array('index' => 2, 'codec_type' => 'audio', 'channels' => 1, 'disposition' => array('default' => 1)),
            array('index' => 3, 'codec_type' => 'audio', 'channels' => 2),
        ));

        $this->assertSame('-map 0:0 -map 0:2', \getRestreamFifoStreamMapFromProbe($probe));
    }

    public function testStreamMapBreaksTiesByIndexEvenWhenProbeRowsAreUnordered()
    {
        $probe = array('streams' => array(
            array('index' => 4, 'codec_type' => 'video', 'width' => 1280, 'height' => 720),
            array('index' => 5, 'codec_type' => 'audio', 'channels' => 2),
            array('index' => 0, 'codec_type' => 'video', 'width' => 1280, 'height' => 720),
            array('index' => 1, 'codec_type' => 'audio', 'channels' => 2),
        ));

        $this->assertSame('-map 0:0 -map 0:1', \getRestreamFifoStreamMapFromProbe($probe));
    }

    public function testFifoTailUsesExplicitMapAndGlobalHeaders()
    {
        $config = \sanitizeRestreamFifoConfig(array('enabled' => true));
        $dst = 'rtmps://a.rtmps.youtube.com/live2/key';
        $tail = \getRestreamFifoOutputTail($dst, 'rtmps://a.rtmps.youtube.com/live2/', $config, '-map 0:4 -map 0:5');

        $this->assertStringContainsString(' -map 0:4 -map 0:5 -flags +global_header -f fifo -fifo_format flv', $tail);
        $this->assertStringNotContainsString('-map 0 ', $tail);
        $this->assertStringContainsString('-attempt_recovery 1', $tail);
        $this->assertStringContainsString('tls_verify=0:rtmp_tcurl=rtmps\\://a.rtmps.youtube.com/live2/', $tail);
        $this->assertStringEndsWith('"' . $dst . '"', $tail);
    }

    public function testFifoTailRefusesMissingOrUnsafeMapSoTheCallerKeepsTheLegacyOutput()
    {
        $config = \sanitizeRestreamFifoConfig(array('enabled' => true));
        $dst = 'rtmps://a.rtmps.youtube.com/live2/key';
        foreach (array('', '-map 0', '-map 0:4; rm -rf /', '-map 0:a -map 0:v', ' -map 0:4 -map 0:5 -map 0:1') as $map) {
            $this->assertSame('', \getRestreamFifoOutputTail($dst, 'rtmps://a.rtmps.youtube.com/live2/', $config, $map), "map: {$map}");
        }
    }
}
