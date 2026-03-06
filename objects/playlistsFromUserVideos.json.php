<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
allowOrigin();
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
if (empty($_REQUEST['users_id'])) {
    forbiddenPage('You need a user');
}

//setRowCount(100);
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once './playlist.php';
header('Content-Type: application/json');
//setDefaultSort('created', 'DESC');
//mysqlBeginTransaction();

// Only the playlist owner or an admin can see non-public playlists
$requestedUserId = intval($_REQUEST['users_id']);
$publicOnly = true;
if (User::isLogged() && (User::getId() == $requestedUserId || User::isAdmin())) {
    $publicOnly = false;
}

if (is_array($_REQUEST['videos_id'])) {
    setRowCount(500/count($_REQUEST['videos_id']));
    $rows = [];
    foreach ($_REQUEST['videos_id'] as $value) {
        $rows[] = ['videos_id' => $value, 'playlists' => PlayList::getAllFromUserVideo($requestedUserId, $value, $publicOnly)];
    }
    echo json_encode($rows);
} else {
    $row = PlayList::getAllFromUserVideo($requestedUserId, $_REQUEST['videos_id'], $publicOnly);
    echo json_encode($row);
}
//mysqlCommit();
