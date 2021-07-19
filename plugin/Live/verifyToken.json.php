<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->token = @$_REQUEST['token'];

if(!AVideoPlugin::isEnabledByName('Live')){
    $obj->msg = "Plugin is disabled";
    die(json_encode($obj));
}

if(empty($_REQUEST['token'])){
    $obj->msg = "Token is empty";
    die(json_encode($obj));
}

$array = Live::decryptHash($_REQUEST['token']);

if(!is_array($array)){
    $obj->msg = "Token is invalid";
    die(json_encode($obj));
}

if(!empty($array['users_id'])){
    $obj->msg = "Users Id is empty";
    die(json_encode($obj));
}

$twelveHours = 43200;

if(time() - $array['time'] > $twelveHours){
    $obj->msg = "Token is expired";
    die(json_encode($obj));
}


$obj->key = Live::getKeyFromUser($array['users_id']);
$lso = new LiveStreamObject($key);
$obj->RTMPLinkWithOutKey = $lso->getRTMPLinkWithOutKey();


$obj->error = false;
die(json_encode($obj));