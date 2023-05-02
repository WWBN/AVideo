<?php

//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
if (!isCommandLineInterface()) {
    echo 'Command Line only';
    exit;
}
if (!AVideoPlugin::loadPlugin('MP4ThumbsAndGif')) {
    echo 'Plugin do not exists';
    exit;
}
$global['rowCount'] = 99999;
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;
foreach ($videos as $value) {
    $count++;
    $videos_id = $value['id'];
    if ($value['type'] == 'video') {
        MP4ThumbsAndGif::getImageIfNotExists($videos_id, 'jpg');
        echo "MP4ThumbsAndGif jpg done: {$count}/{$total} [{$value['id']}] {$value['title']}" . PHP_EOL;
        MP4ThumbsAndGif::getImageIfNotExists($videos_id, 'gif');
        echo "MP4ThumbsAndGif gif done: {$count}/{$total} [{$value['id']}] {$value['title']}" . PHP_EOL;
        MP4ThumbsAndGif::getImageIfNotExists($videos_id, 'webp');
        echo "MP4ThumbsAndGif webp done: {$count}/{$total} [{$value['id']}] {$value['title']}" . PHP_EOL;
    } else if ($value['type'] == 'linkVideo' || $value['type'] == 'embed') {
        MP4ThumbsAndGif::getImageIfNotExists($videos_id, 'jpg');
        echo "MP4ThumbsAndGif jpg done: {$count}/{$total} [{$value['id']}] {$value['title']}" . PHP_EOL;
    } else if ($value['type'] == 'audio') {
        MP4ThumbsAndGif::getImageIfNotExists($videos_id, 'jpg');
        echo "MP4ThumbsAndGif audio spectrum done: {$count}/{$total} [{$value['id']}] {$value['title']}" . PHP_EOL;
    }
}

echo 'Done';
exit;
