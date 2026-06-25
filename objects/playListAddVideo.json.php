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

$isAddRequest = !_empty($_REQUEST['add']);
enforceRateLimit($isAddRequest ? 'playlist_add_video' : 'playlist_remove_video', 180, 60);

if (!User::isLogged()) {
    forbiddenPage('Permission denied', true);
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
    forbiddenPage('This is not your playlist', true);
}

$usersId = User::getId();
$videosId = intval($_REQUEST['videos_id']);
$playlistsId = intval($_REQUEST['playlists_id']);
$duplicateActionCacheKey = 'playlist/mutation/' . $usersId . '/' . $playlistsId . '/' . $videosId . '/' . intval($isAddRequest);
if (!empty(ObjectYPT::getCacheGlobal($duplicateActionCacheKey, 4, true))) {
    _error_log("playlist mutation duplicate suppressed users_id={$usersId} playlists_id={$playlistsId} videos_id={$videosId} add=" . intval($isAddRequest) . " ip=" . getRealIpAddr(), AVideoLog::$SECURITY);
    $obj->error = false;
    $obj->status = true;
    $obj->msg = __('Duplicate playlist request ignored');
    $obj->add = $isAddRequest;
    $obj->videos_id = $videosId;
    $obj->users_id = $playList->getUsers_id();
    $obj->id = $playList->getId();
    $obj->type = $playList->getStatus();
    die(json_encode($obj));
}

$obj->add = $isAddRequest;
$obj->videos_id = $videosId;
$obj->status = $playList->addVideo($obj->videos_id, $obj->add);
if (!empty($obj->status)) {
    ObjectYPT::setCacheGlobal($duplicateActionCacheKey, 1);
}
$obj->users_id = $playList->getUsers_id();
$obj->id = $playList->getId();
$obj->error = empty($obj->status);
$obj->type = $playList->getStatus();

//log_error("videos id: ".$_REQUEST['videos_id']." playlist_id: ".$_REQUEST['playlists_id']);
die(json_encode($obj));
