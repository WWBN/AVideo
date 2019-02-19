<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
//getAllVideosAsync($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
$a = json_decode($argv[1]);
$cacheFileName = $argv[2];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile)){
    return false;
}
file_put_contents($lockFile, 1);
$total = Video::getAllVideos($a[0],$a[1],$a[2],$a[3],$a[4],$a[5],$a[6]);
file_put_contents($cacheFileName, json_encode($total));
unlink($lockFile);