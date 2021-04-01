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

session_write_close();
if (empty($_GET['videoDirectory'])) {
    forbiddenPage("No directory set");
}

$video = Video::getVideoFromFileName($_GET['videoDirectory'], true);

$filename = Video::getStoragePath() . "{$_GET['videoDirectory']}".DIRECTORY_SEPARATOR."index.m3u8";

if (empty($video) || !file_exists($filename)) {
    header("Content-Type: text/plain");
    if (empty($video)) {
        _error_log("HLS.php: Video Not found videoDirectory=({$_GET['videoDirectory']})");
    }
    if (!file_exists($filename)) {
        _error_log("HLS.php: Video file do not exists ({$filename})");
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

$_GET['file'] = Video::getStoragePath() . "{$_GET['videoDirectory']}".DIRECTORY_SEPARATOR."index.m3u8";
//var_dump($_GET['file']);exit;
$cachedPath = explode(DIRECTORY_SEPARATOR, $_GET['videoDirectory']);
if (empty($_SESSION['user']['sessionCache']['hls'][$cachedPath[0]]) && empty($_GET['download'])) {
    AVideoPlugin::xsendfilePreVideoPlay();
    $_SESSION['user']['sessionCache']['hls'][$cachedPath[0]] = 1;
}

$tokenIsValid = false;
if (!empty($_GET['token'])) {
    $secure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
    if ($secure) {
        $filenameParts = explode(".DIRECTORY_SEPARATOR.", $_GET['videoDirectory']); 
        $fname = $filenameParts[0];
        $tokenIsValid = $secure->isTokenValid($_GET['token'], $fname, $_GET['videoDirectory']);
    }
} else if (!empty($_GET['globalToken'])) {
    $tokenIsValid = verifyToken($_GET['globalToken']);
}
$newContent = "";
// if is using a CDN I can not check if the user is logged
if (isAVideoEncoderOnSameDomain() || $tokenIsValid || !empty($advancedCustom->videosCDN) || User::canWatchVideo($video['id'])) {

    if (!empty($_GET['download'])) {
        downloadHLS($_GET['file']);
    } else if (!empty($_GET['playHLSasMP4'])) {
        playHLSasMP4($_GET['file']);
    } else {
        $filename = pathToRemoteURL($filename);
        $content = file_get_contents($filename);
        $newContent = str_replace('{$pathToVideo}', "{$global['webSiteRootURL']}videos/{$_GET['videoDirectory']}/../", $content);
        if (!empty($_GET['token'])) {
            $newContent = str_replace('/index.m3u8', "/index.m3u8?token={$_GET['token']}", $newContent);
        } else if (!empty($_GET['globalToken'])) {
            $newContent = str_replace('/index.m3u8', "/index.m3u8?globalToken={$_GET['globalToken']}", $newContent);
        }
    }
} else {
    $newContent = "HLS.php Can not see video [{$video['id']}] ({$_GET['videoDirectory']}) ";
    $newContent .= $tokenIsValid ? "" : " tokenInvalid";
    $newContent .= User::canWatchVideo($video['id']) ? "" : " cannot watch ({$video['id']})";
    $newContent .= " " . date("Y-m-d H:i:s");
}
header("Content-Type: text/plain");
echo $newContent;
