<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

if(!AVideoPlugin::isEnabledByName('VideoHLS')){
    return die('VideoHLS disabled');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_end_flush();
$global['limitForUnlimitedVideos'] = -1;
$videos = video::getAllVideosLight("", false, true);
$count = 0;
$total = count($videos);
foreach ($videos as $value) {
    $count++;
    
    if(!Video::isValidDuration($value['duration'])){
        $v = new Video('', '', $value['id'], true);
        if(VideoHLS::updateHLSDurationIfNeed($v)){
            echo "[{$count}/{$total}] Success updated [{$value['id']}] ".$v->getDuration().PHP_EOL;
        }else{
            echo "[{$count}/{$total}] ERROR updated [{$value['id']}] ".$v->getDuration().PHP_EOL;
        }
    }else{
        echo "[{$count}/{$total}] No need to updated [{$value['id']}] {$value['duration']}".PHP_EOL;
    }
}
