<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
session_write_close();

if (!AVideoPlugin::isEnabledByName('CDN')) {
    forbiddenPage('CDN Plugin is disabled');
}

if (empty($_REQUEST['token'])) {
    forbiddenPage('Token is empty');
}

$token = decryptString($_REQUEST['token']);

if (empty($token)) {
    forbiddenPage('Token is invalid');
}

$json = json_decode($token);

if (empty($json)) {
    forbiddenPage('Error on decrypt token');
}

if ($json->valid < time()) {
    forbiddenPage('Token expired');
}

if (empty($json->videos_id)) {
    forbiddenPage('videos_id cannot be empty');
}

if (!User::canWatchVideo($json->videos_id)) {
    forbiddenPage('You cannot watch this video');
}

set_time_limit(7200); // 2 hours
ini_set('max_execution_time', 7200);

$url = CDNStorage::convertCDNHLSVideoToDownlaod($json->videos_id, $json->format);

if(empty($url)){
    forbiddenPage("Error on get download URL for videos_id={$json->videos_id}, format={$json->format}");
}
//var_dump($url);exit;
_error_log('download from CDN ' . $url);
header("Location: {$url}");
exit;