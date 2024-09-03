<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserConnections/Objects/Users_connections.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('UserConnections');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Users_connections($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>