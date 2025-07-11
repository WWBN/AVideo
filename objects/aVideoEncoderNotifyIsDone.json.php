<?php
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
allowOrigin();
inputToRequest();
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$global['bypassSameDomainCheck'] = 1;
$_REQUEST['videos_id'] = intval($_REQUEST['videos_id']);

if (empty($_REQUEST)) {
    $obj->msg = __("Your POST data is empty, maybe your video file is too big for the host");
    _error_log($obj->msg);
    die(json_encode($obj));
}

useVideoHashOrLogin();
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to Notify Done: ") . print_r($_REQUEST, true);
    _error_log($obj->msg);
    die(json_encode($obj));
}

if (!Video::canEdit($_REQUEST['videos_id'])) {
    $obj->msg = __("Permission denied to edit a video: ") . print_r($_REQUEST, true);
    _error_log($obj->msg);
    die(json_encode($obj));
}

$file = getTmpDir("aVideoEncoderNotifyIsDone")."video_{$_REQUEST['videos_id']}";
if(file_exists($file)){
    $obj->msg = __("Notification already sent {$file}");
    _error_log($obj->msg);
    die(json_encode($obj));
}

file_put_contents($file, time());


Video::clearCache($_REQUEST['videos_id'], true);
// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", $_REQUEST['videos_id'], true);
$obj->video_id = $_REQUEST['videos_id'];


$video->setAutoStatus(Video::STATUS_ACTIVE);

$video_id = $video->save();

$video = new Video("", "", $video_id, true);

$obj->error = false;
$obj->video_id = $video_id;
Video::updateFilesize($video_id);
// delete original files if any
$originalFilePath =  Video::getStoragePath()."original_" . $video->getFilename();
if (file_exists($originalFilePath)) {
    unlink($originalFilePath);
}
_error_log("Video is done notified {$video_id}: " . $video->getTitle());
Video::clearCache($video_id, true);
AVideoPlugin::onEncoderNotifyIsDone($video_id);
AVideoPlugin::afterNewVideo($video_id);
die(json_encode($obj));

/*
_error_log(print_r($_REQUEST, true));
_error_log(print_r($_FILES, true));
var_dump($_REQUEST, $_FILES);
*/
