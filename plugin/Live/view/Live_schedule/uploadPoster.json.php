<?php

require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_schedule.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');

if (!User::canStream()) {
    $obj->msg = "You cant do this 1";
    die(json_encode($obj));
}


$live_schedule_id = intval($_REQUEST['live_schedule_id']);

if (empty($live_schedule_id)) {
    forbiddenPage("Invalid schedule ID");
}

$row = new Live_schedule($live_schedule_id);

if (User::isAdmin() || $row->getUsers_id() == User::getId()) {
    if (isset($_REQUEST['image'])) {
        $image = Live_schedule::getPosterPaths($live_schedule_id);
        $obj->path = $image['path'];
        $obj->image = saveCroppieImage($obj->path, "image");
        $obj->error = false;
    }
} else {
    $obj->msg = "You cant do this 2";
}

die(json_encode($obj));
