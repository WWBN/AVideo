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

if(empty($_REQUEST['interval'])){
    $interval = 1;
}else{
    $interval = $_REQUEST['interval'];
}
if(empty($_POST['value'])){ 
    $obj->msg = "Invalid Value";
    die(json_encode($obj));
}
$invoiceNumber = uniqid();
if(empty($_REQUEST['paymentName'])){
    $paymentName = "Recurrent Payment";
}else{
    $paymentName = $_REQUEST['paymentName'];
}
@session_write_close();
@session_start();
unset($_SESSION['recurrentSubscription']['plans_id']);
if(!empty($_POST['plans_id'])){
    $_SESSION['recurrentSubscription']['plans_id'] = $_POST['plans_id'];    
}

//setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
$payment = $plugin->setUpSubscription($invoiceNumber, $objS->RedirectURL, $objS->CancelURL, $_POST['value'], $objS->currency, "Day",$interval, $paymentName);
if (!empty($payment)) {
    if(YouPHPTubePlugin::isEnabledByName('Subscription')){
        // create a subscription here
        Subscription::createEmptySubscription($payment->getId(), $_POST['plans_id'], User::getId());
    }
    $obj->error = false;
    $obj->approvalLink = $payment->getApprovalLink();
}
die(json_encode($obj));