<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
User::loginFromRequest();
session_write_close();
header('Content-Type: application/json');
$start = microtime(true);

$TimeLogLimit = 1;
$timeLogName = TimeLogStart("videos.json.php");

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

TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
if (empty($_REQUEST['current'])) {
    $_REQUEST['current'] = getCurrentPage();
}

$status = '';
if (!empty($_REQUEST['status'])) {
    if (!empty(Video::$statusDesc[$_REQUEST['status']])) {
        $status = $_REQUEST['status'];
    }
}
TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);

$videos = Video::getAllVideos($status, $showOnlyLoggedUserVideos, true, [], false, $showUnlisted, $activeUsersOnly);
$total = Video::getTotalVideos($status, $showOnlyLoggedUserVideos, true, $showUnlisted, $activeUsersOnly);
TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
foreach ($videos as $key => $value) {
    /*
      $video = new Video('', '', $value['id']);
      $video->setStatus(Video::$statusActive);
      Video::clearCache($value['id']);continue;
     */
    unset($value['password'], $value['recoverPass']);
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="User Photo" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong>' . $name . '</strong>' . User::getEmailVerifiedIcon($value['users_id']) . ' <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
    $videos[$key]['next_video'] = [];
    $videos[$key]['description'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $videos[$key]['description']);
    $videos[$key]['title'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $videos[$key]['title']);
    $videos[$key]['clean_title'] = preg_replace('/[\x00-\x1F\x7F]/u', '', $videos[$key]['clean_title']);
    TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
    $videos[$key]['typeLabels'] = Video::getVideoTypeLabels($videos[$key]['filename']);
    TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
    $videos[$key]['maxResolution'] = Video::getHigestResolution($videos[$key]['filename']);
    TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
    if (!empty($videos[$key]['next_videos_id'])) {
        unset($_POST['searchPhrase']);
        $videos[$key]['next_video'] = Video::getVideo($videos[$key]['next_videos_id']);
    }
    TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
    if ($videos[$key]['type'] == 'article') {
        $videos[$key]['videosURL'] = getVideosURLArticle($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'image') {
        $videos[$key]['videosURL'] = getVideosURLIMAGE($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'zip') {
        $videos[$key]['videosURL'] = getVideosURLZIP($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'pdf') {
        $videos[$key]['videosURL'] = getVideosURLPDF($videos[$key]['filename']);
    } elseif ($videos[$key]['type'] == 'audio') {
        TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
        $videos[$key]['videosURL'] = getVideosURLAudio($videos[$key]['filename']);
        TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
        Video::checkIfIsBroken($value['id']);
        TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
    } else {
        TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
        $videos[$key]['videosURL'] = getVideosURL($videos[$key]['filename']);
        TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
        Video::checkIfIsBroken($value['id']);
        TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
    }
    unset($videos[$key]['password'], $videos[$key]['recoverPass']);
}

TimeLogEnd($timeLogName, __LINE__, $TimeLogLimit);
$obj = new stdClass();
$obj->users_id = User::getId();
$obj->current = getCurrentPage();
$obj->rowCount = getRowCount();
$obj->total = $total;
$obj->rows = $videos;
$obj->status = $status;
$obj->process_duration = microtime(true)-$start;

die(json_encode($obj));
exit;
