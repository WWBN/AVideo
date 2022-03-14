<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$users_id = User::getId();

if (!User::canStream()) {
    $obj->msg = __("User cannot stream");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$playlistPlugin = AVideoPlugin::getObjectDataIfEnabled('PlayLists');

if (empty($playlistPlugin)) {
    $obj->msg = __("Programs plugin not enabled");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$live = AVideoPlugin::getObjectDataIfEnabled("Live");
if (empty($live)) {
    $obj->msg = __("Live Plugin is not enabled");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$playlists_id = intval($_REQUEST['playlists_id']);
if (empty($playlists_id)) {
    $obj->msg = __("Programs id error");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

if(!PlayLists::canManagePlaylist($playlists_id)){
    $obj->msg = __("Programs does not belong to you");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$pl = new PlayList($playlists_id);

$pl->setShowOnTV($_REQUEST['showOnTV']);

$obj->error = empty($pl->save());
if(empty($obj->error)){
    if(empty($pl->getShowOnTV())){
        $obj->msg = __("showOnTV is OFF");
    }else{
        $obj->msg = __("showOnTV is ON");
    }
}

die(json_encode($obj));