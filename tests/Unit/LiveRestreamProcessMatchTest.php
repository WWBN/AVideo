<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once \APP_ROOT . '/plugin/Live/standAloneFiles/functions.php';

/**
 * Regression coverage for the 2026-10-25 TIC incident: the manual "Stop" resolved the origin
 * (:8443) m3u8 while the running relay had been launched from the CDN m3u8, so the URL-based
 * process lookup found nothing, the stop was reported as "already stopped" and the relay kept
 * pushing. Process lookup must depend only on the restream/live id markers.
 */
class LiveRestreamProcessMatchTest extends TestCase
{
    private function cdnCommand($restreamId = 44, $historyId = 972)
    {
        return '/usr/local/bin/ffmpeg -hide_banner -y -v info -rw_timeout 120000000 -user_agent "AVideoRestreamer"'
            . ' -i "https://site.cdn.ypt.me/662376121a544.m3u8?live_restreams_id=' . $restreamId
            . '&liveTransmitionHistory_id=' . $historyId . '" -c:v libx264 -f flv rtmps://a.rtmps.youtube.com/live2/key';
    }

    public function testMatchesRegardlessOfWhichSourceUrlLaunchedTheRelay()
    {
        $origin = str_replace('https://site.cdn.ypt.me/', 'https://site.example:8443/live/', $this->cdnCommand());
        $this->assertTrue(\isRestreamFfmpegCommandLine($this->cdnCommand(), 44, 972));
        $this->assertTrue(\isRestreamFfmpegCommandLine($origin, 44, 972));
        $this->assertTrue(\isRestreamFfmpegCommandLine(str_replace('/usr/local/bin/ffmpeg', '/usr/bin/ffmpeg', $this->cdnCommand()), 44, 972));
    }

    public function testIdsMustMatchExactly()
    {
        $this->assertFalse(\isRestreamFfmpegCommandLine($this->cdnCommand(44, 972), 4, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine($this->cdnCommand(441, 972), 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine($this->cdnCommand(44, 9721), 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine($this->cdnCommand(43, 972), 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine($this->cdnCommand(), 0, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine($this->cdnCommand(), 44, 0));
    }

    public function testIgnoresShellWrappersAndSearchTools()
    {
        $this->assertFalse(\isRestreamFfmpegCommandLine("sh -c '" . $this->cdnCommand() . " & echo \$! > /tmp/x.pid'", 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine('nohup sh -c ' . $this->cdnCommand(), 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine('grep live_restreams_id=44&liveTransmitionHistory_id=972', 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine('pgrep -af ffmpeg.*live_restreams_id=44.*liveTransmitionHistory_id=972', 44, 972));
        $this->assertFalse(\isRestreamFfmpegCommandLine('', 44, 972));
    }

    public function testFindsEveryMatchingPidFromPsOutput()
    {
        $lines = array(
            ' 1001 ' . $this->cdnCommand(44, 972),
            ' 1002 ' . $this->cdnCommand(43, 972),
            ' 1003 sh -c ' . $this->cdnCommand(44, 972),
            '1004 ' . str_replace('https://site.cdn.ypt.me/', 'https://site.example:8443/live/', $this->cdnCommand(44, 972)),
            'garbage line',
            ' 1001 ' . $this->cdnCommand(44, 972),
        );
        $this->assertSame(array(1001, 1004), \findRestreamProcessIds($lines, 44, 972));
        $this->assertSame(array(1002), \findRestreamProcessIds($lines, 43, 972));
        $this->assertSame(array(), \findRestreamProcessIds($lines, 44, 973));
    }
}
