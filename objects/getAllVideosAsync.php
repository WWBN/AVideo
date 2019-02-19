<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
//getAllVideosAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
$status = $argv[0];
$showOnlyLoggedUserVideos = $argv[1];
$ignoreGroup = $argv[2];
$videosArrayId = $argv[3];
$getStatistcs = $argv[4];
$showUnlisted = $argv[5];
$activeUsersOnly = $argv[6];
$cacheFileName = $argv[7];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile)){
    return false;
}
file_put_contents($lockFile, 1);
$total = Video::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs, $showUnlisted, $activeUsersOnly);
file_put_contents($cacheFileName, json_encode($total));
unlink($lockFile);