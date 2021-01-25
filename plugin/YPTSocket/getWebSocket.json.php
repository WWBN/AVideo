<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->webSocketToken = "";
$obj->webSocketURL = "";

if(!AVideoPlugin::isEnabledByName("YPTSocket")){
    $obj->msg = "Socket plugin not enabled";
    die(json_encode($obj));
}


$obj->error = false;
$obj->webSocketToken = getEncryptedInfo(0);
$obj->webSocketURL = YPTSocket::getWebSocketURL();

die(json_encode($obj));