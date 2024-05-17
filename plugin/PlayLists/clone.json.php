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

if(!User::isLogged()){
    forbiddenPage('Must login');
}

$users_id = User::getId();

$playlistPlugin = AVideoPlugin::getObjectDataIfEnabled('PlayLists');

if (empty($playlistPlugin)) {
    forbiddenPage('Programs plugin not enabled');
}

$playlists_id = intval($_REQUEST['playlists_id']);
if (empty($playlists_id)) {
    forbiddenPage('Programs id error');
}

$pl = new PlayList($playlists_id);
if (User::getId() != $pl->getUsers_id() && !PlayLists::canManageAllPlaylists()) {
    forbiddenPage('Programs does not belong to you');
}

$obj->new_playlist_id = PlayList::clone($playlists_id);

$obj->error = empty($obj->new_playlist_id);

die(json_encode($obj));