<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isLogged()) {
    die("Is not logged");
}
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    die("Plugin not enabled");
}

header('Content-Type: application/json');

$users = $plugin->getAllUsers();

$total = User::getTotalUsers(true);

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($users).'}';
