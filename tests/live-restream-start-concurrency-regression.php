<?php

/**
 * Run only in a throwaway Linux container, like live-restream-kill-stale-log-regression.php.
 * Exercises the real runRestream/startRestream/process lookup with dummy FFmpeg processes.
 * Source readiness is stubbed false so no real FFmpeg or HTTP request is launched.
 * RESTREAM_SCRIPT=<path> selects an earlier copy for before/after verification.
 */
if (PHP_OS_FAMILY !== 'Linux' || getenv('AVIDEO_ISOLATED_TEST') !== '1'
    || !is_dir('/proc/self/fd') || !is_executable('/bin/bash')) {
    echo "SKIP: needs an isolated Linux container with /proc and bash\n";
    exit(0);
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../plugin/Live/standAloneFiles/functions.php';
require __DIR__ . '/../plugin/Live/standAloneFiles/restreamProfiles.php';
require __DIR__ . '/../plugin/Live/standAloneFiles/restreamLogging.php';

// Exercise the source-not-ready return on the first attempt, without network access/retries.
const STARTRESTREAM_MAX_TRIES = 0;
function _isURL200($url, $force)
{
    return false;
}

$srScript = getenv('RESTREAM_SCRIPT') ?: __DIR__ . '/../plugin/Live/standAloneFiles/restreamer.json.php';
$srSource = str_replace("\r\n", "\n", file_get_contents($srScript));
foreach (['runRestream', 'startRestream', 'getRestreamRequestSummary', 'sanitizeLogFileComponent',
    'getRestreamProcessIdsByMarkers', 'getProcess', 'killIfIsRunning', 'isRestreamProcessIdRunning', '_addQueryStringParameter'] as $srFunction) {
    if (!preg_match('/function ' . $srFunction . '\(.*?\n}\n/s', $srSource, $srMatch)) {
        throw new RuntimeException('Could not extract ' . $srFunction);
    }
    // Preserve the source file's Amp import when evaluating its real functions.
    eval('use Amp\\Deferred; ' . $srMatch[0]);
}

function srStartProcess($sourceUrl, $restreamId, $historyId)
{
    $command = '/usr/bin/ffmpeg -i ' . $sourceUrl . '?live_restreams_id=' . $restreamId
        . '&liveTransmitionHistory_id=' . $historyId . ' -f flv rtmp://sink.example/live/test';
    return (int) shell_exec('/bin/bash -c ' . escapeshellarg('exec -a ' . escapeshellarg($command) . ' sleep 300')
        . ' >/dev/null 2>&1 & echo $!');
}

function srAssert($condition, $message)
{
    global $srFailures;
    echo ($condition ? 'PASS ' : 'FAIL ') . $message . PHP_EOL;
    if (!$condition) {
        $srFailures++;
    }
}

$srFailures = 0;
$srPids = [];
$srUrl = 'https://source.example/live/test/index.m3u8';
$srHistory = 87654;
$srDestination = 76543;
$srLockFile = "/tmp/restream_lock_{$srDestination}_{$srHistory}.lock";
$srLock = fopen($srLockFile, 'c');
if (!$srLock || !flock($srLock, LOCK_EX | LOCK_NB)) {
    throw new RuntimeException('The isolated test lock must be available');
}
$ffmpegBinary = '/usr/bin/ffmpeg';
$separateRestreams = true;
$isATest = false;
$json = new stdClass();

try {
    $srPids['scheduled'] = srStartProcess($srUrl, 0, $srHistory);
    $srPids['selected'] = srStartProcess($srUrl, $srDestination, $srHistory);
    $srPids['otherDestination'] = srStartProcess($srUrl, $srDestination + 1, $srHistory);
    $srPids['otherSession'] = srStartProcess($srUrl, $srDestination, $srHistory + 1);
    $srRequest = (object) [
        'm3u8' => $srUrl,
        'restreamsDestinations' => [$srDestination => 'rtmp://sink.example/live/test'],
        'logFile' => '/tmp/start-concurrency.log',
        'users_id' => 1,
        'responseToken' => '',
        'liveTransmitionHistory_id' => $srHistory,
        'live_restreams_id' => $srDestination,
    ];

    // A watchdog/manual restart must never kill the process while another start owns its lock.
    runRestream(clone $srRequest);
    srAssert(isRestreamProcessIdRunning($srPids['selected']), 'a locked destination is not killed before startRestream checks its lock');
    if (!isRestreamProcessIdRunning($srPids['selected'])) {
        $srPids['selected'] = srStartProcess($srUrl, $srDestination, $srHistory);
    }
    $separateRestreams = false;
    runRestream(clone $srRequest);
    srAssert(isRestreamProcessIdRunning($srPids['selected']), 'combined-output mode also honors the lock before killing');
    $separateRestreams = true;
    if (!isRestreamProcessIdRunning($srPids['selected'])) {
        $srPids['selected'] = srStartProcess($srUrl, $srDestination, $srHistory);
    }

    flock($srLock, LOCK_UN);
    fclose($srLock);
    $srLock = null;

    // Simulate the asynchronous on_publish worker after the scheduler's hook started id=0.
    $srRequest->live_restreams_id = 0;
    runRestream($srRequest);
    srAssert(isRestreamProcessIdRunning($srPids['scheduled']), 'the automatic worker preserves the scheduler relay for the same source/session');
    srAssert(!isRestreamProcessIdRunning($srPids['selected']), 'the selected destination\'s previous process is stopped for restart');
    srAssert(isRestreamProcessIdRunning($srPids['otherDestination']), 'a destination not in this request is preserved');
    srAssert(isRestreamProcessIdRunning($srPids['otherSession']), 'the same destination in another session is preserved');
} finally {
    if (is_resource($srLock)) {
        flock($srLock, LOCK_UN);
        fclose($srLock);
    }
    foreach ($srPids as $srPid) {
        if ($srPid > 0 && isRestreamProcessIdRunning($srPid)) {
            exec('kill -9 ' . intval($srPid));
        }
    }
    @unlink($srLockFile);
}

echo $srFailures ? "{$srFailures} FAILURE(S)\n" : "ALL PASS\n";
exit($srFailures ? 1 : 0);
