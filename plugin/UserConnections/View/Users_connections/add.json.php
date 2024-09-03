<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserConnections/Objects/Users_connections.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('UserConnections');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Users_connections(@$_POST['id']);
$o->setUsers_id1($_POST['users_id1']);
$o->setUsers_id2($_POST['users_id2']);
$o->setUser1_status($_POST['user1_status']);
$o->setUser2_status($_POST['user2_status']);
$o->setUser1_mute($_POST['user1_mute']);
$o->setUser2_mute($_POST['user2_mute']);
$o->setCreated_php_time($_POST['created_php_time']);
$o->setModified_php_time($_POST['modified_php_time']);
$o->setJson($_POST['json']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
