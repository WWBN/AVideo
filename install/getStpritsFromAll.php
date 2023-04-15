<?php
//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}
if(!AVideoPlugin::loadPlugin('VideoThumbnails')){
    return die('Plugin VideoThumbnails not exists');
}
$global['rowCount'] = 99999;
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;
foreach ($videos as $value) {
    $count++;
    if($value['type'] !== 'video'){
        continue;
    }
    $videoFileName = $value['filename'];
    $this->createStprits($videoFileName);
    echo "createStprits: {$count}/{$total} (".($updated ? "success" : "fail").") [{$value['id']}] {$value['title']}".PHP_EOL;
}

die();
