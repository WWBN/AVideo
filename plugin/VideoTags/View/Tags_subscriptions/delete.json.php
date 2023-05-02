<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/Tags_subscriptions.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('VideoTags');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Tags_subscriptions($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>