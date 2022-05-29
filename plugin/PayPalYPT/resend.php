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

$output = PayPalYPT::resendWebhook('WH-8701806807823500S-2PU48335EE163281U');


if(!empty($output) && !empty($output->billing_agreement_id)){
    $row = Subscription::getFromAgreement($output->billing_agreement_id);
    _error_log("Paypal:Webhook resend user found from billing_agreement_id (users_id = {$row['users_id']}) ");
    $users_id = $row['users_id'];
    $payment_amount = empty($output->webhook_event->amount->total)?$output->transaction_fee->value:$output->webhook_event->amount->total;
    $payment_currency = empty($output->webhook_event->amount->currency)?$output->transaction_fee->currency:$output->webhook_event->amount->currency;
    if ($walletObject->currency===$payment_currency) {
        $plugin->addBalance($users_id, $payment_amount, "Paypal recurrent webhook: ", json_encode($output));
        Subscription::renew($users_id, $row['subscriptions_plans_id']);
        $obj->error = false;
    } else {
        _error_log("Paypal:Webhook resend FAIL currency check $walletObject->currency===$payment_currency ");
    }
}

//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);


//$output = PayPalYPT::deleteAllWebhooks();

var_dump($output);