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

function getAudioConfiguration($source)
{
    switch (getRestreamProvider($source)) {
        case 'youtube':
            return '-c:a aac -b:a 128k -ar 44100 -ac 2 -profile:a aac_low -aac_coder twoloop ';
        case 'facebook':
            return '-c:a aac -ac 2 -ar 44100 -b:a 128k -profile:a aac_low ';
        case 'twitch':
            return '-c:a aac -b:a 160k -ar 48000 -ac 2 -profile:a aac_low ';
        default:
            return '-c:a aac -b:a 128k -ar 48000 -ac 2 -profile:a aac_low ';
    }
}
