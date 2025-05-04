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

$liveObj = AVideoPlugin::getDataObject('Live');

$token = Live_restreams_logs::verifyToken($_REQUEST['token']);

if (empty($token)) {
    forbiddenPage('Token is invalid');
}
//var_dump($token);exit;
$obj->action = $token->action;

$obj->error = false;
if(!empty($token->live_restreams_logs_id)){
    $lrl = new Live_restreams_logs($token->live_restreams_logs_id);
    $obj->logFile = $lrl->getLogFile();
    $obj->liveTransmitionHistory_id = $lrl->getLive_transmitions_history_id();
    $obj->live_restreams_id = $lrl->getLive_restreams_id();
}else if(!empty($token->live_restreams_id) && !empty($token->live_transmitions_history_id)){
    $obj->logFile = '';
    $obj->liveTransmitionHistory_id = $token->live_transmitions_history_id;
    $obj->live_restreams_id = $token->live_restreams_id;
}else{
    die('Ids not found');
}
$lhistory = new LiveTransmitionHistory($obj->liveTransmitionHistory_id);
$lr = new Live_restreams($obj->live_restreams_id);
$obj->users_id = $lr->getUsers_id();
$obj->restreamsDestinations = array($obj->live_restreams_id=>$lr->getName());
$obj->restreamsToken = array($obj->live_restreams_id=>encryptString($obj->live_restreams_id));
$obj->m3u8 = Live::getM3U8File($lhistory->getKey(), true, true);
$obj->token = encryptString(array('users_id' => $obj->users_id, 'time' => time(), 'liveTransmitionHistory_id' => $obj->liveTransmitionHistory_id, 'live_restreams_id' => $obj->live_restreams_id));
$obj->responseToken = $obj->token;
$obj->restreamStandAloneFFMPEG = $liveObj->restreamStandAloneFFMPEG ;

switch ($token->action) {
    case 'log':
        break;
    case 'stop':
        break;
    case 'start':
        break;
    default :
        $obj->error = true;
        $obj->msg = 'Action not found';
        break;
}
die(json_encode($obj));
