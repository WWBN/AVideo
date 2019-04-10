<?php
// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
error_log("PayPalIPN Start");
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = YouPHPTubePlugin::getObjectData("YPTWallet");
$paypal = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");

$ipn = PayPalYPT::IPNcheck();
if(!$ipn){
    die("IPN Fail");
}
$obj= new stdClass();
$obj->error = true;
if(empty($_POST["recurring_payment_id"])){
    $users_id = User::getId();

    $invoiceNumber = uniqid();

    $payment = $paypal->execute();
    //var_dump($amount);
    if (!empty($payment)) {
        $amount = PayPalYPT::getAmountFromPayment($payment);
        $plugin->addBalance($users_id, $amount->total, "Paypal payment", "PayPalIPN");
        $obj->error = false;
        error_log("PayPalIPN: Executed ".json_encode($payment));
        //header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
    }else{
        error_log("PayPalIPN: Fail");
        //header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
}else{
    // check for the recurrement payment
    $subscription = YouPHPTubePlugin::loadPluginIfEnabled("Subscription");
    if(!empty($subscription)){
        $row = Subscription::getFromAgreement($_POST["recurring_payment_id"]);
        $users_id = $row['users_id']; 
        $payment_amount = $_POST['mc_gross'];
        $payment_currency = $_POST['mc_currency'];
        if($walletObject->currency===$payment_currency){
            $plugin->addBalance($users_id, $payment_amount, "Paypal recurrent", json_encode($_POST));
            Subscription::renew($users_id, $row['subscriptions_plans_id']);
            $obj->error = false;
        }
    }
}

error_log("PayPalIPN: ".json_encode($obj));
error_log("PayPalIPN: POST ".json_encode($_POST));
error_log("PayPalIPN: GET ".json_encode($_GET));
error_log("PayPalIPN END");

?>