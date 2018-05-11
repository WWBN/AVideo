<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet_log.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin()) {
    $obj->msg = ("Is not Admin");
    die(json_encode($obj));
}
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    $obj->msg = ("Plugin not enabled");
    die(json_encode($obj));
}

if(empty($_POST['wallet_log_id'])){
    $obj->msg = ("wallet_log_id ID is empty");
    die(json_encode($obj));
}

$walletLog = new WalletLog($_POST['wallet_log_id']);
$walletLog->setStatus($_POST['status']);
if($walletLog->save()){
    $obj->error = false;
}

die(json_encode($obj));
