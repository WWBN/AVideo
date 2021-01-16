<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');

if(!User::canStream()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Live_restreams($id);
if(!User::isAdmin() && $row->getUsers_id()!= User::getId()){
    $obj->msg = "You are not admin";
    die(json_encode($obj));
}
$obj->error = !$row->delete();
die(json_encode($obj));
?>