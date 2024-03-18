<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
//_session_write_close();
allowOrigin();
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
if (empty($_GET['users_id'])) {
    die("You need a user");
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once './playlist.php';
header('Content-Type: application/json');
_error_log('playlistsFromUserVideos getAllFromUser '.$_GET['users_id']);
//setDefaultSort('created', 'DESC');
//setRowCount(50);
//_session_write_close();
$row = PlayList::getAllFromUser($_GET['users_id'], false);
foreach ($row as $key => $value) {
    foreach ($row[$key]['videos'] as $key2 => $value2) {
        unset($row[$key]['videos'][$key2]['description']);
    }
}
_error_log('playlistsFromUserVideos getAllFromUser '.count($row));
echo json_encode($row);
