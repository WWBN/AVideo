<?php

$apiContext = require __DIR__ . '/../bootstrap.php';

// # Validate Webhook
// PHP Currently does not support certificate chain validation, that is necessary to validate webhook directly, from received data
// To resolve that, we need to use alternative, which includes making a GET call to obtain the data directly from PayPal.

//
// ## Received Body from Webhook
// Body received from webhook. This would be the data that you receive in the post request that comes from PayPal, to your webhook set URL.
// This is a sample data, that represents the webhook event data.
$bodyReceived = '{"id":"WH-36G56432PK518391U-9HW18392D95289106","create_time":"2015-06-01T20:21:13Z","resource_type":"sale","event_type":"PAYMENT.SALE.COMPLETED","summary":"Payment completed for $ 20.0 USD","resource":{"id":"2FY57107YS3937627","create_time":"2015-06-01T20:20:28Z","update_time":"2015-06-01T20:20:46Z","amount":{"total":"20.00","currency":"USD"},"payment_mode":"INSTANT_TRANSFER","state":"completed","protection_eligibility":"ELIGIBLE","protection_eligibility_type":"ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE","parent_payment":"PAY-2SV945219E505370PKVWL5DA","transaction_fee":{"value":"0.88","currency":"USD"},"links":[{"href":"https://api.sandbox.paypal.com/v1/payments/sale/2FY57107YS3937627","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/payments/sale/2FY57107YS3937627/refund","rel":"refund","method":"POST"},{"href":"https://api.sandbox.paypal.com/v1/payments/payment/PAY-2SV945219E505370PKVWL5DA","rel":"parent_payment","method":"GET"}]},"links":[{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-36G56432PK518391U-9HW18392D95289106","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-36G56432PK518391U-9HW18392D95289106/resend","rel":"resend","method":"POST"}]}';

/**
 * This is one way to receive the entire body that you received from PayPal webhook. This is one of the way to retrieve that information.
 * Just uncomment the below line to read the data from actual request.
 */
/** @var String $bodyReceived */
// $bodyReceived = file_get_contents('php://input');

// ### Validate Received Event Method
// Call the validateReceivedEvent() method with provided body, and apiContext object to validate
try {
    /** @var \PayPal\Api\WebhookEvent $output */
    $output = \PayPal\Api\WebhookEvent::validateAndGetReceivedEvent($bodyReceived, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Validate Received Webhook Event", "WebhookEvent", null, $bodyReceived, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("Validate Received Webhook Event", "WebhookEvent", $output->getId(), $bodyReceived, $output);


