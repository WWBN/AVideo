<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';
_session_write_close();

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->response = false;

if (empty($_REQUEST['key'])) {
    $obj->msg = __("Key is empty");
    die(json_encode($obj));
}

$p = AVideoPlugin::loadPluginIfEnabled("Live");

if (empty($p)) {
    $obj->msg = __("Live plugin is not enabled");
    die(json_encode($obj));
}


$obj->response = Live::stopLiveFromkey($_REQUEST['key']);
$obj->error = $obj->response->error;

echo json_encode($obj);
