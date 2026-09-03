<?php

/**
 * ffmpegCapabilities.php
 *
 * Framework-independent FFmpeg capability detector for the restream pipeline. Like
 * restreamProfiles.php/restreamLogging.php, this file has zero AVideo framework dependency so it
 * can be copied alongside restreamer.json.php to a standalone FFMPEG host.
 *
 * Purpose: decide, from real evidence (binary probing), whether the FFmpeg build available on
 * THIS host can run the "fifo" pseudo-muxer resiliency layer used for restream output, instead
 * of guessing from a bare `ffmpeg -version` major number. A version number alone does not tell
 * you whether libavformat was compiled with the fifo muxer, which TLS backend (if any) is
 * available for rtmps output, or whether a usable CA bundle exists on this host.
 *
 * Every probe here is:
 *   - Executed via proc_open() with the command passed as an argv array (POSIX) so the binary
 *     is invoked directly (execvp) with NO shell involved at all - there is no string
 *     concatenation/interpolation into a shell command anywhere in this file, so there is no
 *     injection surface even though $ffmpegBinary ultimately comes from an admin setting.
 *   - Individually timeout-bounded, so a hung/misbehaving binary can never block the caller
 *     indefinitely.
 *   - Best-effort: any single probe failing only clears that one capability flag, it never
 *     throws and never aborts the rest of the detection.
 */

define('FFMPEG_CAPABILITY_EXEC_TIMEOUT_SECONDS', 8);

/**
 * Runs $binary with $args (already-separated argv, never a pre-built shell string) and returns
 * its stdout/stderr/exit code, bounded by $timeoutSeconds. Never uses exec()/shell_exec()/system()
 * and never builds a shell command line - proc_open() is called with the command as a PHP array
 * on POSIX systems, which PHP executes directly via execvp(), bypassing the shell entirely.
 */
function ffmpeg_capabilitiesSafeExec($binary, array $args, $timeoutSeconds = FFMPEG_CAPABILITY_EXEC_TIMEOUT_SECONDS)
{
    $result = array('ok' => false, 'stdout' => '', 'stderr' => '', 'exitCode' => null, 'reason' => null);

    if (empty($binary) || !is_string($binary)) {
        $result['reason'] = 'empty_binary';
        return $result;
    }
    if (!function_exists('proc_open')) {
        $result['reason'] = 'proc_open_unavailable';
        return $result;
    }

    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $descriptorSpec = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w'),
        2 => array('pipe', 'w'),
    );

    if ($isWindows) {
        // proc_open's array form still goes through cmd.exe's argument parsing on Windows, so
        // build a conservatively escaped string instead. This tool targets the Linux/Docker
        // hosts the restream pipeline actually runs on; Windows support here is best-effort only.
        $spawn = escapeshellarg($binary);
        foreach ($args as $arg) {
            $spawn .= ' ' . escapeshellarg($arg);
        }
    } else {
        // No shell at all: PHP execvp()s $binary directly with these exact argv entries.
        $spawn = array_merge(array($binary), $args);
    }

    $process = @proc_open($spawn, $descriptorSpec, $pipes, null, null, array('bypass_shell' => true));
    if (!is_resource($process)) {
        $result['reason'] = 'proc_open_failed';
        return $result;
    }

    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);

    $stdout = '';
    $stderr = '';
    $start = microtime(true);
    $timedOut = false;

    while (true) {
        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);

        $status = proc_get_status($process);
        if (empty($status['running'])) {
            break;
        }
        if ((microtime(true) - $start) > $timeoutSeconds) {
            $timedOut = true;
            break;
        }
        usleep(20000);
    }

    if ($timedOut) {
        // Best-effort terminate; never let a hung probe (e.g. a build waiting on stdin) block
        // the caller beyond the configured timeout.
        @proc_terminate($process, 9);
    }

    $stdout .= (string) @stream_get_contents($pipes[1]);
    $stderr .= (string) @stream_get_contents($pipes[2]);
    @fclose($pipes[1]);
    @fclose($pipes[2]);
    $exitCode = @proc_close($process);

    $result['ok'] = !$timedOut;
    $result['stdout'] = $stdout;
    $result['stderr'] = $stderr;
    $result['exitCode'] = $timedOut ? null : $exitCode;
    $result['reason'] = $timedOut ? 'timeout' : null;
    return $result;
}

/**
 * Parses the first line of `ffmpeg -version` output. Handles both release builds
 * ("ffmpeg version 4.2.7-0ubuntu0.1", "ffmpeg version 6.1.1", "ffmpeg version n4.4.1-289-g...")
 * and git/dev builds ("ffmpeg version N-109871-g1234567"), where no reliable major/minor/patch
 * number is available at all.
 */
function ffmpeg_parseVersion($versionOutput)
{
    $default = array('raw' => '', 'major' => null, 'minor' => null, 'patch' => null, 'isGit' => false);
    if (!is_string($versionOutput) || trim($versionOutput) === '') {
        return $default;
    }

    $firstLine = trim(strtok($versionOutput, "\n"));
    $isGit = (bool) preg_match('/^ffmpeg version\s+N-/i', $firstLine) || stripos($firstLine, 'git') !== false;

    if (preg_match('/ffmpeg version\s+n?(\d+)\.(\d+)(?:\.(\d+))?/i', $firstLine, $m)) {
        return array(
            'raw' => $firstLine,
            'major' => (int) $m[1],
            'minor' => (int) $m[2],
            'patch' => isset($m[3]) && $m[3] !== '' ? (int) $m[3] : 0,
            'isGit' => $isGit,
        );
    }

    return array('raw' => $firstLine, 'major' => null, 'minor' => null, 'patch' => null, 'isGit' => $isGit);
}

/**
 * True when $token appears as its own whitespace/comma-delimited field in $haystack - the shape
 * every `-muxers`/`-demuxers`/`-protocols`/`-encoders`/`-filters` listing line uses. Guards
 * against a false positive on a token that is only a substring of a different, longer name.
 */
function ffmpeg_hasToken($haystack, $token)
{
    if (!is_string($haystack) || $haystack === '' || !is_string($token) || $token === '') {
        return false;
    }
    return (bool) preg_match('/(^|[\s,=])' . preg_quote($token, '/') . '($|[\s,])/mi', $haystack);
}

function ffmpeg_hasMuxer($muxersOutput, $name)
{
    return ffmpeg_hasToken($muxersOutput, $name);
}

function ffmpeg_hasDemuxer($demuxersOutput, $name)
{
    return ffmpeg_hasToken($demuxersOutput, $name);
}

function ffmpeg_hasProtocol($protocolsOutput, $name)
{
    return ffmpeg_hasToken($protocolsOutput, $name);
}

function ffmpeg_hasEncoder($encodersOutput, $name)
{
    return ffmpeg_hasToken($encodersOutput, $name);
}

function ffmpeg_hasFilter($filtersOutput, $name)
{
    return ffmpeg_hasToken($filtersOutput, $name);
}

/**
 * Which TLS backend(s), if any, `ffmpeg -buildconf` reports as compiled in. An empty result
 * means rtmps:// output will fail outright regardless of every other capability.
 */
function ffmpeg_detectTlsBackend($buildconfOutput)
{
    $text = is_string($buildconfOutput) ? $buildconfOutput : '';
    $backends = array();
    if (preg_match('/--enable-openssl\b/', $text)) {
        $backends[] = 'openssl';
    }
    if (preg_match('/--enable-gnutls\b/', $text)) {
        $backends[] = 'gnutls';
    }
    if (preg_match('/--enable-mbedtls\b/', $text)) {
        $backends[] = 'mbedtls';
    }
    if (preg_match('/--enable-libtls\b/', $text)) {
        $backends[] = 'libtls';
    }
    return $backends;
}

/**
 * First readable system CA bundle path found (Debian/Ubuntu, RHEL/CentOS, Alpine), or null when
 * none is readable. A missing CA bundle does not by itself disable rtmps output (FFmpeg's rtmps
 * protocol already runs with -tls_verify 0 in this codebase, see getRestreamTlsOptions()'s own
 * comment), but is still useful diagnostic evidence to surface to an admin/CLI operator.
 */
function ffmpeg_hasReadableCaBundle()
{
    $candidates = array(
        '/etc/ssl/certs/ca-certificates.crt',
        '/etc/pki/tls/certs/ca-bundle.crt',
        '/etc/ssl/cert.pem',
        '/etc/ssl/certs',
    );
    foreach ($candidates as $path) {
        if (is_readable($path)) {
            return $path;
        }
    }
    return null;
}

/**
 * Parses `ffmpeg -h muxer=fifo` output for the presence of every AVOption the restream FIFO
 * output layer needs to configure. A missing option means this FFmpeg build's fifo muxer is too
 * old/limited/different to safely drive with the options this codebase sends.
 */
function ffmpeg_parseFifoMuxerOptions($helpOutput)
{
    $text = is_string($helpOutput) ? $helpOutput : '';
    $requiredOptions = array(
        'attempt_recovery',
        'recovery_wait_time',
        'recovery_wait_streamtime',
        'recover_any_error',
        'restart_with_keyframe',
        'drop_pkts_on_overflow',
        'max_recovery_attempts',
        'queue_size',
        'fifo_format',
        'format_opts',
    );
    $found = array();
    foreach ($requiredOptions as $opt) {
        $found[$opt] = (bool) preg_match('/^\s*-' . preg_quote($opt, '/') . '\b/mi', $text);
    }
    return $found;
}

/**
 * Full, best-effort capability snapshot for $ffmpegBinary. Never throws: an unusable binary
 * simply yields ['available' => false, 'errors' => [...]].
 */
function ffmpegDetectCapabilities($ffmpegBinary, $timeoutSeconds = FFMPEG_CAPABILITY_EXEC_TIMEOUT_SECONDS)
{
    $result = array(
        'binary' => (string) $ffmpegBinary,
        'available' => false,
        'version' => array('raw' => '', 'major' => null, 'minor' => null, 'patch' => null, 'isGit' => false),
        'tlsBackends' => array(),
        'caBundlePath' => null,
        'hasFifoMuxer' => false,
        'fifoMuxerOptions' => array(),
        'hasFlvMuxer' => false,
        'hasTeeMuxer' => false,
        'hasRtmpProtocol' => false,
        'hasRtmpsProtocol' => false,
        'hasLibx264Encoder' => false,
        'hasAacEncoder' => false,
        'checkedAtUtc' => gmdate('Y-m-d\TH:i:s\Z'),
        'errors' => array(),
    );

    $versionRun = ffmpeg_capabilitiesSafeExec($ffmpegBinary, array('-hide_banner', '-version'), $timeoutSeconds);
    if (empty($versionRun['ok']) || stripos($versionRun['stdout'], 'ffmpeg version') === false) {
        $result['errors'][] = 'ffmpeg binary not runnable ('
            . ($versionRun['reason'] ?: ('exit=' . var_export($versionRun['exitCode'], true))) . ')';
        return $result;
    }
    $result['available'] = true;
    $result['version'] = ffmpeg_parseVersion($versionRun['stdout']);

    $buildconfRun = ffmpeg_capabilitiesSafeExec($ffmpegBinary, array('-hide_banner', '-buildconf'), $timeoutSeconds);
    $result['tlsBackends'] = ffmpeg_detectTlsBackend($buildconfRun['stdout'] . $buildconfRun['stderr']);
    $result['caBundlePath'] = ffmpeg_hasReadableCaBundle();

    $muxersRun = ffmpeg_capabilitiesSafeExec($ffmpegBinary, array('-hide_banner', '-muxers'), $timeoutSeconds);
    $muxersText = $muxersRun['stdout'] . $muxersRun['stderr'];
    $result['hasFifoMuxer'] = ffmpeg_hasMuxer($muxersText, 'fifo');
    $result['hasFlvMuxer'] = ffmpeg_hasMuxer($muxersText, 'flv');
    $result['hasTeeMuxer'] = ffmpeg_hasMuxer($muxersText, 'tee');

    $protocolsRun = ffmpeg_capabilitiesSafeExec($ffmpegBinary, array('-hide_banner', '-protocols'), $timeoutSeconds);
    $protocolsText = $protocolsRun['stdout'] . $protocolsRun['stderr'];
    $result['hasRtmpProtocol'] = ffmpeg_hasProtocol($protocolsText, 'rtmp');
    $result['hasRtmpsProtocol'] = ffmpeg_hasProtocol($protocolsText, 'rtmps');

    $encodersRun = ffmpeg_capabilitiesSafeExec($ffmpegBinary, array('-hide_banner', '-encoders'), $timeoutSeconds);
    $encodersText = $encodersRun['stdout'] . $encodersRun['stderr'];
    $result['hasLibx264Encoder'] = ffmpeg_hasEncoder($encodersText, 'libx264');
    $result['hasAacEncoder'] = ffmpeg_hasEncoder($encodersText, 'aac');

    if ($result['hasFifoMuxer']) {
        $helpRun = ffmpeg_capabilitiesSafeExec($ffmpegBinary, array('-hide_banner', '-h', 'muxer=fifo'), $timeoutSeconds);
        $result['fifoMuxerOptions'] = ffmpeg_parseFifoMuxerOptions($helpRun['stdout'] . $helpRun['stderr']);
    } else {
        $result['errors'][] = 'fifo muxer not compiled in this FFmpeg build';
    }

    if (empty($result['tlsBackends'])) {
        $result['errors'][] = 'no TLS backend detected in -buildconf (rtmps:// output will fail)';
    }
    if (empty($result['hasRtmpProtocol'])) {
        $result['errors'][] = 'rtmp protocol not available';
    }

    return $result;
}

/**
 * True only when every AVOption the FIFO output layer sends is confirmed present on this
 * exact FFmpeg build. Used as the hard gate before ever attempting the FIFO output path -
 * capability that cannot be positively confirmed is treated as absent (fail safe to legacy).
 */
function ffmpegFifoMuxerFullySupported($capabilities)
{
    $capabilities = (array) $capabilities;
    if (empty($capabilities['available']) || empty($capabilities['hasFifoMuxer']) || empty($capabilities['hasFlvMuxer'])) {
        return false;
    }
    $requiredOptions = array(
        'attempt_recovery',
        'recovery_wait_time',
        'recovery_wait_streamtime',
        'recover_any_error',
        'restart_with_keyframe',
        'drop_pkts_on_overflow',
        'max_recovery_attempts',
        'queue_size',
        'fifo_format',
        'format_opts',
    );
    $fifoOptions = isset($capabilities['fifoMuxerOptions']) ? (array) $capabilities['fifoMuxerOptions'] : array();
    foreach ($requiredOptions as $opt) {
        if (empty($fifoOptions[$opt])) {
            return false;
        }
    }
    return true;
}

/**
 * One-line, redaction-safe (contains no destination/secret) human-readable summary, used by
 * both the CLI tool and structured log events.
 */
function ffmpegCapabilitiesSummaryText($capabilities)
{
    $capabilities = (array) $capabilities;
    if (empty($capabilities['available'])) {
        return 'ffmpeg not available/runnable: ' . implode('; ', (array) @$capabilities['errors']);
    }
    $version = (array) @$capabilities['version'];
    $versionText = !empty($version['raw']) ? $version['raw'] : 'unknown version';
    $fifoText = ffmpegFifoMuxerFullySupported($capabilities) ? 'fifo=fully-supported' : 'fifo=NOT fully supported';
    $tlsText = !empty($capabilities['tlsBackends']) ? ('tls=' . implode(',', $capabilities['tlsBackends'])) : 'tls=NONE';
    return "{$versionText} | {$fifoText} | {$tlsText} | rtmp="
        . (!empty($capabilities['hasRtmpProtocol']) ? 'yes' : 'no')
        . ' rtmps=' . (!empty($capabilities['hasRtmpsProtocol']) ? 'yes' : 'no')
        . ' libx264=' . (!empty($capabilities['hasLibx264Encoder']) ? 'yes' : 'no')
        . ' aac=' . (!empty($capabilities['hasAacEncoder']) ? 'yes' : 'no');
}
