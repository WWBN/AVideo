<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 3600); // 1 hour
set_time_limit(3600);
header('Content-Type: application/json');
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/MP4ThumbsAndGif/MP4ThumbsAndGif.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::canUpload()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
if (empty($_POST['video_id'])) {
    $obj->msg = __("Video ID Not found");
    die(json_encode($obj));
}

$videos_id = $_POST['video_id'];

if (!Video::canEdit($videos_id)) {
    $obj->msg = __("User can not edit this video");
    die(json_encode($obj));
}

$video = new Video("", "", $videos_id);

if (!$video->getFilename()) {
    $obj->msg = __("Video does not exist");
    die(json_encode($obj));
}

$plugin = AVideoPlugin::loadPluginIfEnabled("VimeoAPI");

$obj = $plugin->upload($videos_id);

if (empty($obj->error)) {
    $obj->msg = __("Error on Upload");
    die(json_encode($obj));
}

die(json_encode($obj));
