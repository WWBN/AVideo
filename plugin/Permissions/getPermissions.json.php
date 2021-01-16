<?php

header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}
$permissions = array();
if(!User::isAdmin()){
    die(json_encode($permissions));
}
if($_REQUEST['users_groups_id']){
    $obj = AVideoPlugin::getObjectDataIfEnabled("Permissions");
    $permissions = Users_groups_permissions::getAllFromUserGorup($_REQUEST['users_groups_id']);
}
die(json_encode($permissions));