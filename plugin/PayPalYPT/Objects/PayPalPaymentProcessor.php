<?php

/**
 * Applies a PayPal payment that the caller has already confirmed with PayPal (API read, verified webhook
 * or verified IPN). Every path (browser return, webhook, IPN, status check) ends here, so one PayPal
 * sale gives one wallet credit and one benefit: the wallet ledger records the sale ID inside the same
 * transaction (YPTWallet::processPaymentOnce()). No PayPal SDK calls are made here.
 */
class PayPalPaymentProcessor
{
    const PROVIDER = 'paypal';
    // Pre-ledger PayPal credits are recognized by these wallet descriptions.
    const LEGACY_DESCRIPTIONS = ['Paypal recurrent%', 'PayPal Subscription from token%'];

    /** Provider callbacks have no browser origin; model saves must still be allowed for this delivery only. */
    public static function withoutBrowserOrigin(callable $work)
    {
        global $global;
        $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
        $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
        try {
            $global['bypassSameDomainCheck'] = 1;
            return $work();
        } finally {
            if ($hadOriginBypass) {
                $global['bypassSameDomainCheck'] = $previousOriginBypass;
            } else {
                unset($global['bypassSameDomainCheck']);
            }
        }
    }

    public static function cents($value)
    {
        return intval(round(floatval($value) * 100));
    }

    /**
     * A completed sale of a one-time checkout (wallet top-up, PPV rental, PPV Live event).
     * $sale = ['id' => ..., 'state' => 'completed', 'total' => '10.00', 'currency' => 'USD'] read from PayPal.
     * Returns the fulfillment label saved in the checkout.
     */
    public static function fulfillCheckoutSale($wallet, array $checkout, array $sale, $source)
    {
        $context = self::logContext($checkout, $sale, $source);
        if (($sale['state'] ?? '') !== 'completed' || empty($sale['id'])) {
            throw new RuntimeException("Only a completed PayPal sale can be fulfilled {$context}");
        }
        if (self::cents($sale['total'] ?? 0) !== self::cents($checkout['amount'])
            || strcasecmp((string) ($sale['currency'] ?? ''), (string) $checkout['currency']) !== 0) {
            // A different amount or currency is never credited automatically.
            throw new PayPalPaymentMismatchException("PayPal sale amount or currency does not match the checkout {$context}");
        }
        $walletCurrency = (string) ($wallet->getDataObject()->currency ?? '');
        if (strcasecmp($walletCurrency, (string) $checkout['currency']) !== 0) {
            throw new PayPalPaymentMismatchException("PayPal checkout currency is not the wallet currency {$context}");
        }
        $selection = $checkout['selection'];
        $fulfillment = 'credited';
        self::withoutBrowserOrigin(function () use ($wallet, $checkout, $sale, $selection, $context, &$fulfillment) {
            $wallet->processPaymentOnce(self::PROVIDER, [(string) $sale['id']], intval($checkout['users_id']),
                function () use ($wallet, $checkout, $sale, $selection, $context, &$fulfillment) {
                    $description = 'PayPal payment: ' . mb_substr((string) ($selection['description'] ?? ''), 0, 150, 'UTF-8');
                    $wallet->addBalance(intval($checkout['users_id']), floatval($checkout['amount']), $description, json_encode([
                        'paypal_sale_id' => (string) $sale['id'], 'paypal_payment_id' => (string) $checkout['payment_id'],
                        'invoice' => (string) $checkout['invoice_number'], 'purpose' => (string) $checkout['purpose'],
                    ]));
                    if ($checkout['purpose'] === 'ppv') {
                        // The savepoint undoes only the rental (debit, split, rental row) when it fails; the paid
                        // credit stays in the wallet so the buyer can finish the same rental without paying again.
                        $fulfillment = $wallet->runPaymentStep(function () use ($checkout, $selection) {
                            $ppv = AVideoPlugin::loadPluginIfEnabled('PayPerView');
                            return !empty($ppv) && method_exists($ppv, 'completeWalletCheckout')
                                && $ppv->completeWalletCheckout(intval($checkout['users_id']), $selection['ppv_plans_id'] ?? 0,
                                    $selection['videos_id'] ?? 0, $selection['ppv_value'] ?? '', $selection['rental_hours'] ?? 0) === true;
                        }, 'PayPal PPV rental') ? 'rented' : 'benefit_failed';
                    } elseif ($checkout['purpose'] === 'ppvlive') {
                        $fulfillment = $wallet->runPaymentStep(function () use ($checkout, $selection) {
                            $live = AVideoPlugin::loadPluginIfEnabled('PayPerViewLive');
                            return !empty($live) && method_exists($live, 'completeWalletCheckout')
                                && $live->completeWalletCheckout(intval($checkout['users_id']), $selection['ppvlive_schedule_id'] ?? 0,
                                    $selection['ppvlive_value'] ?? '', $selection['live_ends'] ?? '') === true;
                        }, 'PayPal PPV Live access') ? 'live_ready' : 'benefit_failed';
                    }
                    if ($fulfillment === 'benefit_failed') {
                        _error_log("PayPal: payment credited, but the purchase was not completed; the balance stays in the wallet {$context}", AVideoLog::$ERROR);
                    }
                    // Same transaction: the checkout is completed only if the credit commits.
                    PayPalYPT_checkout::update($checkout['id'], ['status' => 'completed', 'sale_id' => (string) $sale['id'],
                        'status_detail' => null, 'fulfillment' => $fulfillment]);
                    return true;
                });
        });
        // A duplicate delivery returns before the callback runs: report what the first one saved.
        $saved = PayPalYPT_checkout::getById($checkout['id']);
        return empty($saved['fulfillment']) ? $fulfillment : $saved['fulfillment'];
    }

    /**
     * One completed payment of a billing agreement (first payment or renewal), confirmed by the caller.
     * The wallet ledger keys it by the sale ID, which is the IPN txn_id and the webhook resource.id.
     */
    public static function processSubscriptionSale($wallet, $users_id, $plans_id, $agreementId, array $paymentIds, $amount, $description, $json, array $logFields = [], ?callable $legacyCheck = null)
    {
        $logContext = 'agreement=' . $agreementId . ' payment_ids=' . implode(',', $paymentIds)
            . ' users_id=' . intval($users_id) . ' plans_id=' . intval($plans_id) . ' amount=' . floatval($amount);
        $options = ['legacyDescriptions' => self::LEGACY_DESCRIPTIONS];
        if ($legacyCheck !== null) {
            $options['legacyCheck'] = $legacyCheck;
        }
        return self::withoutBrowserOrigin(function () use ($wallet, $users_id, $plans_id, $agreementId, $paymentIds, $amount, $description, $json, $logFields, $logContext, $options) {
            return $wallet->processPaymentOnce(self::PROVIDER, $paymentIds, $users_id,
                function () use ($wallet, $users_id, $plans_id, $agreementId, $paymentIds, $amount, $description, $json, $logFields, $logContext) {
                    $pp = new PayPalYPT_log(0);
                    $pp->setUsers_id($users_id);
                    $pp->setAgreement_id($agreementId);
                    if (!empty($logFields['token'])) {
                        $pp->setToken($logFields['token']);
                    }
                    if (!empty($logFields['recurring_payment_id'])) {
                        // The legacy log column is VARCHAR(45); the ledger marker keeps the full ID.
                        $pp->setRecurring_payment_id(substr($logFields['recurring_payment_id'], 0, 45));
                    }
                    $pp->setValue($amount);
                    $pp->setJson($json);
                    if (!$pp->save()) {
                        throw new RuntimeException("Cannot save the PayPal log {$logContext}");
                    }
                    $wallet->addBalance($users_id, $amount, $description, is_string($json) ? $json : json_encode($json));
                    $renewed = $wallet->runPaymentStep(function () use ($users_id, $plans_id, $logContext) {
                        $renew = Subscription::renew($users_id, $plans_id);
                        if (!isset($renew->error) || $renew->error) {
                            _error_log('PayPal: Subscription::renew failed: ' . ($renew->msg ?? '') . " {$logContext}", AVideoLog::$ERROR);
                            return false;
                        }
                        return true;
                    }, 'PayPal subscription renewal');
                    if (!$renewed) {
                        _error_log("PayPal: payment credited to the wallet but the renewal was undone; renew it manually {$logContext}", AVideoLog::$ERROR);
                    }
                    $checkout = PayPalYPT_checkout::isInstalled() ? PayPalYPT_checkout::getByAgreementId($agreementId) : false;
                    if (!empty($checkout) && $checkout['status'] === 'active') {
                        PayPalYPT_checkout::update($checkout['id'], ['status' => 'completed', 'sale_id' => reset($paymentIds) ?: null,
                            'fulfillment' => $renewed ? 'subscribed' : 'benefit_failed']);
                    }
                    return true;
                }, $options);
        });
    }

    /** Finds the AVideo buyer of a billing agreement: the Subscription row, else the checkout that created it. */
    public static function findAgreementOwner($agreementId)
    {
        $row = Subscription::getFromAgreement($agreementId);
        if (!empty($row['users_id'])) {
            return ['users_id' => intval($row['users_id']), 'plans_id' => intval($row['subscriptions_plans_id'])];
        }
        // A buyer who re-subscribes keeps the old agreement in the Subscription row; payments of the new
        // agreement are still identified by the checkout that started it.
        $checkout = PayPalYPT_checkout::isInstalled() ? PayPalYPT_checkout::getByAgreementId($agreementId) : false;
        if (!empty($checkout) && !empty($checkout['selection']['plans_id'])) {
            return ['users_id' => intval($checkout['users_id']), 'plans_id' => intval($checkout['selection']['plans_id'])];
        }
        return false;
    }

    private static function logContext($checkout, $sale, $source)
    {
        return 'source=' . preg_replace('/[^a-z_-]/i', '', (string) $source) . ' invoice=' . ($checkout['invoice_number'] ?? '')
            . ' payment=' . ($checkout['payment_id'] ?? '') . ' sale=' . ($sale['id'] ?? '') . ' users_id=' . intval($checkout['users_id'] ?? 0)
            . ' purpose=' . ($checkout['purpose'] ?? '');
    }
}

class PayPalPaymentMismatchException extends RuntimeException
{
}
