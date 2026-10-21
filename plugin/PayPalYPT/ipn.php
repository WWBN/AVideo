<?php
// check recurrent payments
header('Content-Type: application/json');

// Preserve provider IDs before configuration.php's generic _id integer filter.
$paypalIPNIdentifiers = [
    'recurring_payment_id' => $_POST['recurring_payment_id'] ?? '',
    'txn_id' => $_POST['txn_id'] ?? '',
    'initial_payment_txn_id' => $_POST['initial_payment_txn_id'] ?? '',
];
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

_error_log("PayPalIPN Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");

$ipn = PayPalYPT::IPNcheck();
if (!$ipn) {
    die("IPN Fail");
}
// Only a verified delivery may restore these provider-controlled strings.
$_POST = array_replace($_POST, array_filter($paypalIPNIdentifiers, 'is_string'));
$obj= new stdClass();
$obj->error = true;
$paypalTxnType = (string) ($_POST['txn_type'] ?? '');
if (empty($_POST["recurring_payment_id"])) {
    // One-time checkout: the IPN only triggers a fresh read of the payment from PayPal with this site's
    // credentials, so the credited amount never comes from the notification itself.
    $invoice = (string) ($_POST['invoice'] ?? '');
    $checkout = PayPalYPT_checkout::isInstalled() ? PayPalYPT_checkout::getByInvoice($invoice) : false;
    if (empty($checkout)) {
        _error_log("PayPalIPN: acknowledged, not an AVideo checkout txn_type={$paypalTxnType}");
    } else {
        try {
            $checkout = PayPalYPT::reconcileCheckout($checkout, 'ipn');
            $obj->error = false;
            PayPalYPT::recordDelivery('ipn', $paypalTxnType);
            _error_log("PayPalIPN: checkout {$checkout['invoice_number']} status={$checkout['status']}");
        } catch (\Throwable $error) {
            _error_log("PayPalIPN: checkout {$invoice} could not be updated; PayPal will retry " . PayPalYPT::describeApiError($error) . ' ' . $error->getMessage(), AVideoLog::$ERROR);
            http_response_code(500);
        }
    }
} else {
    _error_log("PayPalIPN: recurring_payment_id = {$_POST["recurring_payment_id"]} ");

    // Only a completed payment moves money. The agreement's notify_url also receives profile
    // created/cancel/suspended/skipped/failed notifications, which carry an amount too.
    // A Pending payment gets another IPN with the same txn_id when it completes.
    $paypalPaymentStatus = (string) ($_POST['payment_status'] ?? '');
    $paypalTxnId = (string) ($_POST['txn_id'] ?? '');
    $paypalAmount = empty($_POST['mc_gross']) ? ($_POST['amount'] ?? '') : $_POST['mc_gross'];
    $paypalCurrency = empty($_POST['mc_currency']) ? ($_POST['currency_code'] ?? '') : $_POST['mc_currency'];
    if ($paypalTxnType === 'recurring_payment_profile_created' && ($_POST['initial_payment_status'] ?? '') === 'Completed'
        && !empty($_POST['initial_payment_txn_id'])) {
        // The agreement's first payment (setup fee): same sale ID as the webhook and the checkout return.
        $paypalTxnType = 'recurring_payment';
        $paypalPaymentStatus = 'Completed';
        $paypalTxnId = (string) $_POST['initial_payment_txn_id'];
        $paypalAmount = $_POST['initial_payment_amount'] ?? '';
        $paypalCurrency = $_POST['currency_code'] ?? '';
    }
    if ($paypalTxnType !== 'recurring_payment' || $paypalPaymentStatus !== 'Completed') {
        _error_log("PayPalIPN: acknowledged without changes, not a completed recurring payment (txn_type={$paypalTxnType} payment_status={$paypalPaymentStatus})");
        die(json_encode($obj));
    }

    // Deduplication: use txn_id when present (most specific), fall back to verify_sign.
    // verify_sign is a per-notification cryptographic signature — unique per authentic IPN —
    // so replaying the same captured POST body always carries the same verify_sign.
    // This mirrors the approach used in ipnV2.php (IPN branch).
    $dedup_key = (string) (!empty($paypalTxnId) ? $paypalTxnId : ($_POST['verify_sign'] ?? ''));

    // check for the recurrement payment
    $subscription = AVideoPlugin::loadPluginIfEnabled("Subscription");
    if (!empty($subscription)) {
        $owner = PayPalPaymentProcessor::findAgreementOwner($_POST["recurring_payment_id"]);
        $users_id = $owner['users_id'] ?? 0;
        _error_log("PayPalIPN: user found from recurring_payment_id (users_id = {$users_id}) ");
        $payment_amount = $paypalAmount;
        $payment_currency = $paypalCurrency;
        if ($walletObject->currency===$payment_currency) {
            $logContext = "dedup_key={$dedup_key} recurring_payment_id={$_POST['recurring_payment_id']} users_id={$users_id} amount={$payment_amount}";
            if (empty($users_id)) {
                // not an AVideo subscription: acknowledge it so PayPal does not resend it
                _error_log("PayPalIPN: ignored, no AVideo subscription for this agreement {$logContext}", AVideoLog::$WARNING);
            } else {
                try {
                    // IPNcheck() verified this server-to-server delivery; the wallet ledger serializes this payment.
                    $ipnRecord = ['txn_id' => $dedup_key, 'recurring_payment_id' => (string) $_POST['recurring_payment_id'],
                        'txn_type' => (string) ($_POST['txn_type'] ?? ''), 'payment_status' => $paypalPaymentStatus,
                        'mc_gross' => (string) $payment_amount, 'mc_currency' => (string) $payment_currency,
                        'ipn_track_id' => (string) ($_POST['ipn_track_id'] ?? ''), 'verify_sign' => (string) ($_POST['verify_sign'] ?? '')];
                    PayPalPaymentProcessor::processSubscriptionSale($plugin, $users_id, $owner['plans_id'], (string) $_POST['recurring_payment_id'],
                        [$dedup_key], $payment_amount, "Paypal recurrent", ['post' => $ipnRecord], ['recurring_payment_id' => $dedup_key],
                        function () use ($dedup_key, $plugin, $users_id) {
                            return PayPalYPT::isRecurringPaymentIdUsed($dedup_key)
                                || (!empty($_POST['verify_sign']) && WalletLog::hasProcessedPayment('paypal',
                                    [(string) $_POST['verify_sign']], $plugin->getOrCreateWallet($users_id)->getId(),
                                    ['legacyDescriptions' => PayPalPaymentProcessor::LEGACY_DESCRIPTIONS]));
                        });
                    $obj->error = false;
                    PayPalYPT::recordDelivery('ipn', $paypalTxnType);
                } catch (\Throwable $error) {
                    // nothing was credited; a non-200 answer makes PayPal send the notification again
                    _error_log("PayPalIPN: payment processing failed, nothing was credited; PayPal will retry {$logContext} error="
                        . get_class($error) . ': ' . $error->getMessage(), AVideoLog::$ERROR);
                    http_response_code(500);
                }
            }
        } else {
            _error_log("PayPalIPN: FAIL currency check $walletObject->currency===$payment_currency ");
        }
    }
}

_error_log("PayPalIPN: ".json_encode($obj));
_error_log("PayPalIPN END");
