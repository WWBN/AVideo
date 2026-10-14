<?php
// Linux FFmpeg integration test with temporary media; no database or site configuration loaded.
// php tests/ffmpeg-conversion-argv-regression.php
if (PHP_SAPI !== 'cli') { exit(1); }

$global = [];
$advancedCustom = (object) [];
$ffmpegReviewLogs = [];
class AVideoLog { public static $SECURITY = 'security'; }
function _error_log($message, $level = '') { $GLOBALS['ffmpegReviewLogs'][] = [$message, $level]; }
function getSelfUserAgent() { return 'AVideoStreamer review.example.test'; }
require_once dirname(__DIR__) . '/objects/functionsFFMPEG.php';

function expectConversion($condition, $message)
{
    if (!$condition) { throw new RuntimeException($message); }
    echo "PASS: {$message}\n";
}

$testDir = sys_get_temp_dir() . '/ffmpeg_argv_' . bin2hex(random_bytes(8));
mkdir($testDir, 0700);
try {
    $fixture = $testDir . '/source.mp4';
    $log = $testDir . '/ffmpeg.log';
    $create = array_merge(getFFmpegExecutableArgs(true), ['-v', 'error', '-y', '-f', 'lavfi', '-i', 'testsrc2=size=160x90:rate=10', '-f', 'lavfi', '-i', 'sine=frequency=440:sample_rate=44100', '-t', '4', '-c:v', 'libx264', '-threads', '1', '-c:a', 'aac', '-movflags', '+faststart', $fixture]);
    expectConversion(execFFMPEGArgs($create, $log) === 0, 'Faststart input fixture generated');

    $profiles = [
        'default' => getFFmpegConversionDefaultParameters(),
        'x265' => '-c:v libx265 -preset fast -crf 28 -c:a aac -b:a 96k -movflags +faststart',
        'scale' => '-c:v libx264 -vf scale=-2:720 -pix_fmt yuv420p -r 30000/1001 -c:a aac -ar 48000 -ac 2',
        'copy' => '-c copy -bsf:a aac_adtstoasc',
    ];
    foreach ($profiles as $name => $profile) {
        $advancedCustom->ffmpegParameters = $profile;
        $destination = $testDir . '/' . $name . '.mp4';
        $args = buildFFMPEGCommandArgs($fixture, $destination, 0, 1);
        expectConversion(!in_array('-user_agent', $args, true), $name . ' keeps local input options valid');
        expectConversion(execFFMPEGArgs($args, $log) === 0 && filesize($destination) > 0, $name . ' conversion works');
    }
    foreach ([0, 1] as $try) {
        $destination = $testDir . '/audio-' . $try . '.mp3';
        $result = convertVideoFileWithFFMPEG($fixture, $destination, $log, $try);
        expectConversion($result['return'] === 0 && filesize($destination) > 0, 'MP3 conversion attempt ' . $try);
    }
    $advancedCustom->ffmpegParameters = '-f mp4';
    $args = getFFmpegConversionParametersArgs();
    expectConversion($args === parseFFmpegConversionParameters(getFFmpegConversionDefaultParameters()), 'Rejected stored value uses the default profile');
    expectConversion(end($ffmpegReviewLogs)[1] === AVideoLog::$SECURITY, 'Rejected stored value is logged as SECURITY');

    $hls = $testDir . '/index.m3u8';
    $create = array_merge(getFFmpegExecutableArgs(true), ['-v', 'error', '-y', '-i', $fixture, '-c', 'copy', '-f', 'hls', '-hls_time', '1', '-hls_playlist_type', 'vod', $hls]);
    expectConversion(execFFMPEGArgs($create, $log) === 0, 'Local HLS fixture generated');
    $advancedCustom->ffmpegParameters = getFFmpegConversionDefaultParameters();
    $result = convertVideoFileWithFFMPEG($hls, $testDir . '/hls.mp4', $log);
    expectConversion($result['return'] === 0 && filesize($result['toFileLocation']) > 0, 'Local HLS converts to MP4');

    mkdir($testDir . '/videos/asset', 0700, true);
    $destination = $testDir . '/videos/asset/index.mp4';
    $command = buildFFMPEGCommand($fixture, $destination, 0, 1);
    $workerSource = file_get_contents(dirname(__DIR__) . '/plugin/API/standAlone/ffmpeg.json.php');
    preg_match('/^preg_match\(.*?\);\r?$/m', $workerSource, $outputParser);
    $ffmpegCommand = $command;
    eval($outputParser[0]);
    expectConversion(($matches[1] ?? '') === $destination, 'Standalone worker still identifies the output path');
    expectConversion(strpos(preg_replace('/-threads\s+\d+/', '', $command), '-threads') === false, 'Standalone worker can replace the thread option');
    expectConversion(execFFMPEGArgs(['/bin/sh', '-c', $command], $log) === 0 && filesize($destination) > 0, 'Shell command used by async/remote callers works');

    $executable = trim((string) shell_exec('command -v ' . escapeshellarg(getFFmpegExecutableArgs(true)[0])));
    mkdir($testDir . '/ffmpeg tools', 0700);
    symlink($executable, $testDir . '/ffmpeg tools/ffmpeg');
    $global['ffmpeg'] = 'nice -n 10 "' . $testDir . '/ffmpeg tools/"';
    $result = convertVideoFileWithFFMPEG($fixture, $testDir . '/wrapper.mp4', $log);
    expectConversion($result['return'] === 0 && filesize($result['toFileLocation']) > 0, 'Quoted path prefix inside a wrapper executes correctly');
} catch (Throwable $exception) {
    if (is_file($testDir . '/ffmpeg.log')) { fwrite(STDERR, file_get_contents($testDir . '/ffmpeg.log')); }
    throw $exception;
} finally {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) { rmdir($file->getPathname()); } else { unlink($file->getPathname()); }
    }
    rmdir($testDir);
}
