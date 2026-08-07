<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    $configFile = '../videos/configuration.php';
    if (!file_exists($configFile)) {
        $configFile = '../../videos/configuration.php';
    }
    require_once $configFile;
}

//_error_log("HLS.php: session_id = ".  session_id()." IP = ".  getRealIpAddr()." URL = ".($actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));

_session_write_close();
if (empty($_GET['videoDirectory'])) {
    forbiddenPage("No directory set");
}

// Security: Reject path traversal attempts
$_GET['videoDirectory'] = str_replace('\\', '/', $_GET['videoDirectory']);
if (preg_match('/\.\./', $_GET['videoDirectory'])) {
    forbiddenPage("Invalid directory");
}
// Normalize: strip leading/trailing slashes, collapse multiple slashes
$_GET['videoDirectory'] = trim($_GET['videoDirectory'], '/');
$_GET['videoDirectory'] = preg_replace('#/+#', '/', $_GET['videoDirectory']);

$global['disableGeoblock'] = 1;
$video = Video::getVideoFromFileName($_GET['videoDirectory'], true);

$filename = Video::getPathToFile("{$_GET['videoDirectory']}".DIRECTORY_SEPARATOR."index.m3u8");

if (empty($video) || !file_exists($filename)) {
    header("Content-Type: text/plain");
    if (empty($video)) {
        $msg = "HLS.php: Video Not found videoDirectory=({$_GET['videoDirectory']})";
        rateLimitedLog('hls-video-not-found-' . md5($_GET['videoDirectory']), $msg);
        //echo $msg;
    }
    if (!file_exists($filename)) {
        $msg = "HLS.php: Video file do not exists ({$filename})";
        rateLimitedLog('hls-file-not-found-' . md5($filename), $msg);
        //echo $msg;
    }

    echo "#EXTM3U
#EXT-X-VERSION:3
#EXT-X-STREAM-INF:BANDWIDTH=300000
{$global['webSiteRootURL']}plugin/Live/view/loopBGHLS/res240/index.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=600000
{$global['webSiteRootURL']}plugin/Live/view/loopBGHLS/res360/index.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=1000000
{$global['webSiteRootURL']}plugin/Live/view/loopBGHLS/res480/index.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=2000000
{$global['webSiteRootURL']}plugin/Live/view/loopBGHLS/res720/index.m3u8";
    exit;
} else {
    if (filesize($filename) < 20) {
        Video::clearCache($video['id']);
    }
}

$_GET['file'] = Video::getPathToFile("{$_GET['videoDirectory']}".DIRECTORY_SEPARATOR."index.m3u8");
//var_dump($_GET['file']);exit;
$cachedPath = explode(DIRECTORY_SEPARATOR, $_GET['videoDirectory']);
// use the session-cache helper (not a raw $_SESSION write) since the session was
// already closed above; it degrades gracefully instead of silently never persisting
$hlsCacheName = 'hls_xsendfilePreVideoPlay_' . $cachedPath[0];
if (empty(ObjectYPT::getSessionCache($hlsCacheName, 0)) && empty($_GET['download'])) {
    AVideoPlugin::xsendfilePreVideoPlay();
    ObjectYPT::setSessionCache($hlsCacheName, 1);
}

$tokenIsValid = false;
if (!empty($_GET['token'])) {
    $secure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
    if ($secure) {
        $filenameParts = explode(".DIRECTORY_SEPARATOR.", $_GET['videoDirectory']);
        $fname = $filenameParts[0];
        $tokenIsValid = $secure->isTokenValid($_GET['token'], $fname, $_GET['videoDirectory']);
    }
} elseif (!empty($_GET['globalToken'])) {
    $tokenIsValid = verifyToken($_GET['globalToken']);
}
$newContent = '';
// if is using a CDN I can not check if the user is logged
// SECURITY COMPATIBILITY NOTE:
// isAVideoUserAgent() and isCDN() are client-controlled headers (User-Agent / CDN-Host)
// and are NOT authentication. A client sending these headers can bypass
// User::canWatchVideo() for this playlist/segment listing. This is a real, known
// gap (restricted HLS playlist/segment disclosure only - no credentials, tokens, or
// server files are exposed, and ?download=1/?playHLSasMP4=1 re-check
// CustomizeUser::canDownloadVideos() independently).
// It is intentionally retained: isAVideoUserAgent() is the documented trust signal
// for the Mobile App/Encoder/Streamer/Storage/Restreamer components that fetch this
// URL without a PHP session (see objects/functionsSecurity.php's autoCSRFGuard comment
// and VideoHLS::ignore()/VideoOffline::showOfflineVideo()), and isCDN() targets WWBN's
// external cdn.ypt.me edge infrastructure, neither of which can be safely removed or
// replaced from this repository alone. Do not strip these terms without first migrating
// those callers to a real shared-secret/signed-token mechanism (see isAVideoStreamer()'s
// md5($global['salt']) pattern for the precedent) and verifying playback still works.
if (isAVideoUserAgent() || isAVideoEncoderOnSameDomain() || $tokenIsValid || !empty($advancedCustom->videosCDN) || User::canWatchVideo($video['id']) || User::canWatchVideoWithAds($video['id']) || isCDN()) {
    if (!empty($_GET['download'])) {
        _error_log("Download file {$_GET['file']}");
        downloadHLS($_GET['file']);
    } elseif (!empty($_GET['playHLSasMP4'])) {
        playHLSasMP4($_GET['file']);
    } else {
        if (@filesize($_GET['file'])>20) {
            $filename = $_GET['file'];
        } else {
            $filename = pathToRemoteURL($filename);
        }
        if (!preg_match('/index.m3u8$/', $filename)) {
            $filename .= '/index.m3u8';
        }
        $context = stream_context_create(array('http' => array('timeout' => 30)));
        $content = file_get_contents($filename, false, $context);
        $content = preg_replace('/\.m3u8 +/', '.m3u8', $content);
        $newContent = str_replace('{$pathToVideo}', "{$global['webSiteRootURL']}videos/{$_GET['videoDirectory']}/../", $content);
        if (!empty($_GET['token'])) {
            $newContent = str_replace('/index.m3u8', "/index.m3u8?token={$_GET['token']}", $newContent);
        } elseif (!empty($_GET['globalToken'])) {
            $newContent = str_replace('/index.m3u8', "/index.m3u8?globalToken={$_GET['globalToken']}", $newContent);
        }
    }
} else {
    $newContent = "HLS.php Can not see video [{$video['id']}] ({$_GET['videoDirectory']}) ";
    $newContent .= $tokenIsValid ? "" : " tokenInvalid";
    $newContent .= User::canWatchVideo($video['id']) ? "" : " cannot watch ({$video['id']})";
    $newContent .= " " . date("Y-m-d H:i:s");
    rateLimitedLog('hls-cannot-see-video-' . md5(json_encode([$video['id'], $_GET['videoDirectory'], $tokenIsValid, User::getId(), getRealIpAddr()])), $newContent);
}
//header("Content-Type: text/plain");
header("Content-Type: application/vnd.apple.mpegurl");
echo $newContent;
