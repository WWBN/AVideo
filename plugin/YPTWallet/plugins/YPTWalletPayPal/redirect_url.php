<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$paypal = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");
// how to get the users_ID from the PayPal call back IPN?
$users_id = User::getId();

$invoiceNumber = uniqid();

$payment = $paypal->execute();
//var_dump($amount);
$obj = new stdClass();
$obj->error = true;
if (!empty($payment)) {
    $amount = PayPalYPT::getAmountFromPayment($payment);
    $plugin->addBalance($users_id, $amount->total, "Paypal payment", json_encode($payment));
    $obj->error = false;
    if (!empty($_SESSION['addFunds_Success'])) {
        header("Location: {$_SESSION['addFunds_Success']}");
        unset($_SESSION['addFunds_Success']);
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
    }
} else {
    if (!empty($_SESSION['addFunds_Fail'])) {
        header("Location: {$_SESSION['addFunds_Fail']}");
        unset($_SESSION['addFunds_Fail']);
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
}
error_log(json_encode($obj));
error_log("PAYPAL redirect_url GET:  ".json_encode($_GET));
error_log("PAYPAL redirect_url POST: ".json_encode($_POST));
?>