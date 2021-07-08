<?php

// check recurrent payments
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

_error_log("Payout Info Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

if (empty($plugin)) {
    forbiddenPage('Wallet Disabled');
}

if (empty($paypal)) {
    forbiddenPage('PayPal Disabled');
}

if (empty($_REQUEST['payout_batch'])) {
    forbiddenPage('payout_batch_id required');
}

var_dump($_REQUEST['payout_batch']);
$response = PayPalYPT::getPayoutInfo($_REQUEST['payout_batch']);
var_dump($response);
?>