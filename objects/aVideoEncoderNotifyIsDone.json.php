<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$_POST['videos_id'] = intval($_POST['videos_id']);

if (empty($_POST)) {
    $obj->msg = __("Your POST data is empty, maybe your video file is too big for the host");
    _error_log($obj->msg);
    die(json_encode($obj));
}

useVideoHashOrLogin();
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to Notify Done: ") . print_r($_POST, true);
    _error_log($obj->msg);
    die(json_encode($obj));
}

if(!Video::canEdit($_POST['videos_id'])){
    $obj->msg = __("Permission denied to edit a video: ") . print_r($_POST, true);
    _error_log($obj->msg);
    die(json_encode($obj));
}
Video::clearCache($_POST['videos_id']);
// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", $_POST['videos_id']);
$obj->video_id = $_POST['videos_id'];


$video->setAutoStatus(Video::$statusActive);

$video_id = $video->save();

$video = new Video("", "", $video_id);

if($video->getStatus() === Video::$statusActive){
    AVideoPlugin::afterNewVideo($video_id);
}

if($video->getType() == 'audio' && AVideoPlugin::isEnabledByName('MP4ThumbsAndGif')){
    $videoFileName = $video->getFilename();
    MP4ThumbsAndGif::getImage($videoFileName, 'jpg', $video_id);
    Video::deleteThumbs($videoFileName);
    Video::deleteGifAndWebp($videoFileName);
}

$obj->error = false;
$obj->video_id = $video_id;
Video::updateFilesize($video_id);
// delete original files if any
$originalFilePath =  Video::getStoragePath()."original_" . $video->getFilename();
if(file_exists($originalFilePath)){
    unlink($originalFilePath);
}
_error_log("Video is done notified {$video_id}: " . $video->getTitle());
Video::clearCache($video_id);
die(json_encode($obj));

/*
_error_log(print_r($_POST, true));
_error_log(print_r($_FILES, true));
var_dump($_POST, $_FILES);
*/
