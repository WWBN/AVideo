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
$obj->start = time();
$obj->finish = false;
$obj->results = array();

if (!User::isAdmin()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

$progressFile = $global['systemRootPath'] . 'videos/cache/vimeoUpload.log';
if(file_exists($progressFile)){
    unlink($progressFile);
}
$plugin = AVideoPlugin::loadPluginIfEnabled("VimeoAPI");
$videos = Video::getAllVideosLight("viewable", false, true, false);

$obj->msg = "Process start at ".  date("Y-m-d h:i:s"). " Total of ".count($videos)." Videos\n";

file_put_contents($progressFile, json_encode($obj));
foreach ($videos as $video) {
    $results = $plugin->upload($video['id']);
    $type = "SUCCESS: ";
    if($results->error){
        $type = "**ERROR: ";
    }
    file_put_contents($progressFile, $type.json_encode($results)."\n", FILE_APPEND);
}

die(json_encode($obj));
