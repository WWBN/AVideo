<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
//getAllVideosAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
session_write_close();
$status = $argv[1];
$showOnlyLoggedUserVideos = $argv[2];
$ignoreGroup = $argv[3];
$videosArrayId = $argv[4];
$getStatistcs = $argv[5];
$showUnlisted = $argv[6];
$activeUsersOnly = $argv[7];
$_GET = object_to_array(json_decode($argv[8]));
$_POST = object_to_array(json_decode($argv[9]));
$cacheFileName = $argv[10];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile) && (time() - filemtime($lockFile) < 300)){ // 5 min limit
    error_log("getAllVideos: file locked ".$lockFile." filemtime(\$lockFile) = ".filemtime($lockFile)."| (time() - filemtime(\$lockFile))=".(time() - filemtime($lockFile)));
    return false;
}
file_put_contents($lockFile, 1);
$total = Video::getAllVideos($status, $showOnlyLoggedUserVideos, $ignoreGroup, $videosArrayId, $getStatistcs, $showUnlisted, $activeUsersOnly);
file_put_contents($cacheFileName, json_encode($total));
//error_log(__FILE__." ".$cacheFileName.": done");
unlink($lockFile);