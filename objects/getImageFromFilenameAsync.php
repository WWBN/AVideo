<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
//getTotalVideosInfo($status = "viewable", $showOnlyLoggedUserVideos = false, 
//$ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false)
session_write_close();
$filename = $argv[1];
$type = $argv[2];
$cacheFileName = $argv[3];
$lockFile = $cacheFileName.".lock";
if(file_exists($lockFile)){
    return false;
}
file_put_contents($lockFile, 1);
$total = Video::getImageFromFilename_($filename,$type);
file_put_contents($cacheFileName, json_encode($total));
//error_log(__FILE__." ".$cacheFileName.": done");
unlink($lockFile);