<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_user_preferences.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');

if(!User::isLogged()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Publisher_user_preferences($id);

if($row->getUsers_id() != User::getId() ){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$obj->error = !$row->delete();
die(json_encode($obj));
?>