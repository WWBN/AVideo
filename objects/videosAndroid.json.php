<?php
require_once '../videos/configuration.php';
require_once 'video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');
$videos = Video::getAllVideos("viewableNotAd");
$reversed = array_reverse($videos);
$videos = $reversed;
foreach ($videos as $key => $value) {
    unset($videos[$key]['password']);
    unset($videos[$key]['recoverPass']);
    $videos[$key]['Poster'] = "{$global['webSiteRootURL']}videos/".$videos[$key]['filename'].".jpg";
    $videos[$key]['Thumbnail'] = "{$global['webSiteRootURL']}videos/".$videos[$key]['filename']."_thumbs.jpg";
    $videos[$key]['VideoUrl'] = "{$global['webSiteRootURL']}videos/".$videos[$key]['filename'].".mp4";
    $videos[$key]['UserPhoto'] = "{$global['webSiteRootURL']}".$videos[$key]['photoURL'];
}


echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.count($videos).', "videos":'. json_encode($videos).'}';