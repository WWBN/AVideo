<?php

// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
//_error_log("StripeIPN Start");
$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = AVideoPlugin::getObjectData("YPTWallet");
$stripe = AVideoPlugin::loadPluginIfEnabled("StripeYPT");
$stripeObject = AVideoPlugin::getObjectData("StripeYPT");
if (empty($stripe)) {
    die("Stripe Plugin Disabled");
}
$stripe->start();

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = trim($stripeObject->SigningSecret);

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

$payloadObj = json_decode($payload);

//$payloadObj = object_to_array($payloadObj);

//_error_log("StripeIPN: WEBHOOK: ".json_encode($webhook));
//_error_log("StripeIPN: payload ".json_encode($payloadObj));
//_error_log("StripeIPN: sig_header ".json_encode($sig_header));

$whitelist = array('invoice.payment_succeeded', 'charge.succeeded');
if(!in_array($payloadObj->type, $whitelist)){
    //_error_log("StripeIPN: type ignored " . $payloadObj->type );
    return '';
}
_error_log("StripeIPN Start");
//_error_log("StripeIPN: ({$sig_header}) ({$endpoint_secret}} payload type: " . $payloadObj->type );

try {
    $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $endpoint_secret
    );
    //_error_log("Stripe IPN Valid payload and signature ". json_encode($payloadObj));
    if (StripeYPT::isSubscriptionPayment($payloadObj)) {
        _error_log("StripeIPN: ** Subscription **" );
        // subscription
        $stripe->processSubscriptionIPN($payloadObj);
    } else if (StripeYPT::isSinglePayment($payloadObj)) {
        _error_log("StripeIPN: ** SinglePayment **" );
        $stripe->processSinglePaymentIPN($payloadObj);
    }else{
        //_error_log("StripeIPN: something went wrong: {$payload}" , AVideoLog::$ERROR );
    }

    
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    _error_log("Stripe IPN Invalid payload ");
    http_response_code(400); // PHP 5.4 or greater
    exit();
} catch (\Stripe\Error\SignatureVerification $e) {
    // Invalid signature
    _error_log("Stripe IPN sig_header [$sig_header]");
    _error_log("Stripe IPN endpoint_secret [$endpoint_secret]");
    _error_log("Stripe IPN Invalid signature ");
    http_response_code(400); // PHP 5.4 or greater
    exit();
}

if ($event->type == "payment_intent.succeeded") {
    $intent = $event->data->object;
    _error_log("Stripe IPN Succeeded: " . $intent->id);
} elseif ($event->type == "payment_intent.payment_failed") {
    $intent = $event->data->object;
    $error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";
    _error_log("Stripe IPN Failed: " . $intent->id . " " . $error_message);
}



//_error_log("StripeIPN: ".json_encode($obj));
_error_log("StripeIPN: POST " . json_encode($_POST));
_error_log("StripeIPN: GET " . json_encode($_GET));
_error_log("StripeIPN END");
?>