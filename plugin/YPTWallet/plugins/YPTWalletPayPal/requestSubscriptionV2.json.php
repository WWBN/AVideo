<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
$pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$objS = $pluginS->getDataObject();

$obj= new stdClass();
$obj->error = true;
$obj->msg = '';

$invoiceNumber = uniqid();

//$params = array('total'=>$total, 'currency'=>$currency, 'frequency'=>$frequency, 'interval'=>$interval, 'name'=>$name, 'json'=>$json, 'trialDays'=>$trialDays);

if(empty($_REQUEST['hash'])){
    $obj->msg = 'Hash is empty';
    die(json_encode($obj));
}

$json = decryptString($_REQUEST['hash']);
if(empty($json)){
    $obj->msg = 'Hash not decrypted';
    die(json_encode($obj));
}

$json = _json_decode($json);

$value = floatval($json->total);
if(empty($value)){
    $obj->msg = 'Value is empty';
    die(json_encode($obj));
}

//setUpSubscriptionV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $trialDays = 0)
$payment = $plugin->setUpSubscriptionV2($json->total, $json->currency, $json->frequency, $json->interval, $json->name, $json->json, $json->trialDays);
if (!empty($payment)) {
    $obj->error = false;
    $obj->approvalLink = $payment->getApprovalLink();
    YPTWallet::setAddFundsSuccessRedirectURL($json->addFunds_Success);
}
die(json_encode($obj));