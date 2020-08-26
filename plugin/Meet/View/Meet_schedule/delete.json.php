<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('Meet');

$id = intval($_POST['id']);
$row = new Meet_schedule($id);

if(!$row->canManageSchedule()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$obj->error = !$row->delete();
die(json_encode($obj));
?>