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

$obj = new stdClass();
$obj->error = true;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $obj->msg = 'POST method required';
    die(json_encode($obj));
}

if (!isGlobalTokenValid()) {
    http_response_code(403);
    $obj->msg = 'Invalid or missing CSRF token';
    die(json_encode($obj));
}

$intvalList = array('users_groups_id','plugins_id','type','isEnabled');
foreach ($intvalList as $value) {
    if($_POST[$value]==='true'){
        $_POST[$value] = 1;
    }else{
        $_POST[$value] = intval($_POST[$value]);
    }
}

$obj->error = false;
$obj->id = Permissions::setPermission($_POST['users_groups_id'], $_POST['plugins_id'], $_POST['type'], $_POST['isEnabled']);

die(json_encode($obj));




