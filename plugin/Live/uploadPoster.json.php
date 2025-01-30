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


$ppv_schedule_id = intval($_REQUEST['ppv_schedule_id'] ?? 0);
$live_servers_id = intval($_REQUEST['live_servers_id'] ?? 0);
$live_schedule_id = intval($_REQUEST['live_schedule_id'] ?? 0);
$posterType = intval($_REQUEST['posterType'] ?? 0);

if (!empty($live_schedule_id) || !empty($ppv_schedule_id)) {
    if(!empty($live_schedule_id)){
        $row = new Live_schedule($live_schedule_id);
    }else{
        $row = new Ppvlive_schedule($ppv_schedule_id);
    }
    if (User::isAdmin() || $row->getUsers_id() == User::getId()) {
        if (isset($_REQUEST['image'])) {
            $image = Live_schedule::getPosterPaths($live_schedule_id, $ppv_schedule_id, $posterType);
            $obj->path = $image['path'];
            $obj->image = saveCroppieImage($obj->path, "image");
            $obj->error = false;
        }
    } else {
        $obj->msg = ("This live does not belong to you schedule");
        die(json_encode($obj));
    }
} else {
    $obj->path = $global['systemRootPath'] . Live::_getPosterImage(User::getId(), $live_servers_id, 0, 0, $posterType);
    $obj->image = saveCroppieImage($obj->path, "image");
    if ($obj->image) {
        $obj->pathThumbs = $global['systemRootPath'] . Live::_getPosterThumbsImage(User::getId(), $live_servers_id, $posterType);

        _error_log("removePoster.php ({$obj->pathThumbs}) unlink line=" . __LINE__);
        @unlink($obj->pathThumbs);
        $obj->error = false;
    }
}

if (isset($_REQUEST['liveImgCloseTimeInSeconds']) && isset($_REQUEST['liveImgTimeInSeconds'])) {
    $o = new stdClass();
    $o->liveImgCloseTimeInSeconds = intval($_REQUEST['liveImgCloseTimeInSeconds']);
    $o->liveImgTimeInSeconds = intval($_REQUEST['liveImgTimeInSeconds']);

    $obj->jsonFile = str_replace('.jpg', '.json', $obj->path);
    $obj->jsonFileBytes = _file_put_contents($obj->jsonFile, $o);
}

if (empty($obj->error)) {
    deleteStatsNotifications(true);
}

die(json_encode($obj));
