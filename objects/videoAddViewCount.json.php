<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
enforceRateLimit('video_add_view_count', 240, 60);
ini_set('max_execution_time', 5);
//_error_log('Add view '. json_encode($_REQUEST));

$obj2 = new stdClass();
$obj2->error = true;
$obj2->msg = '';

if (isBot()) {

    $obj2->msg = 'Bot Not Allowed';
    die(json_encode($obj2));
}
$videos_id = intval(@$_REQUEST['id']);
if (empty($videos_id)) {
    $obj2->msg = 'Permission denied';
    die(json_encode($obj2));
}
if (empty($_COOKIE[$global['session_name']]) && !isAVideoMobileApp()) {
    $obj2->msg = 'Cookie is disabled';
    $obj2->HTTP_USER_AGENT = @$_SERVER['HTTP_USER_AGENT'];
    die(json_encode($obj2));
}
if (empty($_COOKIE) && isIframe() && isIframeInDifferentDomain()) {
    $obj2->msg = 'isIframeInDifferentDomain';
    die(json_encode($obj2));
}
require_once $global['systemRootPath'] . 'objects/video.php';
$obj = new Video("", "", $videos_id, true);
if (empty($obj)) {
    $obj2->msg = 'Object not found';
    die(json_encode($obj2));
}
_session_start();
if (empty($_SESSION['addViewCount'])) {
    $_SESSION['addViewCount'] = [];
}

$currentTimeRequest = intval(@$_REQUEST['currentTime']);
if ($currentTimeRequest < 0) {
    $currentTimeRequest = 0;
}

$usersId = User::getId();
$ipHash = md5(getRealIpAddr());
$timeBucket = intval(floor($currentTimeRequest / 10));
$duplicateCacheKey = "videoAddViewCount/{$videos_id}/u{$usersId}/ip{$ipHash}/b{$timeBucket}";

$isDuplicateViewEvent = !empty(ObjectYPT::getCacheGlobal($duplicateCacheKey, 8, true));
if (!$isDuplicateViewEvent) {
    ObjectYPT::setCacheGlobal($duplicateCacheKey, 1);
}

$seconds = parseDurationToSeconds($obj->getDuration());

if (!empty($seconds) && isset($_REQUEST['currentTime'])) {
    $percent = ($currentTimeRequest / $seconds) * 100;
    $percentOptions = [25, 50, 75, 100];
    foreach ($percentOptions as $value) {
        if ($percent >= $value) {
            if (empty($_SESSION['addViewCount'][$videos_id][$value]) && !empty($_REQUEST['currentTime'])) {
                if ($obj->addViewPercent($value)) {
                    _session_start();
                    $_SESSION['addViewCount'][$videos_id][$value] = 1;
                }
            }
        }
    }
}

$obj2 = new stdClass();
$seconds_watching_video = intval(@$_REQUEST['seconds_watching_video']);
if ($seconds_watching_video < 0) {
    $seconds_watching_video = 0;
}

// Comparison and update
$current_time = time();
if (isset($_SESSION['addViewCount'][$videos_id]['last_update_time'])) {
    $elapsed_time = $current_time - $_SESSION['addViewCount'][$videos_id]['last_update_time'];
    if ($seconds_watching_video > $elapsed_time) {
        $seconds_watching_video = $elapsed_time;
    }
}

$_SESSION['addViewCount'][$videos_id]['last_update_time'] = $current_time;

$obj2->seconds_watching_video = $seconds_watching_video;

if (empty($_SESSION['addViewCount'][$videos_id]['time'])) {
    //_error_log("videos_statistics addView {$videos_id} {$_SERVER['HTTP_USER_AGENT']} ".json_encode($_SESSION['addViewCount']));
    if ($isDuplicateViewEvent) {
        $resp = 0;
        $obj2->msg = 'Duplicate view count ignored';
    } else {
        $resp = $obj->addView();
        if(empty($resp)){
            $obj2->msg = $_addViewFailReason;
        }
        _session_start();
        $_SESSION['addViewCount'][$videos_id]['time'] = strtotime("+{$seconds} seconds");
    }
} else {
    $obj2->msg = 'View not added, the user already have a view in this session';
    //_error_log("videos_statistics addView OK {$videos_id} ".json_encode($_SESSION['addViewCount']));
}

if (isset($_REQUEST['currentTime'])) {
    $obj2->currentTime = $currentTimeRequest;
    $resp = VideoStatistic::updateStatistic($obj->getId(), User::getId(), $obj2->currentTime, $obj2->seconds_watching_video);
    $obj2->updateStatistic = $_updateStatisticFailMessage;
} else {
    $resp = 0;
    $obj2->updateStatistic = false;
}
$count = $obj->getViews_count();

$obj2->status = !empty($resp);
$obj2->count = $count;
$obj2->videos_id = $obj->getId();
$obj2->countHTML = number_format_short($count);
$obj2->resp = $resp;
$obj2->users_id = User::getId();
$obj2->session_id = session_id();

echo json_encode($obj2);
