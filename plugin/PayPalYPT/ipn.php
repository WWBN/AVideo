<?php
// check recurrent payments
header('Content-Type: application/json');

// Preserve provider IDs before configuration.php's generic _id integer filter.
$paypalIPNIdentifiers = [
    'recurring_payment_id' => $_POST['recurring_payment_id'] ?? '',
    'txn_id' => $_POST['txn_id'] ?? ''
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
// Only a verified recurring delivery may restore these provider-controlled strings.
if (!empty($paypalIPNIdentifiers['recurring_payment_id'])) {
    $_POST = array_replace($_POST, $paypalIPNIdentifiers);
}
$obj= new stdClass();
$obj->error = true;
if (empty($_POST["recurring_payment_id"])) {
    _error_log("PayPalIPN: recurring_payment_id EMPTY ");
    $users_id = User::getId();

    $invoiceNumber = uniqid();

    $payment = $paypal->execute();
    //var_dump($amount);
    if (!empty($payment)) {
        $amount = PayPalYPT::getAmountFromPayment($payment);
        $plugin->addBalance($users_id, $amount->total, "Paypal payment", "PayPalIPN");
        $obj->error = false;
        _error_log("PayPalIPN: Executed ".json_encode($payment));
    //header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
    } else {
        _error_log("PayPalIPN: Fail");
        //header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
} else {
    _error_log("PayPalIPN: recurring_payment_id = {$_POST["recurring_payment_id"]} ");

    // Only a completed recurring payment moves money. The agreement's notify_url also receives
    // profile created/cancel/suspended/skipped/failed notifications, which carry an amount too
    // (this branch was unreachable until the _id integer filter stopped breaking recurring_payment_id).
    // A Pending payment gets another IPN with the same txn_id when it completes.
    $paypalTxnType = (string) ($_POST['txn_type'] ?? '');
    $paypalPaymentStatus = (string) ($_POST['payment_status'] ?? '');
    if ($paypalTxnType !== 'recurring_payment' || $paypalPaymentStatus !== 'Completed') {
        _error_log("PayPalIPN: acknowledged without changes, not a completed recurring payment (txn_type={$paypalTxnType} payment_status={$paypalPaymentStatus})");
        die(json_encode($obj));
    }

    // Deduplication: use txn_id when present (most specific), fall back to verify_sign.
    // verify_sign is a per-notification cryptographic signature — unique per authentic IPN —
    // so replaying the same captured POST body always carries the same verify_sign.
    // This mirrors the approach used in ipnV2.php (IPN branch).
    $dedup_key = (string) (!empty($_POST['txn_id']) ? $_POST['txn_id'] : ($_POST['verify_sign'] ?? ''));

    // check for the recurrement payment
    $subscription = AVideoPlugin::loadPluginIfEnabled("Subscription");
    if (!empty($subscription)) {
        $row = Subscription::getFromAgreement($_POST["recurring_payment_id"]);
        _error_log("PayPalIPN: user found from recurring_payment_id (users_id = {$row['users_id']}) ");
        $users_id = $row['users_id'];
        $payment_amount = empty($_POST['mc_gross']) ? $_POST['amount'] : $_POST['mc_gross'];
        $payment_currency = empty($_POST['mc_currency']) ? $_POST['currency_code'] : $_POST['mc_currency'];
        if ($walletObject->currency===$payment_currency) {
            $logContext = "dedup_key={$dedup_key} recurring_payment_id={$_POST['recurring_payment_id']} users_id={$users_id} amount={$payment_amount}";
            if (empty($users_id)) {
                // not an AVideo subscription: acknowledge it so PayPal does not resend it
                _error_log("PayPalIPN: ignored, no AVideo subscription for this agreement {$logContext}", AVideoLog::$WARNING);
            } else {
                $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
                $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
                try {
                    // IPNcheck() verified this server-to-server delivery; model saves need no browser origin.
                    $global['bypassSameDomainCheck'] = 1;
                    // The wallet ledger serializes this payment, including the gateway's legacy lookup.
                    $plugin->processPaymentOnce('paypal', [$dedup_key], $users_id,
                        function () use ($plugin, $users_id, $row, $dedup_key, $payment_amount, $logContext) {
                            $pp = new PayPalYPT_log(0);
                            $pp->setUsers_id($users_id);
                            // The legacy log column is VARCHAR(45); the marker and JSON keep the full ID.
                            $pp->setRecurring_payment_id(substr($dedup_key, 0, 45));
                            $pp->setValue($payment_amount);
                            $pp->setJson(['post' => $_POST]);
                            if (!$pp->save()) {
                                throw new RuntimeException("Cannot save the PayPal log {$logContext}");
                            }
                            $plugin->addBalance($users_id, $payment_amount, "Paypal recurrent", json_encode($_POST));
                            $renewed = $plugin->runPaymentStep(function () use ($users_id, $row, $logContext) {
                                $renew = Subscription::renew($users_id, $row['subscriptions_plans_id']);
                                if (!isset($renew->error) || $renew->error) {
                                    _error_log('PayPalIPN: Subscription::renew failed: ' . ($renew->msg ?? '') . " {$logContext}", AVideoLog::$ERROR);
                                    return false;
                                }
                                return true;
                            }, 'PayPal subscription renewal');
                            if (!$renewed) {
                                _error_log("PayPalIPN: payment credited to the wallet but the renewal was undone; renew it manually {$logContext}", AVideoLog::$ERROR);
                            }
                            return true;
                        }, ['legacyCheck' => function () use ($dedup_key, $plugin, $users_id) {
                            return PayPalYPT::isRecurringPaymentIdUsed($dedup_key)
                                || (!empty($_POST['verify_sign']) && WalletLog::hasProcessedPayment('paypal',
                                    [(string) $_POST['verify_sign']], $plugin->getOrCreateWallet($users_id)->getId(),
                                    ['legacyDescriptions' => ['Paypal recurrent%', 'PayPal Subscription from token%']]));
                        }, 'legacyDescriptions' => ['Paypal recurrent%', 'PayPal Subscription from token%']]);
                    $obj->error = false;
                } catch (\Throwable $error) {
                    // nothing was credited; a non-200 answer makes PayPal send the notification again
                    _error_log("PayPalIPN: payment processing failed, nothing was credited; PayPal will retry {$logContext} error="
                        . get_class($error) . ': ' . $error->getMessage(), AVideoLog::$ERROR);
                    http_response_code(500);
                } finally {
                    if ($hadOriginBypass) {
                        $global['bypassSameDomainCheck'] = $previousOriginBypass;
                    } else {
                        unset($global['bypassSameDomainCheck']);
                    }
                }
            }
        } else {
            _error_log("PayPalIPN: FAIL currency check $walletObject->currency===$payment_currency ");
        }
    }
}

_error_log("PayPalIPN: ".json_encode($obj));
_error_log("PayPalIPN: POST ".json_encode($_POST));
_error_log("PayPalIPN: GET ".json_encode($_GET));
_error_log("PayPalIPN END");
