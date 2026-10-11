<?php

// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
_error_log("RazorPayIPN Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$razorpay = AVideoPlugin::loadPluginIfEnabled("RazorPayYPT");
$razorpayObject = AVideoPlugin::getObjectData("RazorPayYPT");

$obj = new stdClass();
$obj->error = true;

$api = $razorpay->start();

$json = file_get_contents('php://input');
$webhookBody = _json_decode($json);

//_error_log("RazorPayIPN header - " . json_encode($_SERVER));

if(empty($webhookBody)){
    $obj->msg = "No body";
    _error_log("RazorPayIPN Body - {$obj->msg}");
    die(json_encode($obj));
} else if ($webhookBody->event !== "subscription.charged") {
    _error_log("RazorPayIPN Not a subscription, webhook will be ignored");
} else {

    if (!empty($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'])) {
        $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'];
    }

    if (empty($webhookSignature)) {
        _error_log("RazorPayIPN ERROR, webhookSignature is empty");
    } else {
        $hadOriginBypass = array_key_exists('bypassSameDomainCheck', $global);
        $previousOriginBypass = $global['bypassSameDomainCheck'] ?? null;
        try {
            $api->utility->verifyWebhookSignature($json, $webhookSignature, $razorpayObject->webhookSecret);
            // A verified provider callback may update subscription models without a browser origin.
            $global['bypassSameDomainCheck'] = 1;
            $subscription = $webhookBody->payload->subscription->entity ?? null;
            $payment = $webhookBody->payload->payment->entity ?? null;
            $users_id = intval($subscription->notes->users_id ?? 0);
            $plans_id = intval($subscription->notes->plans_id ?? 0);
            // Non-secret identifiers to find the payment in the logs and in the RazorPay dashboard.
            $logContext = 'payment=' . ($payment->id ?? '') . ' subscription=' . ($subscription->id ?? '')
                . " users_id={$users_id} plans_id={$plans_id} amount=" . (($payment->amount ?? 0) / 100);
            if (empty($payment->id)) {
                throw new RuntimeException("Missing RazorPay payment id {$logContext}");
            }
            if (empty($users_id) || empty($plans_id)) {
                // Not a subscription created by AVideo: acknowledge it, as before, so RazorPay does not retry it.
                $obj->msg = 'Ignored: subscription without AVideo users_id/plans_id';
                _error_log("RazorPayIPN: ignored, subscription notes have no users_id/plans_id {$logContext}", AVideoLog::$WARNING);
            } else {
                _error_log("RazorPayIPN: processing {$logContext}");
                $plugin->processPaymentOnce('razorpay', [$payment->id], $users_id,
                    function () use ($plugin, $users_id, $plans_id, $payment, $webhookBody, $logContext) {
                        $plugin->addBalance($users_id, $payment->amount / 100,
                            'RazorPay recurrent payment: ', json_encode($webhookBody));
                        $renewed = $plugin->runPaymentStep(function () use ($users_id, $plans_id, $logContext) {
                            $renew = Subscription::renew($users_id, $plans_id);
                            if (!isset($renew->error) || $renew->error) {
                                _error_log('RazorPayIPN: Subscription::renew failed: ' . ($renew->msg ?? '') . " {$logContext}", AVideoLog::$ERROR);
                                return false;
                            }
                            return true;
                        }, 'RazorPay subscription renewal');
                        if (!$renewed) {
                            _error_log("RazorPayIPN: payment credited to the wallet but the renewal was undone; renew it manually {$logContext}", AVideoLog::$ERROR);
                        }
                        return true;
                    });
                $obj->error = false;
            }
        } catch (\Throwable $error) {
            $obj->msg = 'Payment could not be processed';
            $isSignatureError = $error instanceof \Razorpay\Api\Errors\SignatureVerificationError;
            _error_log('RazorPayIPN: ' . ($isSignatureError ? 'invalid signature, check the webhook secret' : 'payment processing failed, nothing was credited; RazorPay will retry')
                . ' ' . ($logContext ?? '') . ' error=' . get_class($error) . ': ' . $error->getMessage(), AVideoLog::$ERROR);
            http_response_code($isSignatureError ? 400 : 500);
        } finally {
            if ($hadOriginBypass) {
                $global['bypassSameDomainCheck'] = $previousOriginBypass;
            } else {
                unset($global['bypassSameDomainCheck']);
            }
        }

    }
    _error_log("RazorPayIPN: " . json_encode($obj));
}
_error_log("RazorPayIPN END");
?>