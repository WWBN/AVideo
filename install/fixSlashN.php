<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$global['limitForUnlimitedVideos'] = -1;
$videos = video::getAllVideosLight("", false, true);
$count = 0;
foreach ($videos as $value) {
    $count++;
    $newDescription = str_replace('\n', '', $value['description']);
    if($newDescription !== $value['description']){
        echo "Change ($count) [{$value['id']}]{$value['title']} ******".PHP_EOL;
        $video = new Video('','',$value['id']);
        $video->setDescription($newDescription);
        $video->save(false, true);
    }else{
        //echo "\n skip ($count) [{$value['id']}]{$value['title']} ******\n";
    }
}
