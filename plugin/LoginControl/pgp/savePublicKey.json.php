<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->id = 0;

$plugin = AVideoPlugin::loadPluginIfEnabled('LoginControl');
                                                
if(!User::isLogged()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$obj->id = LoginControl::setPGPKey(User::getId(), @$_REQUEST['publicKey']);

$obj->error = empty($obj->id);

echo json_encode($obj);
