<?php

// # Get Reference List of all Webhook Event Types
//
// This sample code demonstrate how you can get reference list of all webhook event types, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#get-a-reference-list-of-webhook-event-types
// API used: GET /v1/notifications/webhooks-event-types

$apiContext = require __DIR__ . '/../bootstrap.php';

// ### Get List of all Webhook event types
try {
    $output = \PayPal\Api\WebhookEventType::availableEventTypes($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get List of All Webhook Event Types", "WebhookEventTypeList", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get List of All Webhook Event Types", "WebhookEventTypeList", null, null, $output);

return $output;
