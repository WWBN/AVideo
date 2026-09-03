<?php

/**
 * ffmpegCapabilitiesTool.php
 *
 * CLI-only diagnostic tool for the restream FIFO output-recovery layer (see
 * restreamProfiles.php's own module docblock and Live.php's restreamFifo* admin settings).
 *
 * Deliberately CLI-only (php_sapi_name() must be "cli"): every other file in this directory is
 * designed to be reachable over HTTP with its own auth (restreamer.json.php requires a signed
 * token; ffmpegCapabilities.php/restreamProfiles.php only ever define functions, they do not
 * execute anything on include). This file accepts an operator-supplied --destination host/port
 * and opens a raw TCP connection to it - if that were reachable anonymously over HTTP it would
 * be a trivial unauthenticated SSRF/port-scan primitive, so it refuses to run under a web SAPI.
 *
 * Usage (run from the AVideo host or the standalone FFMPEG host):
 *   php ffmpegCapabilitiesTool.php [--ffmpeg=/usr/bin/ffmpeg] [--destination=rtmps://host/app/key] [--json]
 *
 * What it does:
 *   1. Probes the given (or default "ffmpeg") binary with ffmpegDetectCapabilities() and prints
 *      a full capability report, including whether the FIFO output-recovery layer is fully
 *      supported on this exact build.
 *   2. If --destination is given: detects its provider, resolves DNS, and attempts a bounded
 *      raw TCP connect to host:port (NOT an RTMP handshake and NOT a real publish attempt - this
 *      tool never starts an actual broadcast/stream, it only checks basic network reachability),
 *      then reports whether the FIFO layer would be eligible for that exact destination.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("This tool can only be run from the command line.\n");
}

require_once __DIR__ . '/ffmpegCapabilities.php';
require_once __DIR__ . '/restreamProfiles.php';

function ffmpegCapToolPrintUsageAndExit()
{
    fwrite(STDERR, "Usage: php ffmpegCapabilitiesTool.php [--ffmpeg=/usr/bin/ffmpeg] [--destination=rtmps://host/app/key] [--json]\n");
    exit(1);
}

function ffmpegCapToolParseArgs(array $argv)
{
    $options = array('ffmpeg' => 'ffmpeg', 'destination' => '', 'json' => false);
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            ffmpegCapToolPrintUsageAndExit();
        } elseif ($arg === '--json') {
            $options['json'] = true;
        } elseif (strpos($arg, '--ffmpeg=') === 0) {
            $options['ffmpeg'] = substr($arg, strlen('--ffmpeg='));
        } elseif (strpos($arg, '--destination=') === 0) {
            $options['destination'] = substr($arg, strlen('--destination='));
        } else {
            fwrite(STDERR, "Unknown argument: {$arg}\n");
            ffmpegCapToolPrintUsageAndExit();
        }
    }
    return $options;
}

/**
 * Bounded raw TCP connectivity check only - no RTMP handshake, no publish attempt, no data sent.
 * Kept intentionally minimal so this tool can never itself start a broadcast/stream.
 */
function ffmpegCapToolCheckTcpConnectivity($host, $port, $timeoutSeconds = 5)
{
    $result = array('resolvedIp' => null, 'connected' => false, 'latencyMs' => null, 'error' => null);
    if (empty($host)) {
        $result['error'] = 'no host to resolve';
        return $result;
    }

    $resolvedIp = @gethostbyname($host);
    $result['resolvedIp'] = ($resolvedIp !== $host) ? $resolvedIp : null;
    if (empty($result['resolvedIp'])) {
        $result['error'] = 'DNS resolution failed';
        return $result;
    }

    $start = microtime(true);
    $errno = 0;
    $errstr = '';
    $socket = @fsockopen($result['resolvedIp'], (int) $port, $errno, $errstr, $timeoutSeconds);
    if ($socket === false) {
        $result['error'] = trim("{$errstr} (errno {$errno})");
        return $result;
    }
    fclose($socket);
    $result['connected'] = true;
    $result['latencyMs'] = (int) round((microtime(true) - $start) * 1000);
    return $result;
}

$options = ffmpegCapToolParseArgs($argv);

$report = array(
    'ffmpegBinary' => $options['ffmpeg'],
    'capabilities' => ffmpegDetectCapabilities($options['ffmpeg']),
);
$report['fifoFullySupported'] = ffmpegFifoMuxerFullySupported($report['capabilities']);

if (!empty($options['destination'])) {
    $destination = clearCommandURL($options['destination']);
    $destInfo = getDestinationHostPort($destination);
    $provider = getRestreamProvider($destination);

    // Force-enable + allow every provider for this eligibility check only: the goal here is
    // "would FIFO work for this destination if it were enabled and allow-listed", the operator
    // still has to actually enable/allow-list it via the admin settings for it to take effect.
    $probeConfig = sanitizeRestreamFifoConfig(array('enabled' => true, 'allowedProviders' => array($provider)));

    $report['destination'] = array(
        'url' => redactDestinationForLog($destination),
        'provider' => $provider,
        'scheme' => strtolower((string) parse_url($destination, PHP_URL_SCHEME)),
        'host' => $destInfo['host'],
        'port' => $destInfo['port'],
        'wouldUseFifoIfEnabledAndAllowed' => shouldUseRestreamFifoForDestination($destination, $probeConfig, $report['capabilities']),
        'tcpConnectivity' => ffmpegCapToolCheckTcpConnectivity($destInfo['host'], $destInfo['port']),
    );
}

if (!empty($options['json'])) {
    echo json_encode($report, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

echo "== FFmpeg Restream Capability Report ==" . PHP_EOL;
echo "Binary: {$report['ffmpegBinary']}" . PHP_EOL;
echo ffmpegCapabilitiesSummaryText($report['capabilities']) . PHP_EOL;
echo 'FIFO output-recovery fully supported: ' . ($report['fifoFullySupported'] ? 'YES' : 'no') . PHP_EOL;
if (!empty($report['capabilities']['errors'])) {
    echo '  Notes:' . PHP_EOL;
    foreach ($report['capabilities']['errors'] as $error) {
        echo "    - {$error}" . PHP_EOL;
    }
}

if (isset($report['destination'])) {
    $d = $report['destination'];
    echo PHP_EOL . "== Destination ==" . PHP_EOL;
    echo "URL (redacted): {$d['url']}" . PHP_EOL;
    echo "Provider: {$d['provider']} | Scheme: {$d['scheme']} | Host: {$d['host']} | Port: {$d['port']}" . PHP_EOL;
    echo 'Would use FIFO output if enabled+allow-listed for this provider: ' . ($d['wouldUseFifoIfEnabledAndAllowed'] ? 'YES' : 'no') . PHP_EOL;
    $tcp = $d['tcpConnectivity'];
    if (!empty($tcp['connected'])) {
        echo "TCP connectivity: OK (resolved {$tcp['resolvedIp']}, {$tcp['latencyMs']}ms)" . PHP_EOL;
    } else {
        echo 'TCP connectivity: FAILED (' . ($tcp['error'] ?: 'unknown error') . ')' . PHP_EOL;
    }
}
