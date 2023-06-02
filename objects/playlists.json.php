<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
if (!User::isLogged()) {
    die();
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
header('Content-Type: application/json');
$TimeLog1 = "playList.json getAllFromUser " . User::getId();
TimeLogStart($TimeLog1);
session_write_close();
mysqlBeginTransaction();
TimeLogEnd($TimeLog1, __LINE__);
$row = PlayList::getAllFromUser(User::getId(), false);
TimeLogEnd($TimeLog1, __LINE__);
foreach ($row as $key => $value) {
    foreach ($row[$key]['videos'] as $key2 => $value2) {
        unset($row[$key]['videos'][$key2]['description']);
    }
}
TimeLogEnd($TimeLog1, __LINE__);
mysqlCommit();
TimeLogEnd($TimeLog1, __LINE__);
echo json_encode($row);
