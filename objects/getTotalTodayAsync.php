<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
//getTotalTodayAsync($video_id) 
$video_id = $argv[1];
$cacheFileName = $argv[2];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile)){
    return false;
}
file_put_contents($lockFile, 1);
$total = Video::getTotalToday($video_id);
file_put_contents($cacheFileName, json_encode($total));
unlink($lockFile);