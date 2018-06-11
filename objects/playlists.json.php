<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
header('Content-Type: application/json');
$row = PlayList::getAllFromUser(User::getId(), false);
echo json_encode($row);
