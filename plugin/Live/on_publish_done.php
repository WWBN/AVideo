<?php

require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once './Objects/LiveTransmitionHistory.php';

_error_log("NGINX ON Publish Done  POST: " . json_encode($_POST));
_error_log("NGINX ON Publish Done  GET: " . json_encode($_GET));
_error_log("NGINX ON Publish Done  php://input" . file_get_contents("php://input"));

// get GET parameters
$url = $_POST['tcurl'];
if (empty($url)) {
    $url = $_POST['swfurl'];
}
$parts = parse_url($url);
parse_str($parts["query"], $_GET);
_error_log("NGINX ON Publish Done  parse_url: " . json_encode($parts));
_error_log("NGINX ON Publish Done  parse_str: " . json_encode($_GET));

$_GET = object_to_array($_GET);

if ($_POST['name'] == 'live') {
    _error_log("NGINX ON Publish Done  wrong name {$_POST['p']}");
    // fix name for streamlab
    $pParts = explode("/", $_POST['p']);
    if (!empty($pParts[1])) {
        _error_log("NGINX ON Publish Done  like key fixed");
        $_POST['name'] = $pParts[1];
    }
}

if (empty($_POST['name']) && !empty($_GET['name'])) {
    $_POST['name'] = $_GET['name'];
}
if (empty($_POST['name']) && !empty($_GET['key'])) {
    $_POST['name'] = $_GET['key'];
}
if (strpos($_GET['p'], '/') !== false) {
    $parts = explode("/", $_GET['p']);
    if (!empty($parts[1])) {
        $_GET['p'] = $parts[0];
        if(empty($_POST['name'])){
            $_POST['name'] = $parts[1];
        }
    }
}

Live::deleteStatsCache(true);
$row = LiveTransmitionHistory::getLatest($_POST['name']);
_error_log("NGINX ON Publish Done finishFromTransmitionHistoryId {$_POST['name']} {$row['id']} {$row['key']} {$row['live_servers_id']}");
LiveTransmitionHistory::finishFromTransmitionHistoryId($row['id']);
$array = setLiveKey($row['key'], $row['live_servers_id']);
$parameters = Live::getLiveParametersFromKey($array['key']);
$array['cleanKey'] = $parameters['cleanKey'];
$array['stats'] = LiveTransmitionHistory::getStatsAndRemoveApplication($row['id']);
$socketObj = Live::notifySocketStats("socketLiveOFFCallback", $array);

