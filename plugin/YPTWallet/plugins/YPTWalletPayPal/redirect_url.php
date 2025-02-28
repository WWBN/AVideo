<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
// how to get the users_ID from the PayPal call back IPN?
$users_id = User::getId();

$invoiceNumber = _uniqid();

$payment = $paypal->execute();

//check if there is a token and this token has a user (recurrent payments)
_error_log("Redirect_URL line:" . __LINE__ . " Start ");
if (!empty($_GET['token'])) {
    _error_log("Redirect_URL line:" . __LINE__ . " \$_GET['token'] " . $_GET['token']);
    if (AVideoPlugin::isEnabledByName("Subscription")) {
        _error_log("Redirect_URL line:" . __LINE__ . " \$payment->getId " . $payment->getId());
        $subscription = Subscription::getFromAgreement($payment->getId());

        if (!empty($subscription)) {
            $users_id = $subscription['users_id'];
            _error_log("Redirect_URL line:" . __LINE__ . " \$subscription " . json_encode($subscription));
        } else {
            _error_log("Redirect_URL line:" . __LINE__ . " \$subscription " . $_SESSION['recurrentSubscription']['plans_id']);
            if (!empty($users_id) && !empty($_SESSION['recurrentSubscription']['plans_id'])) {
                //save token
                $subscription = SubscriptionTable::getOrCreateSubscription($users_id, $_SESSION['recurrentSubscription']['plans_id'], $payment->getId());
                _error_log("Redirect_URL line:" . __LINE__ . " \$subscription " . print_r($subscription, true));
                unset($_SESSION['recurrentSubscription']['plans_id']);
            }
        }
    }
}
_error_log("Redirect_URL line:" . __LINE__ . " END ");

if (empty($users_id)) {
    _error_log("Redirect URL error, Not found user or token");
    die();
}

//var_dump($amount);
$obj = new stdClass();
$obj->error = true;
_error_log("Redirect URL try Payment");
if (!empty($payment)) {
    _error_log("Redirect URL try Payment Success");
    $amount = PayPalYPT::getAmountFromPayment($payment);
    $plugin->addBalance($users_id, $amount->total, "Paypal payment", json_encode($payment));
    if (!empty($subscription)) {
        //if empty amount check if it is a trial
        $trialDays = Subscription::isTrial($subscription['subscriptions_plans_id']);
        _error_log("Redirect URL amount->total: $amount->total");
        _error_log("Redirect URL trialDays: $trialDays");
        if (empty($amount->total) && !empty($trialDays)) {
            _error_log("Redirect URL trigger ontrial");
            Subscription::onTrial($subscription['users_id'], $subscription['subscriptions_plans_id']);
        } else {
            _error_log("Redirect URL trigger ontrial FAIL " . intval(empty($amount->total)) . " && " . intval(!empty($trialDays)));
        }
    }

    $obj->error = false;
    if (!empty($_SESSION['addFunds_Success'])) {
        if (!empty($subscription)) {
            Subscription::renew($subscription['users_id'], $subscription['subscriptions_plans_id']);
        }
        header("Location: {$_SESSION['addFunds_Success']}");
        unset($_SESSION['addFunds_Success']);
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
    }
} else {
    _error_log("Redirect URL try Payment Error");
    if (!empty($_SESSION['addFunds_Fail'])) {
        header("Location: {$_SESSION['addFunds_Fail']}");
        unset($_SESSION['addFunds_Fail']);
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
}
_error_log(json_encode($obj));
_error_log("PAYPAL redirect_url GET:  " . json_encode($_GET));
_error_log("PAYPAL redirect_url POST: " . json_encode($_POST));
?>