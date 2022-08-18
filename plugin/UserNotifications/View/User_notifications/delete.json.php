<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserNotifications/Objects/User_notifications.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->id = intval(@$_POST['id']);

$plugin = AVideoPlugin::loadPluginIfEnabled('UserNotifications');

if(!User::isLogged()){
    forbiddenPage("Please login first");
}
$obj->users_id = User::getId();
if(!empty($obj->id)){
    $row = new User_notifications($obj->id);
    //var_dump($row->getElement_Id());exit;
    if(!empty($row->getElement_Id())){
        if(!User::isAdmin()){
            if($row->getUsers_id() != User::getId()){
                forbiddenPage("This notification does not belong to you");
            }
        }
        $obj->error = !$row->delete();
    }else{
        $obj->msg = 'Message was already deleted';
        $obj->error = false;
    }
}else{
    $obj->error = !User_notifications::deleteForUsers_id($obj->users_id);
}


die(json_encode($obj));
?>