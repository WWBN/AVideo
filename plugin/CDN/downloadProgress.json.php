<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');
$resp = new stdClass();
$resp->error = true;
$resp->progress = false;
$resp->msg = '';

if (!AVideoPlugin::isEnabledByName('CDN')) {
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

set_time_limit(7200); // 2 hours
ini_set('max_execution_time', 7200);
$resp->videos_id = $json->videos_id;
$resp->format = $json->format;
$progress = CDNStorage::convertCDNHLSVideoToDownloadProgress($json->videos_id, $json->format);

if(empty($progress)){
    $resp->msg = ("CDN/downloadProgress.json.php Error on get download URL for videos_id={$json->videos_id}, format={$json->format}");
    die(json_encode($resp));
}
$resp->error = false;
//var_dump($url);exit;
_error_log('download from CDN ' . json_encode($progress));

$resp->progress = $progress['progress'];
$resp->file = $progress['file'];

die(json_encode($resp));