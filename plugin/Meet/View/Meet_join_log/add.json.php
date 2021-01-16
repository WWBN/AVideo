<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_join_log.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Meet');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Meet_join_log(@$_POST['id']);
$o->setMeet_schedule_id($_POST['meet_schedule_id']);
$o->setUsers_id($_POST['users_id']);
$o->setIp($_POST['ip']);
$o->setUser_agent($_POST['user_agent']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
