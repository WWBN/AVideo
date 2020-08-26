<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule_has_users_groups.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Meet');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Meet_schedule_has_users_groups(@$_POST['id']);
$o->setMeet_schedule_id($_POST['meet_schedule_id']);
$o->setUsers_groups_id($_POST['users_groups_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
