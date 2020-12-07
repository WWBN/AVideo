<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';


if (!empty($_REQUEST['playlists_id'])) {
    $json = PlayLists::getPlayListEPG($_REQUEST['playlists_id'], @$_REQUEST['users_id']);
    if(!empty($json['programme'])){
        echo PlayLists::epgFromPlayList($json['programme'], $json['generated'], $json['created']);
    }
} else if (!empty($_REQUEST['users_id'])) {
    $channel = PlayLists::getUserEPG($_REQUEST['users_id']);
    if (!empty($channel['playlists'])) {
        foreach ($channel['playlists'] as $json) {
            if (!empty($json['programme'])) {
                echo PlayLists::epgFromPlayList($json['programme'], $channel['generated'], $json['created'], false, true, true);
            }
        }
    }
}else{
    $channels = PlayLists::getSiteEPGs();
    foreach ($channels as $channel) { 
        if (!empty($channel['playlists'])) {
            foreach ($channel['playlists'] as $json) {
                if (!empty($json['programme'])) { 
                    echo PlayLists::epgFromPlayList($json['programme'], $channels['generated'], $json['created']);
                }
            }
        }
    }
}

?>