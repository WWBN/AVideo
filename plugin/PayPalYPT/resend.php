<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

//$output = PayPalYPT::validateWebhook();


//$output = PayPalYPT::createWebhook();

$output = PayPalYPT::resendWebhook('WH-3G7997568U823792U-59G303851C6702143');


//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);


//$output = PayPalYPT::deleteAllWebhooks();

var_dump($output);