<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/userGroups.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}
$item = new UserGroups($_POST['id']);
echo '{"status":"'.$item->delete().'"}';
