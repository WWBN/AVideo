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
$paypal = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");
$users_id = User::getId();
        
$invoiceNumber = uniqid();

$payment = $paypal->execute();
//var_dump($amount);
$obj= new stdClass();
$obj->error = true;
if (!empty($payment)) {
    $amount = PayPalYPT::getAmountFromPayment($payment);
    $plugin->addBalance($users_id, $amount->total, "Paypal payment", json_encode("PayPalIPN: ".$payment));
    $obj->error = false;
    header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
}else{
    header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
}


error_log("PayPalIPN: ".json_encode($obj));
error_log("PayPalIPN: POST ".json_encode($_POST));
error_log("PayPalIPN: GET ".json_encode($_GET));
error_log("PayPalIPN END");

?>