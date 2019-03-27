<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");
$pluginS = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$objS = $pluginS->getDataObject();

$obj= new stdClass();
$obj->error = true;

$invoiceNumber = uniqid();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['recurrentSubscription']['plans_id']);
if(!empty($_POST['plans_id'])){
    $_SESSION['recurrentSubscription']['plans_id'] = $_POST['plans_id'];    
}

$subs = new SubscriptionPlansTable($_POST['plans_id']);

if(empty($subs)){
    die("Plan Not found");
}

$interval = $subs->getHow_many_days();
$price = $subs->getPrice();
$paymentName = $subs->getName();
if(empty($paymentName)){
    $paymentName = "Recurrent Payment";
}
//setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
$payment = $plugin->setUpSubscription($invoiceNumber, $objS->RedirectURL, $objS->CancelURL, $price, $objS->currency, "Day",$interval, $paymentName);
if (!empty($payment)) {
    if(YouPHPTubePlugin::isEnabledByName('Subscription')){
        // create a subscription here
        Subscription::getOrCreateSubscription(User::getId(), $_POST['plans_id'] , $payment->getId());
    }
    $obj->error = false;
    $obj->approvalLink = $payment->getApprovalLink();
}
die(json_encode($obj));