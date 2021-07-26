<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
User::loginFromRequest();
session_write_close();
header('Content-Type: application/json');
$showOnlyLoggedUserVideos = true;
if (Permissions::canModerateVideos()) {
    $showOnlyLoggedUserVideos = false;
}
$showUnlisted = false;
$activeUsersOnly = true;
if (!empty($_REQUEST['showAll'])) {
    $showUnlisted = true;
    if (Permissions::canModerateVideos()) {
        $activeUsersOnly = false;
    }
}

if (empty($_REQUEST['current'])) {
    $_REQUEST['current'] = getCurrentPage();
}

$videos = Video::getAllVideos('', $showOnlyLoggedUserVideos, true, array(), false, $showUnlisted, $activeUsersOnly);
$total = Video::getTotalVideos('', $showOnlyLoggedUserVideos, true, $showUnlisted, $activeUsersOnly);
foreach ($videos as $key => $value) {
    unset($value['password'], $value['recoverPass']);
    $name = empty($value['name'])?$value['user']:$value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="'.User::getPhoto($value['users_id']).'" alt="User Photo" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong>'.$name.'</strong>' . User::getEmailVerifiedIcon($value['users_id']) . ' <small>'.humanTiming(strtotime($value['videoCreation'])).'</small></div></div>';
    $videos[$key]['next_video'] = array();
    $videos[$key]['description'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $videos[$key]['description']);
    $videos[$key]['title'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $videos[$key]['title']);
    $videos[$key]['clean_title'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $videos[$key]['clean_title']);
    $videos[$key]['typeLabels'] = Video::getVideoTypeLabels($videos[$key]['filename']);
    $videos[$key]['maxResolution'] = Video::getHigestResolution($videos[$key]['filename']);
    if (!empty($videos[$key]['next_videos_id'])) {
        unset($_POST['searchPhrase']);
        $videos[$key]['next_video'] = Video::getVideo($videos[$key]['next_videos_id']);
    }
    if ($videos[$key]['type'] == 'article') {
        $videos[$key]['videosURL'] = getVideosURLArticle($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'image') {
        $videos[$key]['videosURL'] = getVideosURLIMAGE($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'zip') {
        $videos[$key]['videosURL'] = getVideosURLZIP($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'pdf') {
        $videos[$key]['videosURL'] = getVideosURLPDF($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'audio') {
        $videos[$key]['videosURL'] = getVideosURLAudio($videos[$key]['filename']);
    } else {
        $videos[$key]['videosURL'] = getVideosURL($videos[$key]['filename']);
    }
    unset($videos[$key]['password'], $videos[$key]['recoverPass']);
}

$obj = new stdClass();
$obj->users_id = User::getId();
$obj->current = getCurrentPage();
$obj->rowCount = getRowCount();
$obj->total = $total;
$obj->rows = $videos;

die(json_encode($obj));
exit;
