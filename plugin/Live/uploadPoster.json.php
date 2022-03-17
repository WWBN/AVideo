<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_schedule.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');

if (!User::canStream()) {
    $obj->msg = "You cant do this 1";
    die(json_encode($obj));
}


$live_servers_id = intval(@$_REQUEST['live_servers_id']);
$live_schedule_id = intval(@$_REQUEST['live_schedule_id']);

if (!empty($live_schedule_id)) {
    $row = new Live_schedule($live_schedule_id);
    if (User::isAdmin() || $row->getUsers_id() == User::getId()) {
        if (isset($_REQUEST['image'])) {
            $image = Live_schedule::getPosterPaths($live_schedule_id);
            $obj->path = $image['path'];
            $obj->image = saveCroppieImage($obj->path, "image");
            $obj->error = false;
        }
    }
} else {
    $obj->path = $global['systemRootPath'] . Live::_getPosterImage(User::getId(), $live_servers_id);
    $obj->image = saveCroppieImage($obj->path, "image");
    if ($obj->image) {
        $obj->pathThumbs = $global['systemRootPath'] . Live::_getPosterThumbsImage(User::getId(), $live_servers_id);
        @unlink($obj->pathThumbs);
        $obj->error = false;
    }
}

die(json_encode($obj));
