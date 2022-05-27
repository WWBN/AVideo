<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");


_error_log("Paypal:Webhook POST: " . json_encode($_POST));
_error_log("Paypal:Webhook GET: " . json_encode($_GET));
_error_log("Paypal:Webhook php://input" . file_get_contents("php://input"));

        
$output = PayPalYPT::validateWebhook();


//$output = PayPalYPT::createWebhook();

//$output = PayPalYPT::getOrCreateWebhook();


//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);


//$output = PayPalYPT::deleteAllWebhooks();

_error_log('PayPal webhook: '. PayPalYPT::getWebhookURL());
_error_log($output);