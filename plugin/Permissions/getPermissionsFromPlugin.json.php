<?php

header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}
if(!User::isAdmin()){
    forbiddenPage("Not admin");
}
$permissions = array();
if($_REQUEST['plugins_id']){
    $obj = AVideoPlugin::getObjectDataIfEnabled("Permissions");
    $permissions = Users_groups_permissions::getAllFromPlugin($_REQUEST['plugins_id']);
}
die(json_encode($permissions));