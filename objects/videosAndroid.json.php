<?php
require_once '../videos/configuration.php';
require_once 'video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');
$videos = Video::getAllVideos("viewableNotAd");
$reversed = array_reverse($videos);
$videos = $reversed;
foreach ($videos as $key => $value) {
    $videos[$key]['Thumbnail'] = "http://".$_SERVER['SERVER_NAME']."/videos/".$videos[$key]['filename'].".jpg";
    $videos[$key]['VideoUrl'] = "http://".$_SERVER['SERVER_NAME']."/videos/".$videos[$key]['filename'].".mp4";
    $videos[$key]['UserPhoto'] = "http://".$_SERVER['SERVER_NAME']."/".$videos[$key]['photoURL'];
}


echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.count($videos).', "videos":'. json_encode($videos).'}';