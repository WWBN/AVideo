<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'userGroups.php';
$obj = new UserGroups(@$_POST['id']);
$obj->setGroup_name($_POST['group_name']);
echo '{"status":"'.$obj->save().'"}';
