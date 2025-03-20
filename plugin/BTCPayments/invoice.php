<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    forbiddenPage("You can not do this");
    exit;
}

$global['bypassSameDomainCheck'] = 1;
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWalletBTC");
$obj = AVideoPlugin::getObjectData("YPTWalletBTC");

if(empty($_REQUEST['orderId'])){
    $_REQUEST['orderId'] = intval(User::getId()).'-'.date('YmdHis');
}

$invoice = BTCPayments::setUpPayment($_GET['value'], User::getId(), array('description'=>$_REQUEST['description'], 'orderId' => $_REQUEST['orderId']), "{$global['webSiteRootURL']}{$_REQUEST['redirectUrl']}");

//var_dump($invoice);exit;

header("Location: {$invoice['invoice']['checkoutLink']}");
