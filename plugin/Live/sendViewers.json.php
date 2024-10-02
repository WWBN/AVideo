<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

header('Content-Type: application/json');

if(empty($_REQUEST['viewerUrl']) || !isValidURL($_REQUEST['viewerUrl'])){
    forbiddenPage('Invalid redirect URL');
}

if(empty($_REQUEST['live_key'])){
    forbiddenPage('Invalid live key');
}

$row = LiveTransmition::keyExists($_REQUEST['live_key']);
if(empty($row)){
    forbiddenPage('Live key not found');
}
if(User::getId() != $row['users_id'] && !User::isAdmin()){
    forbiddenPage('This live does not belong to you');
}

$obj = new stdClass();
$obj->row = $row;
$obj->viewerUrl = $_REQUEST['viewerUrl'];
$obj->customMessage = $_REQUEST['customMessage'];
$obj->live_key = $_REQUEST['live_key'];
$obj->live_servers_id = intval(@$_REQUEST['live_servers_id']);

$obj->sendSocketMessage = sendSocketMessage(array('redirectLive'=>$obj), 'redirectLive', 0);

$obj->msg = '';
$obj->error = false;
$obj->dropURL = Live::getDropURL($obj->live_key, $obj->live_servers_id);
$obj->dropURLResponse = _json_decode(url_get_contents($obj->dropURL));

die(_json_encode($obj));
