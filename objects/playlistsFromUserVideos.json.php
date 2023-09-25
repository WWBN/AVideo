<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
if (empty($_POST['users_id'])) {
    die('You need a user');
}
if (session_status() !== PHP_SESSION_NONE) {
    session_write_close();
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once './playlist.php';
header('Content-Type: application/json');
//mysqlBeginTransaction();
if (is_array($_POST['videos_id'])) {
    $rows = [];
    foreach ($_POST['videos_id'] as $value) {
        $rows[] = ['videos_id' => $value, 'playlists' => PlayList::getAllFromUserVideo($_POST['users_id'], $value, false)];
    }
    echo json_encode($rows);
} else {
    $row = PlayList::getAllFromUserVideo($_POST['users_id'], $_POST['videos_id'], false);
    echo json_encode($row);
}
//mysqlCommit();