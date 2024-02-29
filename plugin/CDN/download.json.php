<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');
$resp = new stdClass();
$resp->error = true;
$resp->msg = '';
$resp->url = '';
$resp->lines = array();

$cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');

if (empty($cdnObj)) {
    $resp->msg = ('CDN Plugin is disabled');
    die(json_encode($resp));
}

if (empty($_REQUEST['token'])) {
    $resp->msg = ('Token is empty');
    die(json_encode($resp));
}

$token = decryptString($_REQUEST['token']);

if (empty($token)) {
    $resp->msg = ('Token is invalid');
    die(json_encode($resp));
}

$json = json_decode($token);

if(!empty($_REQUEST['delete'])){
    $json->format = 'mp4';
}

if (empty($json)) {
    $resp->msg = ('Error on decrypt token');
    die(json_encode($resp));
}

if ($json->valid < time()) {
    $resp->msg = ('Token expired');
    die(json_encode($resp));
}

if (empty($json->videos_id)) {
    $resp->msg = ('videos_id cannot be empty');
    die(json_encode($resp));
}

if (!User::canWatchVideo($json->videos_id)) {
    $resp->msg = ('You cannot watch this video');
    die(json_encode($resp));
}

$resp->deleteRemotely = false;
$resp->deleteLocally = false;
$resp->deleteProgress = false;   

$video = Video::getVideoLight($json->videos_id);
$resp->file = "{$video['filename']}/index.{$json->format}.log";
$convertedFile = "{$global['systemRootPath']}videos/{$video['filename']}/index.mp4";

$progressFile = getVideosDir() . $resp->file;
//$resp->progressFile = $progressFile;
$resp->progress = parseFFMPEGProgress($progressFile);

$resp->lines[] = __LINE__;
if (empty($_REQUEST['delete']) && $resp->progress->secondsOld < 30) {
    $resp->lines[] = __LINE__;
    $resp->msg = ("We are still processing the video, please wait");
    $resp->error = false;
}else if (empty($_REQUEST['delete']) && $resp->progress->secondsOld > 3600 && $resp->progress->progress < 100 ) {
    $resp->lines[] = __LINE__;
    $resp->msg = ("Somethinng is wrong with the transcoding process,it stops in {$resp->progress->progress}%");
    $resp->error = true;
}else if (!empty($_REQUEST['delete']) && file_exists($convertedFile)) {
    $resp->lines[] = __LINE__;
    if ($cdnObj->enable_storage) {
        $resp->lines[] = __LINE__;
        $remote_path = "{$video['filename']}/index.mp4";
        $client = CDNStorage::getStorageClient();
        $resp->deleteRemotely = $client->delete($remote_path);
    }
    $resp->deleteLocally = unlink($convertedFile);
    $resp->deleteProgress = unlink($progressFile);    

    $resp->error = empty($resp->deleteRemotely) && empty($resp->deleteLocally);
} else {
    $resp->lines[] = __LINE__;
    set_time_limit(7200); // 2 hours
    ini_set('max_execution_time', 7200);
    $url = CDNStorage::convertCDNHLSVideoToDownlaod($json->videos_id, $json->format);

    //$resp->convertedFile = $convertedFile;
    if (empty($url)) {
        $resp->lines[] = __LINE__;
        $resp->msg = ("CDN/download.json.php Error on get download URL for videos_id={$json->videos_id}, format={$json->format}");
        die(json_encode($resp));
    }
    $resp->error = false;
    //var_dump($url);exit;
    _error_log('download from CDN ' . $url);
    $resp->url = $url;
}

$resp->lines[] = __LINE__;

die(json_encode($resp));
