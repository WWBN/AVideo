<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags_subscriptions.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('VideoTags');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Tags_subscriptions(@$_POST['id']);
$o->setTags_id($_POST['tags_id']);
$o->setUsers_id($_POST['users_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
