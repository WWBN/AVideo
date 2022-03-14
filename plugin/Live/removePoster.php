<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;

$live_servers_id = intval($_REQUEST['live_servers_id']);
if (empty($live_servers_id)) {
    $obj->msg = 'live_servers_id is empty';
    die(json_encode($obj));
}

if (!User::isLogged()) {
    $obj->msg = 'You can\'t edit this file';
    die(json_encode($obj));
}

$live = AVideoPlugin::loadPluginIfEnabled("Live");

if (empty($live)) {
    $obj->msg = 'Plugin not enabled';
    die(json_encode($obj));
}

header('Content-Type: application/json');
// A list of permitted file extensions
$obj->file = Live::_getPosterImage(User::getId(), $live_servers_id);
$obj->fileThumbs = Live::_getPosterThumbsImage(User::getId(), $live_servers_id);
$obj->newPoster = 'plugin/Live/view/OnAir.jpg';

@unlink($global['systemRootPath'].$obj->file);
@unlink($global['systemRootPath'].$obj->fileThumbs);

$obj->error = false;

die(json_encode($obj));
