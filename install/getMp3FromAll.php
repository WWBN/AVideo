<?php
//streamer config
require_once __DIR__.'/../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
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
    $updated = convertVideoToMP3FileIfNotExists($value['id']);
    echo "getMP3: {$count}/{$total} (".($updated ? "success" : "fail").") [{$value['id']}] {$value['title']}".PHP_EOL;
}

die();
