<?php

require_once '../../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if(!User::canStream()){
    forbiddenPage('You cannot stream');
}

_error_log('Testing reestream users_id=['.User::getId().'] '.json_encode(debug_backtrace()));

$lth = new LiveTransmitionHistory();
$lth->setTitle('Restream test '.date('Y-m-d H:i:s'));
$lth->setDescription('');
$lth->setKey(_uniqid());
$lth->setDomain('localhost');
$lth->setUsers_id(User::getId());
$lth->setLive_servers_id(Live::getLiveServersIdRequest());
$obj->liveTransmitionHistory_id = $lth->save();
$obj->restream = Live::restream($obj->liveTransmitionHistory_id, 0, true);

$obj->error = false;
die(json_encode($obj));
