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

$obj = new stdClass();
$obj->error = true;

if (empty($_POST['value'])) {
    $obj->msg = "Invalid Value";
    die(json_encode($obj));
}

$invoiceNumber = uniqid();
//setUpPayment($total = '1.00', $currency = "USD", $description = "");
$payment = $plugin->setUpPayment($_POST['value'], $objS->currency, $config->getWebSiteTitle() . " Payment");

if (!empty($payment) && StripeYPT::isPaymentOk($payment, $_POST['value'], $objS->currency)) {
    $obj->error = false;
    $obj->charge = $payment;
    $obj->amount = StripeYPT::getAmountFromPayment($payment);

    $pluginS->addBalance(User::getId(), $obj->amount, "Stripe payment", json_encode($payment));
}
$obj->walletBalance = $pluginS->getBalanceFormated(User::getId());
die(json_encode($obj));
