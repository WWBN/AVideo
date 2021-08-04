<?php

header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (!file_exists($configFile)) {
        $configFile = '../videos/configuration.php';
    }
    require_once $configFile;
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = "";

if (!User::canStream()) {
    $obj->msg = __("Permition denied");
    die(json_encode($obj));
}

$live = AVideoPlugin::loadPluginIfEnabled("Live");
require_once './Objects/LiveTransmition.php';

if (empty($live)) {
    $obj->msg = __("Plugin disabled");
    die(json_encode($obj));
}

if (!isValidURL($_REQUEST['m3u8'])) {
    $obj->msg = 'Invalid m3u8';
    die(json_encode($obj));
}
_error_log('webRTCToLive: start '. json_encode($_REQUEST));
$users_id = User::getId();
$count = 1;
while ($count <= 4) {
    sleep(10);
    $count++;
    if (isURL200($_REQUEST['m3u8'], true)) {
        break;
    } else {
        _error_log('webRTCToLive: wait till 200 ' . $_REQUEST['m3u8']);
    }
}

$obj->response = Live::reverseRestream($_REQUEST['m3u8'], $users_id, @$_REQUEST['live_servers_id'], @$_REQUEST['forceIndex']);

$obj->error = false;

_error_log('webRTCToLive: complete '. json_encode($obj));
die(json_encode($obj));
