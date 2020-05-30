<?php
// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
_error_log("PayPalIPN Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

$ipn = PayPalYPT::IPNcheck();
if(!$ipn){
    die("IPN Fail");
}
$obj= new stdClass();
$obj->error = true;
if(empty($_POST["recurring_payment_id"])){
    _error_log("PayPalIPN: recurring_payment_id EMPTY ");
    $users_id = User::getId();

    $invoiceNumber = uniqid();

    $payment = $paypal->execute();
    //var_dump($amount);
    if (!empty($payment)) {
        $amount = PayPalYPT::getAmountFromPayment($payment);
        $plugin->addBalance($users_id, $amount->total, "Paypal payment", "PayPalIPN");
        $obj->error = false;
        _error_log("PayPalIPN: Executed ".json_encode($payment));
        //header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
    }else{
        _error_log("PayPalIPN: Fail");
        //header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
}else{
    _error_log("PayPalIPN: recurring_payment_id = {$_POST["recurring_payment_id"]} ");
    // check for the recurrement payment
    $subscription = AVideoPlugin::loadPluginIfEnabled("Subscription");
    if(!empty($subscription)){
        $row = Subscription::getFromAgreement($_POST["recurring_payment_id"]);
        _error_log("PayPalIPN: user found from recurring_payment_id (users_id = {$row['users_id']}) ");
        $users_id = $row['users_id']; 
        $payment_amount = empty($_POST['mc_gross'])?$_POST['amount']:$_POST['mc_gross'];
        $payment_currency = empty($_POST['mc_currency'])?$_POST['currency_code']:$_POST['mc_currency'];
        if($walletObject->currency===$payment_currency){
            $plugin->addBalance($users_id, $payment_amount, "Paypal recurrent", json_encode($_POST));
            Subscription::renew($users_id, $row['subscriptions_plans_id']);
            $obj->error = false;
        }else{
            _error_log("PayPalIPN: FAIL currency check $walletObject->currency===$payment_currency ");
        }
    }
}

_error_log("PayPalIPN: ".json_encode($obj));
_error_log("PayPalIPN: POST ".json_encode($_POST));
_error_log("PayPalIPN: GET ".json_encode($_GET));
_error_log("PayPalIPN END");

?>