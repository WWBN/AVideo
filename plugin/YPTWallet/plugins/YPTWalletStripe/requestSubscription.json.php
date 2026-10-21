<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    forbiddenPage('Login required');
}
forbidIfInvalidToken();
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

$users_id = User::getId();
//setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement')
_error_log('Request subscription: users_id=' . $users_id . ' plans_id=' . $obj->plans_id);
try {
    // A lost response must not send an already activated customer through payment again.
    $activeAccess = method_exists('SubscriptionTable', 'getCheckoutAccess')
        ? SubscriptionTable::getCheckoutAccess($users_id, $obj->plans_id)
        : SubscriptionTable::getUserPlan($obj->plans_id, $users_id);
    if (!empty($activeAccess)) {
        $obj->error = false;
        $obj->already_active = true;
        $obj->msg = __('Your subscription is already active. No new charge was made.');
        echo json_encode($obj);
        exit;
    }
    $payment = $plugin->setUpSubscription($obj->plans_id, $_POST['stripeToken']);
} catch (Throwable $th) {
    _error_log('Stripe subscription checkout failed: ' . $th->getMessage(), AVideoLog::$ERROR);
    $obj->msg = __('Could not start the subscription. Please check your account before retrying.');
    echo json_encode($obj);
    exit;
}
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
