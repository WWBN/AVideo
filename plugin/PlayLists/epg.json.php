<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
header('Content-Type: application/json');

if (!empty($_REQUEST['playlists_id'])) {
    $json = PlayLists::getPlayListEPG($_REQUEST['playlists_id'], @$_REQUEST['users_id']);
    die(json_encode($json));
} else if (!empty($_REQUEST['users_id'])) {
    $channel = PlayLists::getUserEPG($_REQUEST['users_id']);
    if (!empty($channel['playlists'])) {
        die(json_encode($channel['playlists']));
    }else{
        die(json_encode(array()));
    }
} else {
    $channels = PlayLists::getSiteEPGs(true);

    die(json_encode($channels));
}
?>