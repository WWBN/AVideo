<?php

require_once '../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if (empty($objP)) {
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

if (!User::canStream()) {
    $obj->msg = __('Cannot stream');
    die(json_encode($obj));
}

$obj->live_transmitions_history_id = intval($_REQUEST['live_transmitions_history_id']);
$obj->restreams_id = intval($_REQUEST['restreams_id']);

if (empty($obj->live_transmitions_history_id)) {
    forbiddenPage('live_transmitions_history_id is empty');
}
if (empty($obj->restreams_id)) {
    forbiddenPage('restreams_id is empty');
}

$url = Live_restreams_logs::getURLFromTransmitionAndRestream($obj->live_transmitions_history_id, $obj->restreams_id, 'log');

if (empty($url)) {
    $obj->error = false;
    $obj->log = false;
} else {
    $obj->log = json_decode(url_get_contents($url));
    if (!empty($obj->log)) {
        $obj->error = false;
    }
}

die(json_encode($obj));
