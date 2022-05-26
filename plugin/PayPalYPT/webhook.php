<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

_error_log("PayPal::webhook start ".json_encode($_GET).' '.json_encode($_POST));
        
$output = PayPalYPT::validateWebhook();


//$output = PayPalYPT::createWebhook();

//$output = PayPalYPT::getOrCreateWebhook();


//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);


//$output = PayPalYPT::deleteAllWebhooks();

_error_log('PayPal webhook: '. PayPalYPT::getWebhookURL());
_error_log($output);