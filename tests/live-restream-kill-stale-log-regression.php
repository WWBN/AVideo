<?php

/**
 * Regression test for plugin/Live/standAloneFiles/kill_ffmpeg_restream.php (cron, every minute).
 *
 * A restream that was killed leaves its log behind with "Opening ... index.m3u8" lines. A new
 * restream for the same destination and live has the same input URL, and the cron used the old
 * log to kill the new, healthy restream every minute (reproduced on the Docker stack, 2026-10-29).
 *
 * Linux only. It starts dummy processes named "ffmpeg ..." and runs the real script, which reads
 * /var/www/tmp and kills processes, so it only runs in a throwaway container:
 *   docker run --rm --network none -e AVIDEO_ISOLATED_TEST=1 -v "<repo>:/repo:ro" --entrypoint sh <image with php and bash> \
 *     -c 'php /repo/tests/live-restream-kill-stale-log-regression.php'
 * KILL_RESTREAM_SCRIPT=<path> runs it against another copy of the script (e.g. the old one).
 * Repeat with php -d disable_functions=readlink to exercise the legacy /proc fallback.
 */

$failures = 0;
function krAssert($condition, $message)
{
    global $failures;
    echo ($condition ? 'PASS ' : 'FAIL ') . $message . PHP_EOL;
    if (!$condition) {
        $failures++;
    }
}

if (PHP_OS_FAMILY !== 'Linux' || !is_dir('/proc/self/fd') || !is_executable('/bin/bash')) {
    echo "SKIP: needs Linux with /proc and bash\n";
    exit(0);
}
if (getenv('AVIDEO_ISOLATED_TEST') !== '1') {
    echo "SKIP: set AVIDEO_ISOLATED_TEST=1 and run it in a throwaway container (it kills processes listed in /var/www/tmp logs)\n";
    exit(0);
}

$krLogDir = '/var/www/tmp/';
@mkdir($krLogDir, 0777, true);
if (!empty(glob($krLogDir . 'ffmpeg_restreamer_*.log'))) {
    echo "SKIP: {$krLogDir} already has restream logs, use an empty throwaway container\n";
    exit(0);
}
$krScript = getenv('KILL_RESTREAM_SCRIPT') ?: __DIR__ . '/../plugin/Live/standAloneFiles/kill_ffmpeg_restream.php';

function krUrl($restreamId, $historyId)
{
    return "https://live:8443/live/685042d64ab95/index.m3u8?live_restreams_id={$restreamId}&liveTransmitionHistory_id={$historyId}";
}

function krOpeningLog($url, $withSegments)
{
    $lines = array();
    for ($i = 0; $i < 12; $i++) {
        $lines[] = "frame= 2960 fps= 25 q=26.0 size=   35420KiB time=00:01:38.60 bitrate=2942.8kbits/s speed=0.8x";
        $lines[] = "[https @ 0x70d4d00ecf00] Opening '{$url}' for reading";
        if ($withSegments) {
            $lines[] = "[hls @ 0x5839ae836080] Opening 'crypto+https://live:8443/live/685042d64ab95/{$i}.ts' for reading";
        }
    }
    return implode("\n", $lines) . "\n";
}

// Starts a process whose command line is "ffmpeg -i <url> ..." and whose output goes to $outFile.
function krStartFakeFfmpeg($url, $outFile)
{
    $argv0 = "ffmpeg -hide_banner -i {$url} -c:v libx264 -f flv rtmp://127.0.0.1:19351/live/sink";
    $cmd = '/bin/bash -c ' . escapeshellarg('exec -a ' . escapeshellarg($argv0) . ' sleep 300')
        . ' > ' . escapeshellarg($outFile) . ' 2>&1 & echo $!';
    $pid = (int) shell_exec($cmd);
    usleep(300000);
    return $pid;
}

function krIsRunning($pid)
{
    $stat = @file_get_contents("/proc/{$pid}/stat");
    return $stat !== false && !preg_match('/^\d+ \(.+\) Z /', $stat);
}

// kill -9 returns before the process is gone; poll for up to 2 s
function krStopped($pid)
{
    for ($i = 0; $i < 40 && krIsRunning($pid); $i++) {
        usleep(50000);
    }
    return !krIsRunning($pid);
}

class KrCronScope
{
    public function run($script)
    {
        include $script;
    }
}

$pids = array();

// 1) old log of a killed restream (not written for 2 minutes) + new healthy restream, same URL
$url1 = krUrl(14, 52);
$oldLog = $krLogDir . 'ffmpeg_restreamer_7_2026-10-29-01-53-18_14_52_127.0.0.1.log';
file_put_contents($oldLog, krOpeningLog($url1, false));
touch($oldLog, time() - 120);
$newLog = $krLogDir . 'ffmpeg_restreamer_7_2026-10-29-01-57-00_14_52_127.0.0.1.log';
$pids['healthy'] = krStartFakeFfmpeg($url1, $newLog);
file_put_contents($newLog, krOpeningLog($url1, true));

// 2) orphan restream after the source ended: still writing its log, only playlist reloads
$url2 = krUrl(21, 60);
$orphanLog = $krLogDir . 'ffmpeg_restreamer_7_2026-10-29-02-10-00_21_60_127.0.0.1.log';
$pids['orphan'] = krStartFakeFfmpeg($url2, $orphanLog);
file_put_contents($orphanLog, krOpeningLog($url2, false));

// 3) fresh orphan-looking log, but the process with that URL writes to another file
$url3 = krUrl(31, 70);
$otherLog = $krLogDir . 'ffmpeg_restreamer_7_2026-10-29-02-20-00_31_70_127.0.0.1.log';
file_put_contents($otherLog, krOpeningLog($url3, false));
$pids['otherWriter'] = krStartFakeFfmpeg($url3, '/tmp/kill-restream-regression-other-output.log');

ob_start();
(new KrCronScope())->run($krScript);
$output = ob_get_clean();
echo $output;

krAssert(krIsRunning($pids['healthy']), 'a new restream is not killed because of the stale log of an earlier restream with the same URL');
krAssert(krStopped($pids['orphan']), 'an orphan restream that still writes its log (only playlist reloads) is killed');
if (function_exists('readlink')) {
    krAssert(krIsRunning($pids['otherWriter']), 'a process that does not write the analyzed log is not killed');
} else {
    krAssert(restreamProcessWritesToLog($pids['otherWriter'], $otherLog) === null, 'unavailable readlink falls back instead of aborting the cron');
    krAssert(krStopped($pids['otherWriter']), 'without /proc inspection the legacy URL-based kill is preserved');
}

foreach ($pids as $pid) {
    if ($pid > 0 && krIsRunning($pid)) {
        exec('kill -9 ' . intval($pid) . ' 2>/dev/null');
    }
}
foreach (array($oldLog, $newLog, $orphanLog, $otherLog, '/tmp/kill-restream-regression-other-output.log') as $file) {
    @unlink($file);
}

echo $failures ? "{$failures} FAILURE(S)\n" : "ALL PASS\n";
exit($failures ? 1 : 0);
