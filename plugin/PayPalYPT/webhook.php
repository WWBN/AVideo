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

$output = false;
try {
    $output = PayPalYPT::validateWebhook();
    if (!empty($output) && ($output->webhook_event->event_type ?? '') === 'PAYMENT.SALE.COMPLETED'
        && !empty($output->webhook_event->resource->billing_agreement_id)
        && !empty(AVideoPlugin::loadPluginIfEnabled('Subscription'))) {
        $token = $output->webhook_event->id;
        $resource = $output->webhook_event->resource;
        $row = Subscription::getFromAgreement($resource->billing_agreement_id);
        $users_id = $row['users_id'] ?? 0;
        $payment_amount = empty($resource->amount->total) ? $resource->transaction_fee->value : $resource->amount->total;
        $payment_currency = empty($resource->amount->currency) ? $resource->transaction_fee->currency : $resource->amount->currency;
        if (!empty($users_id) && $walletObject->currency === $payment_currency) {
            $description = "Paypal recurrent WH";
            if (!empty($row['subscriptions_plans_id'])) {
                $plan = new SubscriptionPlansTable($row['subscriptions_plans_id']);
                $description .= " " . $plan->getName();
            }
            $paymentId = $resource->id ?? $token;
            $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
            $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
            try {
                $global['bypassSameDomainCheck'] = 1;
                // The sale ID also identifies the transaction in a recurring IPN delivery.
                $plugin->processPaymentOnce('paypal', [$paymentId, $token], $users_id,
                    function () use ($plugin, $users_id, $payment_amount, $description, $output, $row, $resource, $token) {
                        $pp = new PayPalYPT_log(0);
                        $pp->setUsers_id($users_id);
                        $pp->setAgreement_id($resource->billing_agreement_id);
                        $pp->setToken($token);
                        $pp->setValue($payment_amount);
                        $pp->setJson($output);
                        if (!$pp->save()) {
                            throw new RuntimeException('Cannot save PayPal webhook log');
                        }
                        $plugin->addBalance($users_id, $payment_amount, $description, json_encode($output));
                        if (!$plugin->runPaymentStep(function () use ($users_id, $row) {
                            $renew = Subscription::renew($users_id, $row['subscriptions_plans_id']);
                            return isset($renew->error) && !$renew->error;
                        }, 'PayPal webhook subscription renewal')) {
                            _error_log("PayPal webhook: payment credited but renewal failed; renew manually users_id={$users_id} event={$token}", AVideoLog::$ERROR);
                        }
                        return true;
                    }, ['legacyCheck' => function () use ($token, $paymentId) {
                        return PayPalYPT::isTokenUsed($token) || PayPalYPT::isRecurringPaymentIdUsed($paymentId);
                    }, 'legacyDescriptions' => ['Paypal recurrent%', 'PayPal Subscription from token%']]);
            } finally {
                if ($hadOriginBypass) {
                    $global['bypassSameDomainCheck'] = $previousOriginBypass;
                } else {
                    unset($global['bypassSameDomainCheck']);
                }
            }
        } else {
            _error_log("Paypal:Webhook FAIL currency check $walletObject->currency===$payment_currency ");
        }
    }
} catch (\Throwable $error) {
    _error_log('PayPal webhook: payment processing failed; retry delivery: ' . $error->getMessage(), AVideoLog::$ERROR);
    http_response_code(500);
}

//$output = PayPalYPT::createWebhook();
//$output = PayPalYPT::getOrCreateWebhook();
//require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
//$output = \PayPal\Api\Webhook::getAll($apiContext);
//$output = PayPalYPT::deleteAllWebhooks();

_error_log('PayPal webhook: ' . PayPalYPT::getWebhookURL());
_error_log($output);
