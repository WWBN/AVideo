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
    $videos[$key]['VideoUrl'] = getVideosURL($videos[$key]['filename']);
    $videos[$key]['pageUrl'] = "{$global['webSiteRootURL']}video/".$videos[$key]['clean_title'];
    $videos[$key]['embedUrl'] = "{$global['webSiteRootURL']}videoEmbeded/".$videos[$key]['clean_title'];
    $videos[$key]['firstVideo'] = "";
    foreach ($videos[$key]['VideoUrl'] as $value2) {
        if($value2["type"] === 'video'){
            $videos[$key]['firstVideo'] = $value2["url"];
            break;
        }        
    }
    if(preg_match("/^videos/", $videos[$key]['photoURL'])){
        $videos[$key]['UserPhoto'] = "{$global['webSiteRootURL']}".$videos[$key]['photoURL'];
    }else{
        $videos[$key]['UserPhoto'] = $videos[$key]['photoURL'];
    }
    
}


echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.count($videos).', "videos":'. json_encode($videos).'}';