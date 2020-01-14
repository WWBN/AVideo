<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
if(empty($_GET['users_id'])){
    die("You need a user");
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once './playlist.php';
header('Content-Type: application/json');
$row = PlayList::getAllFromUser($_GET['users_id'], false);
echo json_encode($row);