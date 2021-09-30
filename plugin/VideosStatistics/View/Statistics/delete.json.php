<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideosStatistics/Objects/Statistics.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('VideosStatistics');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Statistics($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>