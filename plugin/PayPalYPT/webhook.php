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

try {
    $output = PayPalYPT::validateWebhook();
} catch (Exception $exc) {
    _error_log("Paypal:Webhook validation ERROR " . json_encode($exc->getTraceAsString()));
    exit;
}



_error_log("Paypal:Webhook validation " . json_encode($output));

if (!empty($output) && !empty($output->webhook_event->resource->billing_agreement_id)) {

    $token = $output->webhook_event->id;
    if (!PayPalYPT::isTokenUsed($token)) {
        $resource = $output->webhook_event->resource;

        $row = Subscription::getFromAgreement($resource->billing_agreement_id);
        _error_log("Paypal:Webhook user found from billing_agreement_id (users_id = {$row['users_id']}) (subscriptions_plans_id = {$row['subscriptions_plans_id']})");
        $users_id = $row['users_id'];
        $payment_amount = empty($resource->amount->total) ? $resource->transaction_fee->value : $resource->amount->total;
        $payment_currency = empty($resource->amount->currency) ? $resource->transaction_fee->currency : $resource->amount->currency;
        if ($walletObject->currency === $payment_currency) {
            $description = "Paypal recurrent WH";
            if (!empty($row['subscriptions_plans_id'])) {
                $plan = new SubscriptionPlansTable($row['subscriptions_plans_id']);
                $description .= " " . $plan->getName();
            }
            $plugin->addBalance($users_id, $payment_amount, $description, json_encode($output));
            Subscription::renew($users_id, $row['subscriptions_plans_id']);
            //$obj->error = false;

            $pp = new PayPalYPT_log(0);
            $pp->setUsers_id($users_id);
            $pp->setAgreement_id($resource->billing_agreement_id);
            $pp->setToken($token);
            $pp->setValue($payment_amount);
            $pp->setJson($output);
            $pp->save();
        } else {
            _error_log("Paypal:Webhook FAIL currency check $walletObject->currency===$payment_currency ");
        }
    } else {
        _error_log("Paypal:Webhook event already processed " . json_encode($output));
    }
}

//$output = PayPalYPT::createWebhook();
//$output = PayPalYPT::getOrCreateWebhook();
//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);
//$output = PayPalYPT::deleteAllWebhooks();

_error_log('PayPal webhook: ' . PayPalYPT::getWebhookURL());
_error_log($output);