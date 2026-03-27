<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isLogged()) {
    die("Is not logged");
}
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    die("Plugin not enabled");
}

header('Content-Type: application/json');

$_POST['current'] = getCurrentPage();
$_POST['rowCount'] = getRowCount();
// Admins receive full rows; non-admins get display-only fields (see YPTWallet::getAllUsersPublicFields).
$users = User::isAdmin() ? $plugin->getAllUsers() : $plugin->getAllUsersPublicFields();

$total = User::getTotalUsers(true);
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($users).'}';
