<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
$pluginS = AVideoPlugin::loadPlugin("YPTWallet");
$objS = $pluginS->getDataObject();

$obj= new stdClass();
$obj->error = true;

if(empty($_POST['value'])){
    $obj->msg = "Invalid Value";
    die(json_encode($obj));
}

if(!empty($_REQUEST['videos_id'])){
    YPTWallet::setAddFundsSuccessRedirectToVideo($_REQUEST['videos_id']);
}

$invoiceNumber = _uniqid();

$description = $config->getWebSiteTitle()." Payment";

$RedirectURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletPayPal/redirect_url.php";
$CancelURL = "{$global['webSiteRootURL']}plugin/YPTWallet/plugins/YPTWalletPayPal/cancel_url.php";

$payment = $plugin->setUpPayment($invoiceNumber, $RedirectURL, $CancelURL, $_POST['value'], $objS->currency, $description);

if (!empty($payment)) {
    $obj->error = false;
    $obj->approvalLink = $payment->getApprovalLink();
}
die(json_encode($obj));