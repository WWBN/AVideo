<?php

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

// HTTP contract with PayPal: 200 = handled or not ours (do not resend), 400 = not authentic,
// 500 = could not be handled now (PayPal resends it). Bodies, headers and payer data are never logged.
$output = false;
try {
    if (empty($paypal) || empty($plugin)) {
        throw new RuntimeException('PayPalYPT and YPTWallet must be enabled');
    }
    $output = PayPalYPT::validateWebhook();
    if (empty($output)) {
        http_response_code(400);
    } else {
        $event = $output->webhook_event;
        $eventType = (string) ($event->event_type ?? '');
        $resource = $event->resource ?? new stdClass();
        $token = (string) $event->id;
        _error_log('Paypal:Webhook verified event=' . preg_replace('/[^A-Z0-9-]/i', '', $token) . ' type=' . preg_replace('/[^A-Z0-9._-]/i', '', $eventType)
            . ' resource=' . preg_replace('/[^A-Z0-9_-]/i', '', (string) ($resource->id ?? '')));
        if ($eventType === 'PAYMENT.SALE.COMPLETED' && !empty($resource->billing_agreement_id)) {
            if (!empty(AVideoPlugin::loadPluginIfEnabled('Subscription'))) {
                $agreementId = (string) $resource->billing_agreement_id;
                $owner = PayPalPaymentProcessor::findAgreementOwner($agreementId);
                $payment_amount = empty($resource->amount->total) ? $resource->transaction_fee->value : $resource->amount->total;
                $payment_currency = empty($resource->amount->currency) ? $resource->transaction_fee->currency : $resource->amount->currency;
                if (empty($owner)) {
                    _error_log("Paypal:Webhook ignored, no AVideo subscription for agreement={$agreementId}", AVideoLog::$WARNING);
                } elseif ($walletObject->currency !== $payment_currency) {
                    _error_log("Paypal:Webhook FAIL currency check $walletObject->currency===$payment_currency ");
                } else {
                    $description = "Paypal recurrent WH";
                    if (!empty($owner['plans_id'])) {
                        $plan = new SubscriptionPlansTable($owner['plans_id']);
                        $description .= " " . $plan->getName();
                    }
                    $paymentId = (string) ($resource->id ?? $token);
                    $saleRecord = ['event_id' => $token, 'event_type' => $eventType, 'sale_id' => $paymentId, 'agreement_id' => $agreementId];
                    // The sale ID also identifies the transaction in a recurring IPN delivery and the checkout return.
                    PayPalPaymentProcessor::processSubscriptionSale($plugin, $owner['users_id'], $owner['plans_id'], $agreementId,
                        [$paymentId, $token], $payment_amount, $description, $saleRecord, ['token' => $token],
                        function () use ($token, $paymentId) {
                            return PayPalYPT::isTokenUsed($token) || PayPalYPT::isRecurringPaymentIdUsed($paymentId);
                        });
                }
            }
        } elseif (in_array($eventType, ['PAYMENT.SALE.COMPLETED', 'PAYMENT.SALE.PENDING', 'PAYMENT.SALE.DENIED'], true)
            && !empty($resource->parent_payment)) {
            // One-time checkout: the event only triggers a fresh read of the payment from PayPal.
            $checkout = PayPalYPT_checkout::isInstalled() ? PayPalYPT_checkout::getByPaymentId((string) $resource->parent_payment) : false;
            if (empty($checkout)) {
                _error_log('Paypal:Webhook acknowledged, not an AVideo checkout payment=' . preg_replace('/[^A-Z0-9_-]/i', '', (string) $resource->parent_payment));
            } else {
                PayPalYPT::reconcileCheckout($checkout, 'webhook');
            }
        } elseif (in_array($eventType, ['PAYMENT.SALE.REFUNDED', 'PAYMENT.SALE.REVERSED'], true)) {
            // Policy: refunds and reversals never add credit or access, and AVideo does not take back a credit
            // that may already be spent. The checkout is marked and the administrator reviews the wallet.
            $saleId = (string) ($resource->sale_id ?? $resource->id ?? '');
            $checkout = PayPalYPT_checkout::isInstalled() ? PayPalYPT_checkout::getByPaymentId((string) ($resource->parent_payment ?? '')) : false;
            if (!empty($checkout) && $checkout['status'] === 'completed') {
                PayPalYPT_checkout::update($checkout['id'], ['status' => 'refunded', 'status_detail' => $eventType === 'PAYMENT.SALE.REVERSED' ? 'reversed_after_credit' : 'refunded_after_credit']);
            }
            _error_log('Paypal:Webhook ' . $eventType . ' sale=' . preg_replace('/[^A-Z0-9_-]/i', '', $saleId)
                . ' agreement=' . preg_replace('/[^A-Z0-9_-]/i', '', (string) ($resource->billing_agreement_id ?? ''))
                . ' users_id=' . intval($checkout['users_id'] ?? 0) . '; review the wallet credit and access manually', AVideoLog::$WARNING);
        } else {
            _error_log('Paypal:Webhook acknowledged without changes type=' . preg_replace('/[^A-Z0-9._-]/i', '', $eventType));
        }
        PayPalYPT::recordDelivery('webhook', $eventType);
    }
} catch (\Throwable $error) {
    _error_log('PayPal webhook: payment processing failed; retry delivery: '
        . (class_exists('PayPalYPT') ? PayPalYPT::describeApiError($error) : get_class($error)), AVideoLog::$ERROR);
    http_response_code(500);
}
