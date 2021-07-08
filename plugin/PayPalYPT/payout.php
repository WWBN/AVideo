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

$response = PayPalYPT::getPayoutInfo($_REQUEST['payout_batch']);

if(!is_object($response) || empty($response->result)){
    forbiddenPage('Request error');
}

?>
Subject: <?php echo $response->result->batch_header->sender_batch_header->email_subject; ?><br>
Status: <?php echo $response->result->batch_header->batch_status; ?><br>
Time: <?php echo $response->result->batch_header->time_created; ?><br>
Amount: $<?php echo $response->result->batch_header->amount->value, $response->result->batch_header->amount->currency; ?><br> 