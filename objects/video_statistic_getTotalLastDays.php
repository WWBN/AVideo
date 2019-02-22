<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
session_write_close();
//getTotalLastDays($video_id, $numberOfDays)
$videos_id = $argv[1];
$numberOfDays = boolval($argv[2]);
$cacheFileName = $argv[3];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile)){
    return false;
}
file_put_contents($lockFile, 1);
$total = VideoStatistic::getTotalLastDays($video_id, $numberOfDays);
file_put_contents($cacheFileName, json_encode($total));
error_log(__FILE__." ".$cacheFileName.": done");
unlink($lockFile);