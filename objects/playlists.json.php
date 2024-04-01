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
_session_write_close();
//mysqlBeginTransaction();
$row = PlayList::getAllFromUser(User::getId(), false);
//var_dump($row);exit;
foreach ($row as $key => $value) {
    foreach ($row[$key]['videos'] as $key2 => $value2) {
        unset($row[$key]['videos'][$key2]['description']);
    }
}
//mysqlCommit();
echo json_encode($row);
