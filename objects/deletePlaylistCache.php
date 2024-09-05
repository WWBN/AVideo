<?php
//error_reporting(0);
//php get_videos_paths.php example_video 0
header('Content-Type: application/json');

require_once __DIR__ . '/../videos/configuration.php';

if (!isCommandLineInterface()) {
    forbiddenPage('Command line only');
}

if ($argc < 2) {
    die("Usage: php objects/deletePlaylistCache.php playlists_id videos_id\n");
}

require_once $global['systemRootPath'] . 'objects/playlist.php';

PlayList::deleteCacheDir($this->id, true);
//_error_log('playlistSort addVideo line=' . __LINE__);
PlayList::removeCache($videos_id);
