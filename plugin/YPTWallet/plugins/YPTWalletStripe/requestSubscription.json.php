<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/Subscription/Subscription.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("StripeYPT");
$pluginS = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$objS = $pluginS->getDataObject();

$obj= new stdClass();
$obj->error = true;
$obj->confirmCardPayment = false;
$obj->msg = "";
$obj->customer = false;
$obj->plans_id = intval(@$_REQUEST['plans_id']);

$invoiceNumber = uniqid();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['recurrentSubscription']['plans_id']);


if(empty($obj->plans_id)){
    forbiddenPage("Plan ID Not found");
}
$_SESSION['recurrentSubscription']['plans_id'] = $obj->plans_id;

if($obj->plans_id > 0  || !User::isAdmin()){
    $subs = new SubscriptionPlansTable($obj->plans_id);

    if(empty($subs)){
        forbiddenPage("Plan Not found");
    }
}
if(empty($_POST['stripeToken'])){
    forbiddenPage("stripeToken Not found");
}

if(!User::isLogged()){
    forbiddenPage("User not logged");

}
$users_id = User::getId();
//setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
_error_log("Request subscription setUpSubscription: ".  json_encode($_POST));
$payment = $plugin->setUpSubscription($obj->plans_id, $_POST['stripeToken']);
$obj->msg = $setUpSubscriptionErrorResponse;
$obj->payment = $payment;
_error_log("Request subscription setUpSubscription Done ");
if (!empty($payment) && !empty($payment->status) && ($payment->status=="active" || $payment->status=="trialing")) {
    if($payment->status=="trialing" && Subscription::isTrial($obj->plans_id)){
        Subscription::onTrial($users_id, $obj->plans_id);
    }
    $obj->error = false;
    $obj->subscription = $payment;
}else if (!empty($payment) && !empty($payment->status) && ($payment->status=="incomplete" && $payment->customer)) {
    _error_log("Request subscription Stripe is incomplete ");
    $obj->confirmCardPayment = true;
    $obj->msg = "Please Confirm your Payment";
    $obj->customer = $payment->customer;
}
die(json_encode($obj));
