<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserNotifications/Objects/User_notifications.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('UserNotifications');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new User_notifications(@$_POST['id']);
$o->setMsg($_POST['msg']);
$o->setTitle($_POST['title']);
$o->setType($_POST['type']);
$o->setStatus($_POST['status']);
//$o->setTime_readed($_POST['time_readed']);
$o->setUsers_id($_POST['User_notificationsusers_id']);
$o->setImage($_POST['image']);
$o->setIcon($_POST['User_notificationsicon']);
$o->setHref($_POST['href']);
$o->setOnclick($_POST['onclick']);
$o->setElement_class($_POST['element_class']);
$o->setElement_id($_POST['element_id']);
$o->setPriority($_POST['priority']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
