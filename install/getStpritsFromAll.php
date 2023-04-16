<?php

//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    echo 'Command Line only';
    exit;
}
if (!AVideoPlugin::loadPlugin('VideoThumbnails')) {
    echo 'Plugin VideoThumbnails not exists';
    exit;
}
$global['rowCount'] = 99999;
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;
foreach ($videos as $value) {
    $count++;
    if ($value['type'] !== 'video') {
        echo "createStprits: {$count}/{$total} skipp [{$value['id']}] type=[{$value['type']}] {$value['title']}" . PHP_EOL;
        continue;
    }
    $videoFileName = $value['filename'];
    echo "createStprits start: {$count}/{$total} [{$value['id']}]" . PHP_EOL;
    VideoThumbnails::createStpritsFileName($videoFileName, 1);
    echo "createStprits done: {$count}/{$total} (" . ($updated ? "success" : "fail") . ") [{$value['id']}] {$value['title']}" . PHP_EOL;
}

echo 'Done';
exit;
