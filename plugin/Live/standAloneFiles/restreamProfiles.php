<?php

/**
 * Pure destination-profile helpers shared by the standalone restream endpoint and unit tests.
 */

// Redacts a destination URL for logging: keeps scheme/host/path-prefix visible, hides the
// stream key portion (a secret) except for a short prefix, so support can diagnose format
// issues (unexpected char, length, etc.) without a full secret ending up in the logs.
function redactDestinationForLog($url)
{
    if (!is_string($url)) {
        return '(non-string)';
    }
    $len = strlen($url);
    $visible = substr($url, 0, 24);
    return $visible . (($len > 24) ? '...[REDACTED,total_len=' . $len . ']' : '');
}

function clearCommandURL($url)
{
    if (empty($url) || !is_string($url)) {
        error_log('clearCommandURL: empty or non-string URL');
        return '';
    }

    $url = trim($url);
    $parts = parse_url($url);
    $scheme = strtolower($parts['scheme'] ?? '');
    if (!in_array($scheme, array('http', 'https', 'rtmp', 'rtmps'), true) || empty($parts['host'])) {
        error_log('clearCommandURL: Invalid URL format, scheme=' . var_export($scheme, true)
            . ', host=' . var_export($parts['host'] ?? null, true)
            . ', value=' . redactDestinationForLog($url));
        return '';
    }

    // The URL is interpolated inside a double-quoted shell argument. Reject shell expansion,
    // quoting and control characters, but preserve RFC 3986 characters used by real provider
    // keys/query strings (notably %, + and @), which the old replacement silently removed.
    if (preg_match('/[\x00-\x20\x7f"`$\\\\|\[\]]/', $url, $m, PREG_OFFSET_CAPTURE)) {
        error_log('clearCommandURL: URL contains unsafe characters, offendingCharHex='
            . bin2hex($m[0][0]) . ', offset=' . $m[0][1] . ', value=' . redactDestinationForLog($url));
        return '';
    }

    return $url;
}

function buildAutomaticRestreamDestination($streamUrl, $streamKey, $expectedScheme = '')
{
    if (!is_string($streamUrl) || !is_string($streamKey)) {
        return '';
    }

    $streamUrl = rtrim(trim($streamUrl), '/');
    $streamKey = ltrim(trim($streamKey), '/');
    if ($streamUrl === '' || $streamKey === '') {
        return '';
    }

    $destination = clearCommandURL("{$streamUrl}/{$streamKey}");
    if ($destination === '') {
        return '';
    }

    if ($expectedScheme !== '' && strtolower((string) parse_url($destination, PHP_URL_SCHEME)) !== strtolower($expectedScheme)) {
        return '';
    }

    return $destination;
}

/**
 * Convert the automatic-provider response into a preferred destination and an optional
 * downgrade destination. The legacy stream_url remains the primary value for providers
 * that do not return the additive rtmps_stream_url field.
 */
function getAutomaticRestreamDestinationPair($response)
{
    $result = array(
        'primary' => '',
        'fallback' => '',
    );

    if (!is_object($response) && !is_array($response)) {
        return $result;
    }

    $values = (array) $response;
    $streamKey = isset($values['stream_key']) ? $values['stream_key'] : '';
    $streamUrl = isset($values['stream_url']) ? $values['stream_url'] : '';
    $rtmpsStreamUrl = isset($values['rtmps_stream_url']) ? $values['rtmps_stream_url'] : '';

    $secureDestination = buildAutomaticRestreamDestination($rtmpsStreamUrl, $streamKey, 'rtmps');
    $legacyDestination = buildAutomaticRestreamDestination($streamUrl, $streamKey);

    if ($secureDestination !== '') {
        $result['primary'] = $secureDestination;
        if (
            $legacyDestination !== ''
            && strtolower((string) parse_url($legacyDestination, PHP_URL_SCHEME)) === 'rtmp'
            && $legacyDestination !== $secureDestination
        ) {
            $result['fallback'] = $legacyDestination;
        }
    } else {
        $result['primary'] = $legacyDestination;
    }

    return $result;
}

/**
 * Only allow the secure-to-cleartext downgrade for the matching YouTube destination and
 * only when the caller has positively identified an initial connection failure.
 */
function shouldAttemptAutomaticRestreamFallback($primaryDestination, $fallbackDestination, $initialConnectionFailed)
{
    if (!$initialConnectionFailed) {
        return false;
    }

    $primary = parse_url((string) $primaryDestination);
    $fallback = parse_url((string) $fallbackDestination);
    if (!is_array($primary) || !is_array($fallback)) {
        return false;
    }
    if (empty($primary['scheme']) || empty($fallback['scheme'])) {
        return false;
    }
    if (strtolower($primary['scheme']) !== 'rtmps' || strtolower($fallback['scheme']) !== 'rtmp') {
        return false;
    }
    if (!isYouTubeRestreamDestination($primaryDestination) || !isYouTubeRestreamDestination($fallbackDestination)) {
        return false;
    }

    return ($primary['path'] ?? '') === ($fallback['path'] ?? '')
        && ($primary['query'] ?? '') === ($fallback['query'] ?? '');
}

function automaticRestreamLaunchSamplesIndicateFailure(array $firstSample, array $secondSample)
{
    // An unreachable/ambiguous executor must never cause a cleartext downgrade because the
    // primary process may still be running even though its status could not be retrieved.
    if (empty($firstSample['known']) || empty($secondSample['known'])) {
        return false;
    }
    if (empty($secondSample['running'])) {
        return true;
    }

    $firstModified = isset($firstSample['modified']) ? intval($firstSample['modified']) : 0;
    $secondModified = isset($secondSample['modified']) ? intval($secondSample['modified']) : 0;

    // A healthy FFmpeg process continually updates its progress log. A process that remains
    // alive while its TLS handshake is stuck does not, so unchanged progress is also a failure.
    return $secondModified <= $firstModified;
}

function restreamHostMatches($host, array $domains)
{
    $host = strtolower(rtrim((string) $host, '.'));
    foreach ($domains as $domain) {
        $domain = strtolower($domain);
        if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
            return true;
        }
    }
    return false;
}

function getRestreamProvider($url)
{
    $host = parse_url((string) $url, PHP_URL_HOST);
    if (restreamHostMatches($host, array('youtube.com', 'youtu.be'))) {
        return 'youtube';
    }
    if (restreamHostMatches($host, array('facebook.com', 'fbcdn.net'))) {
        return 'facebook';
    }
    // Current Twitch ingest hosts use contribute.live-video.net; keep legacy domains too.
    if (restreamHostMatches($host, array('twitch.tv', 'live-video.net', 'contribute.video.net'))) {
        return 'twitch';
    }
    if (restreamHostMatches($host, array('linkedin.com'))) {
        return 'linkedin';
    }
    return 'generic';
}

function isYouTubeRestreamDestination($url)
{
    return getRestreamProvider($url) === 'youtube';
}

// YouTube, Facebook, Twitch and generic RTMP(S) destinations all use the same plain "-f flv"
// output - a prior tee+fifo isolation attempt specifically for YouTube caused it to reject the
// stream while every other provider kept working fine with this exact same output block, so
// YouTube gets no special-cased muxer/mapping here anymore.
function getRestreamOutputTail($destinationUrl, $tlsVerify = '')
{
    return " -flvflags no_duration_filesize -f flv {$tlsVerify} \"{$destinationUrl}\"";
}

function getRestreamTlsOptions($destinationUrl, $tcurl)
{
    if (strtolower((string) parse_url($destinationUrl, PHP_URL_SCHEME)) !== 'rtmps') {
        return '';
    }

    // RTMPS without peer verification is encrypted but does not authenticate the server.
    // Peer verification is intentionally always disabled: FFmpeg builds vary in how (or
    // whether) they can locate a usable CA bundle, so requiring verification broke restream
    // to destinations like Facebook on hosts where it otherwise worked fine.
    return "-tls_verify 0 -rtmp_tcurl \"{$tcurl}\" ";
}

function getAudioConfiguration($source)
{
    switch (getRestreamProvider($source)) {
        case 'youtube':
            return '-c:a aac -b:a 128k -ar 44100 -ac 2 -profile:a aac_low -aac_coder twoloop ';
        case 'facebook':
            return '-c:a aac -ac 2 -ar 44100 -b:a 128k -profile:a aac_low ';
        case 'twitch':
            return '-c:a aac -b:a 160k -ar 48000 -ac 2 -profile:a aac_low ';
        case 'linkedin':
            return '-c:a aac -b:a 128k -ar 48000 -ac 2 -profile:a aac_low ';
        default:
            return '-c:a aac -b:a 128k -ar 48000 -ac 2 -profile:a aac_low ';
    }
}

/**
 * Return a conservative 30 fps ingest profile tailored to the destination.
 * Each platform transcodes the incoming stream for its viewers, so matching its own
 * published ingest recommendation per resolution is what keeps delivery stable, instead of
 * forcing one bitrate on every output or requiring a manual bitrate per destination.
 */
function getRestreamVideoProfile($destinationUrl, $resolution = 720)
{
    $provider = getRestreamProvider($destinationUrl);
    $resolution = (int) $resolution;
    if (!in_array($resolution, array(480, 720, 1080), true)) {
        $resolution = 720;
    }

    $dimensions = array(
        480 => array('width' => 854, 'height' => 480),
        720 => array('width' => 1280, 'height' => 720),
        1080 => array('width' => 1920, 'height' => 1080),
    );

    // Video bitrate in kbit/s for H.264 at 30 fps. Each provider is pinned to the SAME value
    // per resolution (1500/3000/6000) for consistency and to favor smooth delivery over hitting
    // a platform's documented ceiling (YouTube's own 1080p ceiling is 10000, but its live Stream
    // Health tool routinely recommends ~6800 for real ingest links, so 6000 is used instead).
    // Twitch's 1080p is the one deliberate exception, kept at Twitch's own documented CBR spec
    // (help.twitch.tv/s/article/broadcasting-guidelines: 1080p30 = 4500) rather than raised to
    // match the rest. "generic" (destination/provider unknown) stays the lowest of all tiers.
    $bitrates = array(
        'youtube' => array(480 => 1500, 720 => 3000, 1080 => 6000),
        'facebook' => array(480 => 1500, 720 => 3000, 1080 => 6000),
        'twitch' => array(480 => 1500, 720 => 3000, 1080 => 4500),
        'linkedin' => array(480 => 1500, 720 => 3000, 1080 => 6000),
        'generic' => array(480 => 1200, 720 => 2800, 1080 => 4500),
    );

    $bitrate = $bitrates[$provider][$resolution] ?? $bitrates['generic'][$resolution];
    if ($provider === 'linkedin') {
        // LinkedIn explicitly recommends Baseline when Main/High causes ingest issues.
        $videoProfile = 'baseline';
    } else {
        $videoProfile = in_array($provider, array('youtube', 'twitch'), true) ? 'high' : 'main';
    }

    return array(
        'provider' => $provider,
        'resolution' => $resolution,
        'width' => $dimensions[$resolution]['width'],
        'height' => $dimensions[$resolution]['height'],
        'fps' => 30,
        'gop' => 60,
        'bitrateKbps' => $bitrate,
        // 1.5x (not 2x) keeps the VBV tight so the measured delivered bitrate stays close to
        // the target instead of bursting well above each platform's recommended ingest rate.
        'bufsizeKbps' => (int) round($bitrate * 1.5),
        'videoProfile' => $videoProfile,
        'bframes' => $videoProfile === 'baseline' ? 0 : 2,
    );
}

function getRestreamVideoConfiguration($destinationUrl, $resolution = 720)
{
    $profile = getRestreamVideoProfile($destinationUrl, $resolution);

    return " -c:v libx264 -preset veryfast"
        . " -profile:v {$profile['videoProfile']} -level:v 4.0"
        . " -pix_fmt yuv420p -r {$profile['fps']}"
        . " -g {$profile['gop']} -keyint_min {$profile['gop']} -sc_threshold 0"
        . " -bf {$profile['bframes']} -refs 1"
        . " -x264-params \"nal-hrd=cbr:force-cfr=1\""
        . " -b:v {$profile['bitrateKbps']}k -minrate {$profile['bitrateKbps']}k"
        . " -maxrate {$profile['bitrateKbps']}k -bufsize {$profile['bufsizeKbps']}k"
        . " -vf \"scale={$profile['width']}:{$profile['height']}:force_original_aspect_ratio=decrease,"
        . "pad={$profile['width']}:{$profile['height']}:(ow-iw)/2:(oh-ih)/2,format=yuv420p\" ";
}

/**
 * Applies an explicit, reversible RTMP/RTMPS override coming from the (optional) admin-only
 * "restreamForceProtocolForYouTubeTest" Live plugin setting. Only ever narrows the pair that
 * getAutomaticRestreamDestinationPair() already computed - never invents a destination, never
 * touches encoding parameters, and is a no-op ($forceProtocol === '') by default.
 */
function applyRestreamProtocolTestOverride(array $destinations, $forceProtocol)
{
    $forceProtocol = strtolower((string) $forceProtocol);
    if ($forceProtocol !== 'rtmp' && $forceProtocol !== 'rtmps') {
        return $destinations;
    }

    $primaryScheme = strtolower((string) parse_url((string) $destinations['primary'], PHP_URL_SCHEME));
    if ($forceProtocol === $primaryScheme) {
        return $destinations;
    }

    if ($forceProtocol === 'rtmp' && !empty($destinations['fallback'])
        && strtolower((string) parse_url($destinations['fallback'], PHP_URL_SCHEME)) === 'rtmp') {
        return array('primary' => $destinations['fallback'], 'fallback' => '');
    }

    // Requested rtmps but only an rtmp destination is available (or vice versa): nothing safe
    // to switch to, keep the original pair rather than guessing a URL.
    return $destinations;
}

/**
 * Central redaction for destination host:port (never the path/stream key). Used by structured
 * lifecycle logging so log lines can name the destination without ever containing a credential.
 */
function getDestinationHostPort($url)
{
    $parts = is_string($url) ? parse_url($url) : false;
    $scheme = strtolower($parts['scheme'] ?? '');
    $host = $parts['host'] ?? '';
    $defaultPorts = array('rtmp' => 1935, 'rtmps' => 443, 'http' => 80, 'https' => 443);
    $port = $parts['port'] ?? ($defaultPorts[$scheme] ?? null);

    return array(
        'protocol' => $scheme,
        'host' => $host,
        'port' => $port,
    );
}

/**
 * Centralized secret redaction for arbitrary free text (a full FFmpeg command line, a raw
 * stderr snippet, etc.) - as opposed to redactDestinationForLog(), which redacts a single known
 * destination URL. Used everywhere a string that MIGHT embed a stream key/token/password/
 * Authorization header is about to be written to a log.
 */
function redactSecretsInText($text)
{
    if (!is_string($text)) {
        return '(non-string)';
    }

    // Any rtmp(s)/http(s) URL: keep scheme+host, redact everything from the path onward, since
    // the stream key/token normally lives in the path or query string.
    $text = preg_replace_callback(
        '#(rtmps?|https?)://[^\s"\'\\\\]+#i',
        function ($m) {
            return redactDestinationForLog($m[0]);
        },
        $text
    );

    // Query-string style secrets that may appear outside of a URL match above (e.g. logged
    // separately), and Authorization headers.
    $text = preg_replace(
        '/((?:^|[?&\s])(?:key|token|secret|password|pass|auth|apisecret|access_token)\s*[=:]\s*)([^&\s"\']+)/i',
        '$1[REDACTED]',
        $text
    );
    $text = preg_replace('/(Authorization:\s*Bearer\s+)\S+/i', '$1[REDACTED]', $text);

    return $text;
}

/**
 * Parses the LAST FFmpeg periodic progress line found in $text (frame=/fps=/bitrate=/time=/
 * speed=/drop=/dup=). Fields are matched independently since their presence/order varies by
 * FFmpeg version and by whether stream-copy or re-encoding is used. Returns null when no
 * progress line is found at all (as opposed to a line with some fields missing).
 */
function parseFfmpegProgressLine($text)
{
    if (!is_string($text) || $text === '') {
        return null;
    }

    $lines = explode("\n", $text);
    for ($i = count($lines) - 1; $i >= 0; $i--) {
        $line = $lines[$i];
        if (strpos($line, 'frame=') === false || strpos($line, 'time=') === false) {
            continue;
        }

        $result = array(
            'frame' => null,
            'fps' => null,
            'bitrateKbps' => null,
            'time' => null,
            'speed' => null,
            'drop' => null,
            'dup' => null,
        );
        if (preg_match('/frame=\s*(\d+)/', $line, $m)) {
            $result['frame'] = (int) $m[1];
        }
        if (preg_match('/fps=\s*([\d.]+)/', $line, $m)) {
            $result['fps'] = (float) $m[1];
        }
        if (preg_match('/bitrate=\s*([\d.]+)kbits\/s/i', $line, $m)) {
            $result['bitrateKbps'] = (float) $m[1];
        }
        if (preg_match('/time=(\S+)/', $line, $m)) {
            $result['time'] = $m[1];
        }
        if (preg_match('/speed=\s*([\d.]+)x/i', $line, $m)) {
            $result['speed'] = (float) $m[1];
        }
        if (preg_match('/drop=\s*(\d+)/', $line, $m)) {
            $result['drop'] = (int) $m[1];
        }
        if (preg_match('/dup=\s*(\d+)/', $line, $m)) {
            $result['dup'] = (int) $m[1];
        }
        return $result;
    }
    return null;
}

/**
 * Best-effort failure taxonomy for a destination disconnection, from already-collected
 * evidence only (no I/O here, so this stays a pure/unit-testable function). $context flags,
 * when known, always take priority over guessing from stderr text:
 *   - intentionalStop: the application itself issued the stop/kill (never a real failure)
 *   - oomEvidence: the diagnostic snapshot found an OOM-killer hit for this process
 *   - dnsFailed: a live DNS check for the destination host failed
 */
function classifyFfmpegFailure($stderrTail, array $context = array())
{
    if (!empty($context['intentionalStop'])) {
        return 'killed_by_application';
    }
    if (!empty($context['oomEvidence'])) {
        return 'resource_exhaustion';
    }
    if (!empty($context['dnsFailed'])) {
        return 'dns_failure';
    }

    $text = is_string($stderrTail) ? $stderrTail : '';

    if (preg_match('/Could not resolve host|Name or service not known|Temporary failure in name resolution|nodename nor servname/i', $text)) {
        return 'dns_failure';
    }
    if (preg_match('/session has been invalidated|SSL_read|SSL_write|SSL error|TLS.{0,20}(error|failed|handshake)|certificate verify failed|tls_verify/i', $text)) {
        return 'tls_failure';
    }
    if (preg_match('/Connection timed out|Operation timed out|rw_timeout|I\/O timeout/i', $text)) {
        return 'timeout';
    }
    if (preg_match('/Broken pipe|Error muxing a packet|Error writing trailer|Error submitting a packet to the muxer|Error in the push function|Error closing file/i', $text)) {
        return 'output_broken_pipe';
    }
    if (preg_match('/(Invalid data found when processing input|Server returned 404|Server returned 403|error reading header).{0,80}(m3u8|\.ts)/i', $text)) {
        return 'input_failure';
    }
    if (preg_match('/Killed process|Out of memory|oom-killer/i', $text)) {
        return 'resource_exhaustion';
    }

    return 'unknown';
}

/**
 * Exponential backoff with jitter for the watchdog's reconnect delay, e.g. 2,5,10,20,30s.
 * $attemptNumber is 1-based: the attempt about to be made (count(pastAttempts) + 1).
 * $randomFn defaults to mt_rand and is injectable so tests can be deterministic.
 */
function computeRestreamBackoffDelaySeconds($attemptNumber, array $sequence, $jitterPercent = 0, $randomFn = null)
{
    $sequence = array_values(array_filter(array_map('intval', $sequence), function ($v) {
        return $v > 0;
    }));
    if (empty($sequence)) {
        $sequence = array(2, 5, 10, 20, 30);
    }

    $attemptNumber = max(1, (int) $attemptNumber);
    $index = min($attemptNumber, count($sequence)) - 1;
    $base = $sequence[$index];

    $jitterPercent = max(0, (int) $jitterPercent);
    if ($jitterPercent > 0) {
        $randomFn = $randomFn ?: 'mt_rand';
        $jitterRange = (int) round($base * $jitterPercent / 100);
        if ($jitterRange > 0) {
            $base += call_user_func($randomFn, -$jitterRange, $jitterRange);
        }
    }

    return max(1, (int) $base);
}
