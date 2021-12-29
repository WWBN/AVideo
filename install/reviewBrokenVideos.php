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


$sites_id_to_check = array();

foreach ($videos as $value) {
    if ($value['status'] !== Video::$statusBrokenMissingFiles) {
        continue;
    }
    $sites_id_to_check[] = $value['filename'];
    echo "{$key}/{$total} added to move {$global['webSiteRootURL']}v/{$value['id']} {$value['title']}" . PHP_EOL;
}

$total = count($sites_id_to_check);
foreach ($sites_id_to_check as $key => $value) {
    if(!empty($index) && $key<$index){
        continue;
    }
    echo "{$key}/{$total} Start check {$value} " . PHP_EOL;
    if(Video::isMediaFileMissing($value)){
        $sources = getVideosURL_V2($filename);
        echo "{$key}/{$total} is missing ". json_encode($sources) . PHP_EOL;
    }
}

echo PHP_EOL . " Done! " . PHP_EOL;
die();
