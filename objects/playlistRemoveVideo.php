<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
enforceRateLimit('playlist_remove_video_legacy', 120, 60);
if (!User::isLogged()) {
    forbiddenPage('Permission denied', true);
}
$obj = new PlayList($_POST['playlist_id']);
if (!PlayLists::canManageAllPlaylists()) {
    if (User::getId() !== $obj->getUsers_id()) {
        forbiddenPage('Permission denied', true);
    }
}
$usersId = User::getId();
$playlistId = intval($_POST['playlist_id']);
$videoId = intval($_POST['video_id']);
$duplicateActionCacheKey = 'playlist/remove/legacy/' . $usersId . '/' . $playlistId . '/' . $videoId;
if (!empty(ObjectYPT::getCacheGlobal($duplicateActionCacheKey, 4, true))) {
    _error_log("playlist legacy remove duplicate suppressed users_id={$usersId} playlists_id={$playlistId} videos_id={$videoId} ip=" . getRealIpAddr(), AVideoLog::$SECURITY);
    echo '{"status":"1"}';
    exit;
}
$result = $obj->addVideo($videoId, false);
if (!empty($result)) {
    ObjectYPT::setCacheGlobal($duplicateActionCacheKey, 1);
}

echo '{"status":"'.$result.'"}';
