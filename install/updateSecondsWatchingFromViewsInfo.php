<?php
//streamer config
require_once '../videos/configuration.php';
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
    $newTotal = VideoStatistic::getSecondsWatchedFromVideos_id($value['id']);
    if($newTotal>$value['total_seconds_watching']){
        $sql = "UPDATE videos SET total_seconds_watching = ?, modified = now() WHERE id = ?";
        sqlDAL::writeSql($sql, "ii", [intval($newTotal), intval($value['id'])]);
        echo "{$count}/{$total} SUCCESS $newTotal>{$value['total_seconds_watching']}".PHP_EOL;
    }else{        
        echo "{$count}/{$total} SKIPP   $newTotal>{$value['total_seconds_watching']}".PHP_EOL;
    }
}

die();
