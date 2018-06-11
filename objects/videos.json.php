<?php
error_reporting(0);
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');
$showOnlyLoggedUserVideos = true;
if (User::isAdmin()) {
    $showOnlyLoggedUserVideos = false;
}
$videos = Video::getAllVideos('', $showOnlyLoggedUserVideos, true);
$total = Video::getTotalVideos('', $showOnlyLoggedUserVideos, true);
foreach ($videos as $key => $value) {
    unset($value['password']);
    unset($value['recoverPass']);
    $name = empty($value['name'])?$value['user']:$value['name'];
    //$categories[$key]['comment'] = " <div class=\"commenterName\"><strong>{$name}</strong><div class=\"date sub-text\">{$value['created']}</div></div><div class=\"commentText\">". nl2br($value['comment'])."</div>";
    $videos[$key]['creator'] = '<div class="pull-left"><img src="'.User::getPhoto($value['users_id']).'" alt="" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong>'.$name.'</strong> <small>'.humanTiming(strtotime($value['videoCreation'])).'</small></div></div>';
    $videos[$key]['next_video'] = array();
    $videos[$key]['description'] = ($videos[$key]['description']);
    $videos[$key]['title'] = ($videos[$key]['title']);
    if(!empty($videos[$key]['next_videos_id'])){
        unset($_POST['searchPhrase']);
        $videos[$key]['next_video'] = Video::getVideo($videos[$key]['next_videos_id']);
    }
    $videos[$key]['videosURL'] = getVideosURL($videos[$key]['filename']);
}

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($videos).'}';
