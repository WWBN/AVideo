<?php

require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->liveTransmitionHistory_id = 0;

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if(!User::canStream()){
    forbiddenPage('You cannot stream');
}

$obj->liveTransmitionHistory_id = intval($_REQUEST['live_transmitions_history_id']);

if(empty($obj->liveTransmitionHistory_id)){
    forbiddenPage('live_transmitions_history_id cannot be empty');
}

$live_restreams_id = intval($_REQUEST['live_restreams_id']);

if(empty($live_restreams_id)){
    forbiddenPage('live_restreams_id cannot be empty');
}

$lth = new LiveTransmitionHistory($obj->liveTransmitionHistory_id);


if(!User::isAdmin()){
    $users_id = $lth->getUsers_id();
    if($users_id != User::getId()){
        forbiddenPage('You cannot restream this live');
    }

    // also verify ownership of the destination itself, matching getAction.json.php/delete.json.php
    $lr = new Live_restreams($live_restreams_id);
    if ($lr->getUsers_id() != User::getId()) {
        _error_log("Live_restreams/resendRestreamer.json.php: forbidden, restream owner mismatch. restream_users_id=" . $lr->getUsers_id() . " request_users_id=" . User::getId() . " live_restreams_id={$live_restreams_id}");
        forbiddenPage('You cannot restream to this destination');
    }
}

$obj->restream = Live::restream($obj->liveTransmitionHistory_id, $live_restreams_id, true);

$obj->error = false;
die(json_encode($obj));
