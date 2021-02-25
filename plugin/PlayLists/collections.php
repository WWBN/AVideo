<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';

if (!User::isAdmin() && !PlayList::canSee($_GET['playlists_id'], User::getId())) {
    die('{"error":"' . __("Permission denied") . '"}');
}

$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);


