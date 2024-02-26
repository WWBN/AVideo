<?php

require_once '../../../../videos/configuration.php';

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
}

$obj->restream = Live::restream($obj->liveTransmitionHistory_id, $live_restreams_id, true);

$obj->error = false;
die(json_encode($obj));
