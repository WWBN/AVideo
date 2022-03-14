<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;

$live_servers_id = intval($_REQUEST['live_servers_id']);
$live_schedule_id = intval($_REQUEST['live_schedule_id']);

if (!User::isLogged()) {
    $obj->msg = 'You cant edit this file';
    die(json_encode($obj));
}

$live = AVideoPlugin::loadPluginIfEnabled("Live");

if (empty($live)) {
    $obj->msg = 'Plugin not enabled';
    die(json_encode($obj));
}

header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = ['jpg', 'jpeg', 'gif', 'png'];
if (isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['file_data']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }

    if (empty($live_schedule_id)) {
        $obj->file = Live::_getPosterImage(User::getId(), $live_servers_id);
        $obj->fileThumbs = Live::_getPosterThumbsImage(User::getId(), $live_servers_id);
    } else {
        $paths = Live_schedule::getPosterPaths($live_schedule_id);
        $obj->file = str_replace($global['systemRootPath'], '', $paths['path']);
        $obj->fileThumbs = str_replace($global['systemRootPath'], '', $paths['path_thumbs']);
    }

    $tmpDestination = "{$global['systemRootPath']}{$obj->file}.{$extension}";

    make_path($global['systemRootPath'].$obj->file);
    if (!move_uploaded_file($_FILES['file_data']['tmp_name'], $tmpDestination)) {
        $obj->msg = "Error on move_file_uploaded_file {$obj->file}" ;
        die(json_encode($obj));
    }
    if (file_exists($tmpDestination)) {
        convertImage($tmpDestination, $global['systemRootPath'].$obj->file, 70);
        unlink($tmpDestination);
    } else {
        $obj->msg = "Image not moved {$tmpDestination}" ;
        die(json_encode($obj));
    }
    if (file_exists($global['systemRootPath'].$obj->file)) {
        im_resizeV2($global['systemRootPath'].$obj->file, $global['systemRootPath'].$obj->fileThumbs, $advancedCustom->thumbsWidthLandscape, $advancedCustom->thumbsHeightLandscape);
    } else {
        $obj->msg = "Image not created {$tmpDestination} {$global['systemRootPath']}{$obj->file}" ;
        die(json_encode($obj));
    }

    if (!empty($live_schedule_id)) {
        $ls = new Live_schedule($live_schedule_id);
        $array = setLiveKey($ls->getKey(), $ls->getLive_servers_id());
        $array['users_id'] = $ls->getUsers_id();
        $array['stats'] = getStatsNotifications(true);
        Live::notifySocketStats("socketLiveOFFCallback", $array);
    }

    echo "{}";
    exit;
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
