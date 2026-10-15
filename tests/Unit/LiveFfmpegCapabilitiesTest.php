<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once \APP_ROOT . '/plugin/Live/standAloneFiles/ffmpegCapabilities.php';

class LiveFfmpegCapabilitiesTest extends TestCase
{
    /**
     * Option lines copied from real `ffmpeg -hide_banner -h full` output.
     *
     * @dataProvider providerHelpOutput
     */
    public function testCfrSyncOptionFollowsTheOptionsThisBuildAccepts($expected, $helpOutput)
    {
        $this->assertSame($expected, \ffmpeg_cfrSyncOption($helpOutput));
    }

    public function providerHelpOutput()
    {
        return [
            // FFmpeg 5.0 (2022 build): only -vsync
            ['-vsync cfr', "-vsync              video sync method\n-async              audio sync method\n"],
            // FFmpeg 6.1: both; keep -vsync so working hosts get the same command as before
            ['-vsync cfr', "-vsync              set video sync method globally; deprecated, use -fps_mode\n"
                . "-fps_mode           set framerate mode for matching video streams; overrides vsync\n"],
            // FFmpeg 9.0: -vsync was removed
            ['-fps_mode cfr', "-fps_mode[:<stream_spec>]  set framerate mode for matching video streams\n"],
        ];
    }

    public function testCfrSyncOptionIgnoresOptionNamesInsideDescriptions()
    {
        $help = "-fps_mode[:<stream_spec>]  set framerate mode; replaces the removed -vsync option\n";
        $this->assertSame('-fps_mode cfr', \ffmpeg_cfrSyncOption($help));
    }

    public function testCfrSyncOptionFallsBackToVersionWhenProbeFails()
    {
        $this->assertSame('-fps_mode cfr', \ffmpeg_cfrSyncOption('', \ffmpeg_parseVersion('ffmpeg version 9.0.2 Copyright')));
        $this->assertSame('-vsync cfr', \ffmpeg_cfrSyncOption('', \ffmpeg_parseVersion('ffmpeg version 6.1.1-3ubuntu5 Copyright')));
        // Unknown version (git build or unrunnable binary): keep the historical option.
        $this->assertSame('-vsync cfr', \ffmpeg_cfrSyncOption('', \ffmpeg_parseVersion('ffmpeg version N-109871-g1234567')));
        $this->assertSame('-vsync cfr', \ffmpeg_cfrSyncOption(null));
    }
}
