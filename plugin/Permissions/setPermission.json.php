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

$intvalList = array('users_groups_id','plugins_id','type','isEnabled');
foreach ($intvalList as $value) {
    if($_REQUEST[$value]==='true'){
        $_REQUEST[$value] = 1;
    }else{
        $_REQUEST[$value] = intval($_REQUEST[$value]);
    }
}

$obj = new stdClass();
$obj->id = Permissions::setPermission($_REQUEST['users_groups_id'], $_REQUEST['plugins_id'], $_REQUEST['type'], $_REQUEST['isEnabled']);

die(json_encode($obj));




