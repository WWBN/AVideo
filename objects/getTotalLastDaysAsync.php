<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
//getTotalTodayAsync($video_id) 
$video_id = $argv[1];
$numberOfDays = $argv[2];
$cacheFileName = $argv[3];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile) && (time() - filemtime($lockFile) < 300)){ // 5 min limit
    error_log("getTotalLastDaysAsync: file locked ".$lockFile." filemtime(\$lockFile) = ".filemtime($lockFile)."| (time() - filemtime(\$lockFile))=".(time() - filemtime($lockFile)));
    return false;
}
file_put_contents($lockFile, 1);
$total = VideoStatistic::getTotalLastDays($video_id, $numberOfDays);
file_put_contents($cacheFileName, json_encode($total));
unlink($lockFile);