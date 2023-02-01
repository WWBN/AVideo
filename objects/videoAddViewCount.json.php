<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
ini_set('max_execution_time', 5);
//_error_log('Add view '. json_encode($_REQUEST));

if(isBot()){
    die('Bot Not Allowed');
}
if (empty($_REQUEST['id'])) {
    die('{"error":"Permission denied"}');
}
if (empty($_COOKIE[$global['session_name']])) {
    die('{"error":"Cookie is disabled"}');
}
if (empty($_COOKIE) && isIframe() && isIframeInDifferentDomain()) {
    die('{"error":"isIframeInDifferentDomain"}');
}
require_once $global['systemRootPath'] . 'objects/video.php';
$obj = new Video("", "", $_REQUEST['id']);
if (empty($obj)) {
    die("Object not found");
}
_session_start();
if (empty($_SESSION['addViewCount'])) {
    $_SESSION['addViewCount'] = [];
}

$seconds = parseDurationToSeconds($obj->getDuration());

if (!empty($seconds)) {
    $percent = (intval($_REQUEST['currentTime']) / $seconds) * 100;
    $percentOptions = [25,50,75,100];
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
if ($seconds_watching_video<0) {
    $seconds_watching_video = 0;
}

$obj2->seconds_watching_video = $seconds_watching_video;
if (empty($_SESSION['addViewCount'][$_REQUEST['id']]['time'])) {
    //_error_log("videos_statistics addView {$_REQUEST['id']} {$_SERVER['HTTP_USER_AGENT']} ".json_encode($_SESSION['addViewCount']));
    $resp = $obj->addView();
    _session_start();
    $_SESSION['addViewCount'][$_REQUEST['id']]['time'] = strtotime("+{$seconds} seconds");
}else{
    //_error_log("videos_statistics addView OK {$_REQUEST['id']} ".json_encode($_SESSION['addViewCount']));
}

if (isset($_REQUEST['currentTime'])) {
    $currentTime = intval($_REQUEST['currentTime']);
    if ($currentTime<0) {
        $currentTime = 0;
    }
    $resp = VideoStatistic::updateStatistic($obj->getId(), User::getId(), $currentTime, $seconds_watching_video);
} else {
    $resp = 0;
}
$count = $obj->getViews_count();

$obj2->status = !empty($resp);
$obj2->count = $count;
$obj2->countHTML = number_format_short($count);
$obj2->resp = $resp;

echo json_encode($obj2);
