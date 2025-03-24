<?php

// # Get Webhook Sample
//
// This sample code demonstrate how you can get a webhook, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#get-a-webhook
// API used: GET /v1/notifications/webhooks/<Webhook-Id>

// ## List Subscribed Event Types
// Use this call to retrieve the list of events types that are subscribed to a webhook.

/** @var \PayPal\Api\Webhook $webhook */
$webhook = require 'CreateWebhook.php';
$webhookId = $webhook->getId();

// ### Get List of Subscribed Event Types
try {
    $output = \PayPal\Api\WebhookEventType::subscribedEventTypes($webhookId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("List subscribed webhook event types", "WebhookEventTypeList", null, $webhookId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("List subscribed webhook event types", "WebhookEventTypeList",null, null, $output);

return $output;
