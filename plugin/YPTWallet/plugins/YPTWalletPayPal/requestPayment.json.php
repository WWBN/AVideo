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

$invoiceNumber = uniqid();

$description = $config->getWebSiteTitle()." Payment";

$payment = $plugin->setUpPayment($invoiceNumber, $objS->RedirectURL, $objS->CancelURL, $_POST['value'], $objS->currency, $description);

if (!empty($payment)) {
    $obj->error = false;
    $obj->approvalLink = $payment->getApprovalLink();
}
die(json_encode($obj));