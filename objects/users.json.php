<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
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

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($users).'}';
