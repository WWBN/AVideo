<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
header('Content-Type: application/json');

function epgPlaylistId($playlist)
{
    return is_array($playlist) ? @$playlist['playlists_id'] : @$playlist->playlists_id;
}

// the live stream key/liveDir are credentials, not schedule metadata - never expose them here
function epgRedactPlaylist(&$playlist)
{
    if (is_array($playlist)) {
        unset($playlist['key'], $playlist['liveDir']);
    } elseif (is_object($playlist)) {
        unset($playlist->key, $playlist->liveDir);
    }
}

// mirrors the showOnTV() filter already applied by epg.xml.php/iptv.php, so private playlists aren't leaked here
function epgFilterAndRedactPlaylists(&$playlists)
{
    foreach ($playlists as $key => &$playlist) {
        $playlists_id = epgPlaylistId($playlist);
        if (empty($playlists_id) || !PlayLists::showOnTV($playlists_id)) {
            unset($playlists[$key]);
            continue;
        }
        epgRedactPlaylist($playlist);
    }
    unset($playlist);
}

if (!empty($_REQUEST['playlists_id'])) {
    $playlists_id = intval($_REQUEST['playlists_id']);
    if (!PlayLists::showOnTV($playlists_id)) {
        die(json_encode(array()));
    }
    $json = PlayLists::getPlayListEPG($playlists_id, @$_REQUEST['users_id']);
    epgRedactPlaylist($json);
    die(json_encode($json));
} else if (!empty($_REQUEST['users_id'])) {
    $channel = PlayLists::getUserEPG($_REQUEST['users_id']);
    if (!empty($channel['playlists'])) {
        epgFilterAndRedactPlaylists($channel['playlists']);
        die(json_encode($channel['playlists']));
    }else{
        die(json_encode(array()));
    }
} else {
    $channels = PlayLists::getSiteEPGs(true);
    foreach ($channels as &$channel) {
        if (is_array($channel) && !empty($channel['playlists'])) {
            epgFilterAndRedactPlaylists($channel['playlists']);
        }
    }
    unset($channel);

    die(json_encode($channels));
}
?>
