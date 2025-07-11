<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);

$checkAll = intval(@$argv[1]);

echo "Path: {$path}" . PHP_EOL;

$sites_id_to_check = [];

foreach ($videos as $value) {
    if ($value['status'] !== Video::STATUS_BROKEN_MISSING_FILES && empty($checkAll)) {
        continue;
    }
    if ($value['type'] !== Video::$videoTypeVideo) {
        continue;
    }
    $sites_id_to_check[] = $value['id'];
    echo "{$key}/{$total} added to review {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
}

$total = count($sites_id_to_check);
foreach ($sites_id_to_check as $key => $value) {
    if (!empty($index) && $key<$index) {
        continue;
    }
    $video = new Video('', '', $value);
    $filename = $video->getFilename();

    echo "{$key}/{$total} Start check {$filename} " . PHP_EOL;
    if (Video::isMediaFileMissing($filename)) {
        $sources = getVideosURL_V2($filename);
        echo "{$key}/{$total} is missing ". json_encode($sources) . PHP_EOL;
    } else if($video->getStatus()===Video::STATUS_BROKEN_MISSING_FILES){
        $video->setStatus(Video::STATUS_ACTIVE);
        echo "{$key}/{$total} is set to active " . PHP_EOL;
    }
}

echo PHP_EOL . " Done! " . PHP_EOL;
die();
