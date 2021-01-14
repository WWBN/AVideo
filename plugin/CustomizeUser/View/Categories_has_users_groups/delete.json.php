<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Categories_has_users_groups.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Categories_has_users_groups($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>