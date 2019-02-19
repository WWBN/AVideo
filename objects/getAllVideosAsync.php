<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
//getAllVideosAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
$status = $argv[1];
$showOnlyLoggedUserVideos = $argv[2];
$ignoreGroup = $argv[3];
$videosArrayId = $argv[4];
$getStatistcs = $argv[5];
$showUnlisted = $argv[6];
$activeUsersOnly = $argv[7];
$cacheFileName = $argv[8];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile)){
    return false;
}
file_put_contents($lockFile, 1);
$total = Video::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs, $showUnlisted, $activeUsersOnly);
file_put_contents($cacheFileName, json_encode($total));
unlink($lockFile);