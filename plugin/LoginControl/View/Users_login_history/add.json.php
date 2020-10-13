<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LoginControl/Objects/logincontrol_history.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('LoginControl');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new logincontrol_history(@$_POST['id']);
$o->setUsers_id($_POST['users_id']);
$o->setUniqidV4($_POST['uniqidV4']);
$o->setIp($_POST['ip']);
$o->setUser_agent($_POST['user_agent']);
$o->setConfirmation_code($_POST['confirmation_code']);
$o->setStatus($_POST['status']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
