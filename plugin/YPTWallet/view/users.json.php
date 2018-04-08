<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isAdmin()) {
    die("Is not admin");
}
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    die("Plugin not enabled");
}

header('Content-Type: application/json');

$users = User::getAllUsers();

foreach ($users as $key => $value) {
    $users[$key]['balance'] = $plugin->getBalance($value['id']);
    $users[$key]['photo'] = User::getPhoto($value['id']);
}

$total = User::getTotalUsers();

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($users).'}';
