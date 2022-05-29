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

_error_log("Paypal:Webhook validation " . json_encode($output));

if(!empty($output) && !empty($output->billing_agreement_id)){
    $row = Subscription::getFromAgreement($output->billing_agreement_id);
    _error_log("Paypal:Webhook user found from billing_agreement_id (users_id = {$row['users_id']}) ");
    $users_id = $row['users_id'];
    $payment_amount = empty($output->webhook_event->amount->total)?$output->transaction_fee->value:$output->webhook_event->amount->total;
    $payment_currency = empty($output->webhook_event->amount->currency)?$output->transaction_fee->currency:$output->webhook_event->amount->currency;
    if ($walletObject->currency===$payment_currency) {
        $plugin->addBalance($users_id, $payment_amount, "Paypal recurrent webhook: ", json_encode($output));
        Subscription::renew($users_id, $row['subscriptions_plans_id']);
        $obj->error = false;
    } else {
        _error_log("Paypal:Webhook FAIL currency check $walletObject->currency===$payment_currency ");
    }
}

//$output = PayPalYPT::createWebhook();

//$output = PayPalYPT::getOrCreateWebhook();


//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);


//$output = PayPalYPT::deleteAllWebhooks();

_error_log('PayPal webhook: '. PayPalYPT::getWebhookURL());
_error_log($output);