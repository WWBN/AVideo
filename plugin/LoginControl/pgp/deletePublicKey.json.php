<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->users_id = @$_REQUEST['users_id'];
$obj->id = 0;

$plugin = AVideoPlugin::loadPluginIfEnabled('LoginControl');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

if(empty($obj->users_id)){
    $obj->msg = "users_id cannot be empty";
    die(json_encode($obj));
}

$obj->id = LoginControl::setPGPKey($obj->users_id, '');

$obj->error = empty($obj->id);
if(!$obj->error){
    $obj->msg = __('PGP Key removed');
}

echo json_encode($obj);
