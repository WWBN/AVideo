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

_error_log("Live_restreams/getAction.json.php: request received users_id=" . User::getId() . " action={$obj->action} live_restreams_logs_id={$obj->live_restreams_logs_id} live_transmitions_history_id={$obj->live_transmitions_history_id} live_restreams_id={$obj->live_restreams_id}");

if (empty($obj->live_restreams_logs_id)) {
    if (!empty($obj->live_transmitions_history_id) && !empty($obj->live_restreams_id)) {

    } else {
        _error_log("Live_restreams/getAction.json.php: ids are empty, rejecting");
        forbiddenPage(__("ids are empty"));
    }
} else {
    $lrl = new Live_restreams_logs($obj->live_restreams_logs_id);
    $obj->live_transmitions_history_id = $lrl->getLive_transmitions_history_id();
    $obj->live_restreams_id = $lrl->getLive_restreams_id();
}

$obj->url = Live_restreams_logs::getURLFromTransmitionAndRestream($obj->live_transmitions_history_id, $obj->live_restreams_id, $obj->action);

_error_log("Live_restreams/getAction.json.php: computed url=" . var_export($obj->url, true));

if (!User::isAdmin()) {
    require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
    $lr = new Live_restreams($obj->live_restreams_id);
    if ($lr->getUsers_id() !== User::getId()) {
        _error_log("Live_restreams/getAction.json.php: forbidden, restream owner mismatch. restream_users_id=" . $lr->getUsers_id() . " request_users_id=" . User::getId());
        forbiddenPage(__("You have no access to this restream"));
    }
}

$obj->response = url_get_contents($obj->url, '', 0, false, false, false);
_error_log("Live_restreams/getAction.json.php: raw response from restreamer=" . var_export($obj->response, true));
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

_error_log("Live_restreams/getAction.json.php: final response=" . json_encode($obj));

die(json_encode($obj));
