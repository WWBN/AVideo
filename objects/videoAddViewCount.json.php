<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
ini_set('max_execution_time', 5);
//_error_log('Add view '. json_encode($_REQUEST));

$obj2 = new stdClass();
$obj2->error = true;
$obj2->msg = '';

if (isBot()) {

    $obj2->msg = 'Bot Not Allowed';
    die(json_encode($obj2));
}
if (empty($_REQUEST['id'])) {
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
$obj = new Video("", "", $_REQUEST['id'], true);
if (empty($obj)) {
    $obj2->msg = 'Object not found';
    die(json_encode($obj2));
}
_session_start();
if (empty($_SESSION['addViewCount'])) {
    $_SESSION['addViewCount'] = [];
}

$seconds = parseDurationToSeconds($obj->getDuration());

if (!empty($seconds) && isset($_REQUEST['currentTime'])) {
    $percent = (intval($_REQUEST['currentTime']) / $seconds) * 100;
    $percentOptions = [25, 50, 75, 100];
    foreach ($percentOptions as $value) {
        if ($percent >= $value) {
            if (empty($_SESSION['addViewCount'][$_REQUEST['id']][$value]) && !empty($_REQUEST['currentTime'])) {
                if ($obj->addViewPercent($value)) {
                    _session_start();
                    $_SESSION['addViewCount'][$_REQUEST['id']][$value] = 1;
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
if (isset($_SESSION['addViewCount'][$_REQUEST['id']]['last_update_time'])) {
    $elapsed_time = $current_time - $_SESSION['addViewCount'][$_REQUEST['id']]['last_update_time'];
    if ($seconds_watching_video > $elapsed_time) {
        $seconds_watching_video = $elapsed_time;
    }
}

$_SESSION['addViewCount'][$_REQUEST['id']]['last_update_time'] = $current_time;

$obj2->seconds_watching_video = $seconds_watching_video;

if (empty($_SESSION['addViewCount'][$_REQUEST['id']]['time'])) {
    //_error_log("videos_statistics addView {$_REQUEST['id']} {$_SERVER['HTTP_USER_AGENT']} ".json_encode($_SESSION['addViewCount']));
    $resp = $obj->addView();
    if(empty($resp)){
        $obj2->msg = $_addViewFailReason;
    }
    _session_start();
    $_SESSION['addViewCount'][$_REQUEST['id']]['time'] = strtotime("+{$seconds} seconds");
} else {
    $obj2->msg = 'View not added, the user already have a view in this session';
    //_error_log("videos_statistics addView OK {$_REQUEST['id']} ".json_encode($_SESSION['addViewCount']));
}

if (isset($_REQUEST['currentTime'])) {
    $currentTime = intval($_REQUEST['currentTime']);
    if ($currentTime < 0) {
        $currentTime = 0;
    }
    $resp = VideoStatistic::updateStatistic($obj->getId(), User::getId(), $currentTime, $seconds_watching_video);
} else {
    $resp = 0;
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
