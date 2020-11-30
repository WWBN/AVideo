<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/Objects/Playlists_schedules.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('PlayLists');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Playlists_schedules($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>