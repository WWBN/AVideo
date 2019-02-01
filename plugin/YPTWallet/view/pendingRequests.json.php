<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet_log.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}


header('Content-Type: application/json');

if(!empty($_POST['sort']['valueText'])){
    $_POST['sort']['value'] = $_POST['sort']['valueText'];
    unset($_POST['sort']['valueText']);
}

$row = WalletLog::getAllFromWallet(0,true,'pending');
$total = WalletLog::getTotalFromWallet(0,true,'pending');
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($row).'}';