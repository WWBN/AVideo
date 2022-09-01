<?php

require_once '../../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if (empty($_REQUEST['token'])) {
    forbiddenPage('Token is empty');
}


$token = Live_restreams_logs::verifyToken($_REQUEST['token']);

if (empty($token)) {
    forbiddenPage('Token is invalid');
}
//var_dump($token);exit;
$obj->action = $token->action;

$obj->error = false;
$lrl = new Live_restreams_logs($token->live_restreams_logs_id);
switch ($token->action) {
    case 'log':
        $obj->logFile = $lrl->getLogFile();
        break;
    case 'stop':
        $obj->m3u8 = $lrl->getM3u8();
        $obj->liveTransmitionHistory_id = $lrl->getLive_transmitions_history_id();
        $obj->live_restreams_id = $lrl->getLive_restreams_id();
        break;
    case 'start':
        $lr = new Live_restreams($lrl->getLive_restreams_id());
        $obj->m3u8 = $lrl->getM3u8();
        $obj->liveTransmitionHistory_id = $lrl->getLive_transmitions_history_id();
        $obj->live_restreams_id = $lrl->getLive_restreams_id();
        $obj->restreamsToken = array(encryptString($obj->live_restreams_id));
        $obj->restreamsDestinations = array($lr->getName());
        $obj->users_id = $lr->getUsers_id();
        $obj->token = encryptString(array('users_id' => $obj->users_id, 'time' => time(), 'liveTransmitionHistory_id' => $obj->liveTransmitionHistory_id, 'live_restreams_id' => $obj->live_restreams_id));        
        $obj->responseToken = $obj->token;
        break;
    default :
        $obj->error = true;
        $obj->msg = 'Action not found';
        break;
}
die(json_encode($obj));
