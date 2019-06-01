<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("StripeYPT");
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

if(empty($_POST['stripeToken'])){
    die("stripeToken Not found");
}

//setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
$payment = $plugin->setUpSubscription($_POST['plans_id'], $_POST['stripeToken']);
if (!empty($payment) && !empty($payment->status) && $payment->status=="active") {
    $obj->error = false;
    $obj->subscription = $payment;
}
die(json_encode($obj));