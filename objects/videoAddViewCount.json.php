<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
if (empty($_POST['id'])) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if (empty($_COOKIE[$global['session_name']])) {
    die('{"error":"Cookie is disabled"}');
}
if(empty($_COOKIE) && isIframe() && isIframeInDifferentDomain()){
    die('{"error":"isIframeInDifferentDomain"}');
}
require_once $global['systemRootPath'] . 'objects/video.php';
$obj = new Video("", "", $_POST['id']);
if (empty($obj)) {
    die("Object not found");
}
_session_start();
if (empty($_SESSION['addViewCount'])) {
    $_SESSION['addViewCount'] = array();
}

$seconds = parseDurationToSeconds($obj->getDuration());

if (!empty($seconds)) {
    $percent = (intval($_POST['currentTime']) / $seconds) * 100;
    $percentOptions = array(25,50,75,100);
    foreach ($percentOptions as $value) {
        if ($percent >= $value) {
            if (empty($_SESSION['addViewCount'][$_POST['id']][$value]) && !empty($_POST['currentTime'])) {
                if ($obj->addViewPercent($value)) {
                    _session_start();
                    $_SESSION['addViewCount'][$_POST['id']][$value] = 1;
                }
            }
        }
    }
}
if (empty($_SESSION['addViewCount'][$_POST['id']]['time'])) {
    $resp = $obj->addView();
    _session_start();
    $_SESSION['addViewCount'][$_POST['id']]['time'] = strtotime("+{$seconds} seconds");
} elseif (!empty($_POST['currentTime'])) {
    $resp = VideoStatistic::updateStatistic($obj->getId(), User::getId(), intval($_POST['currentTime']));
} else {
    $resp = 0;
}
$count = $obj->getViews_count();

$obj2 = new stdClass();
$obj2->status = !empty($resp);
$obj2->count = $count;
$obj2->resp = $resp;

echo json_encode($obj2);
