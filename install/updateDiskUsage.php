<?php
//streamer config
require_once '../videos/configuration.php';

if(!isCommandLineInterface()){
    return die('Command Line only');
}

$total = Video::getTotalVideos("",false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, true);
$count = 0;
foreach ($videos as $value){
    $count++;
    $updated = Video::updateFilesize($value['id']);
    echo "{$count}/{$total} (".($updated?"success":"fail").") {$value['title']}".PHP_EOL;
}

die();




