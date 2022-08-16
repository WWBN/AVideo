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

$obj->live_restreams_logs_id = @$_REQUEST['live_restreams_logs_id'];
if (empty($obj->live_restreams_logs_id)) {
    forbiddenPage(__("live_restreams_logs_id is empty"));
}

$lrl = new Live_restreams_logs($obj->live_restreams_logs_id);

$obj->action = @$_REQUEST['action'];
$url = $lrl->getRestreamer();
$url = addQueryStringParameter($url, 'tokenForAction', Live_restreams_logs::getToken($obj->action, $obj->live_restreams_logs_id));
$obj->url = $url;
$obj->response = url_get_contents($url);
$obj->json = json_decode($obj->response);
if (empty($obj->json)) {
    $obj->responseFrom = 'Streamer/GetAction/Restreamer[Empty]';
    $obj->msg = $obj->response;
} else {
    $obj->responseFrom = 'Streamer/GetAction/Restreamer';
    if(empty(!$obj->json->error)){
        $obj->msg = $obj->json->msg;
    }else{        
        $obj->error = false;
    }
}

die(json_encode($obj));
