<?php
// check recurrent payments
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
error_log("StripeIPN Start");
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$walletObject = YouPHPTubePlugin::getObjectData("YPTWallet");
$stripe = YouPHPTubePlugin::loadPluginIfEnabled("StripeYPT");

$stripe->start();
$webhook = $stripe->getWebhook();

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = $webhook->id;

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

error_log("StripeIPN: WEBHOOK: ".json_encode($webhook));
error_log("StripeIPN: payload ".json_encode($payload));
error_log("StripeIPN: sig_header ".json_encode($sig_header));

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    error_log("Stripe IPN Invalid payload ");
    http_response_code(400); // PHP 5.4 or greater
    exit();
} catch(\Stripe\Error\SignatureVerification $e) {
    // Invalid signature
    error_log("Stripe IPN Invalid signature ");
    http_response_code(400); // PHP 5.4 or greater
    exit();
}

if ($event->type == "payment_intent.succeeded") {
    $intent = $event->data->object;
    error_log("Stripe IPN Succeeded: ". $intent->id);
} elseif ($event->type == "payment_intent.payment_failed") {
    $intent = $event->data->object;
    $error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";
    error_log("Stripe IPN Failed: ".$intent->id." ".$error_message);
}



//error_log("StripeIPN: ".json_encode($obj));
error_log("StripeIPN: POST ".json_encode($_POST));
error_log("StripeIPN: GET ".json_encode($_GET));
error_log("StripeIPN END");

?>