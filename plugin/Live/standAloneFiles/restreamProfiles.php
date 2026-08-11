<?php

/**
 * Pure destination-profile helpers shared by the standalone restream endpoint and unit tests.
 */

function clearCommandURL($url)
{
    if (empty($url) || !is_string($url)) {
        return '';
    }

    $url = trim($url);
    $parts = parse_url($url);
    $scheme = strtolower($parts['scheme'] ?? '');
    if (!in_array($scheme, array('http', 'https', 'rtmp', 'rtmps'), true) || empty($parts['host'])) {
        error_log('clearCommandURL: Invalid URL format');
        return '';
    }

    // The URL is interpolated inside a double-quoted shell argument. Reject shell expansion,
    // quoting and control characters, but preserve RFC 3986 characters used by real provider
    // keys/query strings (notably %, + and @), which the old replacement silently removed.
    if (preg_match('/[\x00-\x20\x7f"`$\\\\|\[\]]/', $url)) {
        error_log('clearCommandURL: URL contains unsafe characters');
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
