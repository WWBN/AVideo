<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->token = @$_REQUEST['token'];

if (!AVideoPlugin::isEnabledByName('Live')) {
    $obj->msg = "Plugin is disabled";
    die(json_encode($obj));
}

if (empty($_REQUEST['token'])) {
    $obj->msg = "Token is empty";
    die(json_encode($obj));
}

$array = Live::decryptHash($_REQUEST['token']);

if (!is_array($array)) {
    $obj->msg = "Token is invalid";
    die(json_encode($obj));
}

$obj->users_id = intval($array['users_id']);

$twelveHours = 43200;

if (!empty($array['time']) && time() - $array['time'] > $twelveHours) {
    $obj->msg = "Token is expired";
    die(json_encode($obj));
}

$liveObj = AVideoPlugin::getDataObject('Live');

_error_log("Live::verifyToken.json.php {$_SERVER['HTTP_REFERER']} ". json_encode($array));

$trasnmition = LiveTransmition::createTransmitionIfNeed($obj->users_id);
$obj->key = $trasnmition['key'].'_'.time();
$lso = new LiveStreamObject($obj->key);
$obj->RTMPLinkWithOutKey = $lso->getRTMPLinkWithOutKey();
$obj->restreamStandAloneFFMPEG = $liveObj->restreamStandAloneFFMPEG ;


$obj->error = false;
die(json_encode($obj));
