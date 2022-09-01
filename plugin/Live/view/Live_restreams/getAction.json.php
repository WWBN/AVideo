<?php

require_once '../../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->responseFrom = 'Streamer/GetAction';

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if (!Live::canRestream()) {
    forbiddenPage(__("You can not do this"));
}

$obj->live_restreams_logs_id = intval(@$_REQUEST['live_restreams_logs_id']);
$obj->live_transmitions_history_id = intval(@$_REQUEST['live_transmitions_history_id']);
$obj->live_restreams_id = intval(@$_REQUEST['live_restreams_id']);
$obj->action = @$_REQUEST['action'];

if (empty($obj->live_restreams_logs_id)) {
    if (!empty($obj->live_transmitions_history_id) && !empty($obj->live_restreams_id)) {
        
    } else {
        forbiddenPage(__("ids are empty"));
    }
} else {
    $lrl = new Live_restreams_logs($obj->live_restreams_logs_id);
    $obj->live_transmitions_history_id = $lrl->getLive_transmitions_history_id();
    $obj->live_restreams_id = $lrl->getLive_restreams_id();
}

$obj->url = Live_restreams_logs::getURLFromTransmitionAndRestream($obj->live_transmitions_history_id, $obj->live_restreams_id, $obj->action);
$obj->response = url_get_contents($obj->url);
$obj->json = json_decode($obj->response);
if (empty($obj->json)) {
    $obj->responseFrom = 'Streamer/GetAction/Restreamer[Empty]';
    $obj->msg = $obj->response;
} else {
    $obj->responseFrom = 'Streamer/GetAction/Restreamer';
    if (empty(!$obj->json->error)) {
        $obj->msg = $obj->json->msg;
    } else {
        $obj->error = false;
    }
}

die(json_encode($obj));
