<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Live_servers($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>