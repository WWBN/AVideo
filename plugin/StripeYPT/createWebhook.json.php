<?php

// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

_error_log("StripeINTENT Webhook Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$stripe = AVideoPlugin::loadPluginIfEnabled("StripeYPT");
$stripeObject = AVideoPlugin::getObjectData("StripeYPT");

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin()) {
    $obj->msg = "Admin only";
    die(json_encode($obj));
}

if (empty($stripe)) {
    $obj->msg = "Stripe Plugin Disabled";
    die(json_encode($obj));
}
if (empty($plugin)) {
    $obj->msg = "Wallet Plugin Disabled";
    die(json_encode($obj));
}


StripeYPT::_start();
$obj->webhook = StripeYPT::getWebhook();
$obj->error = empty($obj->webhook);

die(json_encode($obj));
?>
