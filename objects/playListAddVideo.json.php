<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

$obj = new stdClass();
$obj->error = true;
$obj->status = 0;

if (!User::isLogged()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}


$plugin = YouPHPTubePlugin::loadPluginIfEnabled("PlayLists");
if(empty($plugin)){
    $obj->msg = "Plugin not enabled";
    die(json_encode($obj));
}

if(!PlayLists::canAddVideoOnPlaylist($_POST['videos_id'])){
    $obj->msg = "You can not add this video on playlist";
    die(json_encode($obj));
}

$playList = new PlayList($_POST['playlists_id']);
if(empty($playList || User::getId()!=$playList->getUsers_id()) || empty($_POST['videos_id'])){
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

$obj->error = false;
$obj->status = $playList->addVideo($_POST['videos_id'], $_POST['add']);

log_error("videos id: ".$_POST['videos_id']." playlist_id: ".$_POST['playlists_id']);
die(json_encode($obj));
