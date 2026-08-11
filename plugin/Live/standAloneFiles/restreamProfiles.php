<?php

/**
 * Pure destination-profile helpers shared by the standalone restream endpoint and unit tests.
 */

// The FIFO absorbs short network stalls and retries recoverable failures. Keep onfail=abort so a
// permanent authentication/endpoint error terminates FFmpeg and remains visible to the watchdog
// instead of leaving a healthy-looking process that is no longer publishing anything.
const YOUTUBE_FIFO_OPTIONS = 'f=fifo:fifo_format=flv:format_opts=flvflags=no_duration_filesize:onfail=abort:queue_size=1024:drop_pkts_on_overflow=1:attempt_recovery=1:recovery_wait_time=1:restart_with_keyframe=1';

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

function getRestreamOutputTail($destinationUrl, $tlsVerify = '', $videoAudioMap = '-map 0:v:0? -map 0:a:0?')
{
    if (isYouTubeRestreamDestination($destinationUrl)) {
        error_log("Restreamer.json.php startRestream YouTube destination detected ({$destinationUrl}), using FFmpeg tee+fifo output isolation: " . YOUTUBE_FIFO_OPTIONS);
        // Map exactly one video/audio stream. YouTube rejects duplicate audio/video tracks, and
        // HLS inputs may expose metadata or alternate tracks when using the broad "-map 0". The
        // caller resolves $videoAudioMap to the highest-resolution rendition available (falling
        // back to stream index 0, the lowest-quality rendition, only if that detection fails) -
        // see getBestVideoAudioMap() in restreamer.json.php.
        // flvflags belongs to the tee child muxer, not to tee itself.
        // Values are interpolated directly here (not left as {placeholder}s for a later
        // str_replace pass) because PHP's str_replace() with an array of search terms does a
        // single left-to-right pass: a search term processed earlier in the array can never
        // match text introduced by a later replacement, so any {placeholder} still present in
        // this return value would be left in the command verbatim.
        return " {$tlsVerify} {$videoAudioMap} -f tee \"[" . YOUTUBE_FIFO_OPTIONS . "]{$destinationUrl}\"";
    }
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
