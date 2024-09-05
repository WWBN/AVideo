<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
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

$plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");
if (empty($plugin)) {
    $obj->msg = "Plugin not enabled";
    die(json_encode($obj));
}

if (!PlayLists::canAddVideoOnPlaylist($_REQUEST['videos_id'])) {
    $obj->msg = "You can not add this video on playlist";
    die(json_encode($obj));
}

$playList = new PlayList($_REQUEST['playlists_id']);
if (empty($playList) || empty($_REQUEST['videos_id'])) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

if(!PlayLists::canManageAllPlaylists() && User::getId() !== $playList->getUsers_id() ){
    $obj->msg = __("This is not your playlist");
    die(json_encode($obj));
}

$obj->add = !_empty($_REQUEST['add']);
$obj->videos_id = intval($_REQUEST['videos_id']);
$obj->status = $playList->addVideo($obj->videos_id, $obj->add);
$obj->users_id = $playList->getUsers_id();
$obj->id = $playList->getId();
$obj->error = empty($obj->status);

//log_error("videos id: ".$_REQUEST['videos_id']." playlist_id: ".$_REQUEST['playlists_id']);
die(json_encode($obj));
