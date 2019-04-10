<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');

if(empty($_POST['current'])){
    $_POST['current'] = 1;
}
if(empty($_POST['rowCount'])){
    $_POST['rowCount'] = 10;
}
$users = User::getAllUsers();
$total = User::getTotalUsers();
$json = json_encode($users);

if(empty($json)){
    error_log("users.json error: ".print_r($users, true));
    foreach ($users as $key => $value) {
        $users[$key]['about'] = "";
    }
    $json = json_encode($users);
}

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. $json .'}';
