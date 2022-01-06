<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->id = 0;

$plugin = AVideoPlugin::loadPluginIfEnabled('LoginControl');
                                                
if(!User::isLogged()){
    $obj->msg = "You can't do this";
    die(json_encode($obj));
}

if(User::isAdmin() && !empty($_REQUEST['users_id'])){
    $users_id = intval($_REQUEST['users_id']);
}
if(empty($users_id)){
    $users_id = User::getId();
}
if(empty($users_id)){
    $obj->msg = "empty users id";
    die(json_encode($obj));
}


$obj->id = LoginControl::setPGPKey($users_id, @$_REQUEST['publicKey']);

$obj->error = empty($obj->id);

echo json_encode($obj);
