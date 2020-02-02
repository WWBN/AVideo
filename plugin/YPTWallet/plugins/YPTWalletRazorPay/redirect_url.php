<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$razorPay = AVideoPlugin::loadPluginIfEnabled("RazorPayYPT");
$objS = $plugin->getDataObject();
$obj = $razorPay->getDataObject();

$displayCurrency = $objS->currency;
$users_id = User::getId();

require_once ($global['systemRootPath'] . 'plugin/RazorPayYPT/razorpay-php/Razorpay.php');

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$success = true;
$json = file_get_contents('php://input');
$body = json_decode($json);
_error_log("RazorPay redirect_url start:  $json");

$error = "Payment Failed";

$api = new Api($obj->api_key, $obj->api_secret);
// payment
if(!empty($body->error[code])){
    if(!empty($_SERVER["HTTP_REFERER"])){
        header("Location: {$_SERVER["HTTP_REFERER"]}&msg={$body->error[description]}");
    }else{
        header("Location: {$global['webSiteRootURL']}?msg={$body->error[description]}");
    }
}else
if (!empty($_POST['razorpay_payment_id']) && !empty($_POST['razorpay_order_id'])) {

    try {
// Please note that the razorpay order ID must
// come from a trusted source (session here, but
// could be database or something else)
        $attributes = array(
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    } catch (SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
        _error_log("RazorPay redirect_url:  {$error}");
    }
    if (!empty($_POST['razorpay_payment_id']) && $success === true) {
        $api = new Api($obj->api_key, $obj->api_secret);
        $payment = $api->payment->fetch($_POST['razorpay_payment_id']);
        if ($payment->currency == $displayCurrency) {
            $plugin->addBalance($users_id, $payment->amount / 100, "RazorPay payment: ", json_encode($attributes));
            header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
        } else {
            header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
        }
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
} else if (!empty($_POST['razorpay_payment_id']) && !empty($_POST['razorpay_subscription_id'])) { // this is for the subscription
    try {
// Please note that the razorpay order ID must
// come from a trusted source (session here, but
// could be database or something else)
        $attributes = array(
            'razorpay_subscription_id' => $_POST['razorpay_subscription_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    } catch (SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
        _error_log("RazorPay redirect_url:  {$error}");
    }
    if (!empty($_POST['razorpay_payment_id']) && $success === true) {
        $api = new Api($obj->api_key, $obj->api_secret);
        $payment = $api->payment->fetch($_POST['razorpay_payment_id']);
        if ($payment->currency == $displayCurrency) {
            AVideoPlugin::isEnabledByName('Subscription');
            $plugin->addBalance($users_id, $payment->amount / 100, "RazorPay payment for subscription: ", json_encode($attributes));
            $currentSubscription = SubscriptionTable::getSubscription(User::getId(), $payment->notes->plans_id, false, false);
            if (empty($currentSubscription)) {
                // create a subscription here
                Subscription::getOrCreateGatewaySubscription(User::getId(), $payment->notes->plans_id, SubscriptionTable::$gatway_razorpay, $payment->id);

                if (Subscription::isTrial($payment->notes->plans_id)) {
                    Subscription::onTrial(User::getId(), $payment->notes->plans_id);
                }else{
                    Subscription::renew(User::getId(), $payment->notes->plans_id);
                }
            }else{
                Subscription::renew(User::getId(), $payment->notes->plans_id);
            }
            header("Location: {$global['webSiteRootURL']}plugin/Subscription/showPlans.php?status=success");
        } else {
            header("Location: {$global['webSiteRootURL']}plugin/Subscription/showPlans.php?status=fail");
        }
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/Subscription/showPlans.php?status=fail");
    }
}else{
    
        _error_log("RazorPay nothing to process");
    
}

_error_log(json_encode($obj));
_error_log("RazorPay redirect_url GET:  " . json_encode($_GET));
_error_log("RazorPay redirect_url POST: " . json_encode($_POST));
?>