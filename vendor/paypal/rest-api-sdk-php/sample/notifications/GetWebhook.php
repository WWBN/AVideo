<?php

// # Get Webhook Sample
//
// This sample code demonstrate how you can get a webhook, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#get-a-webhook
// API used: GET /v1/notifications/webhooks/<Webhook-Id>

// ## Get Webhook ID.
// In samples we are using CreateWebhook.php sample to get the created instance of webhook.
// However, in real case scenario, we could use just the ID from database or retrieved from the form.
/** @var \PayPal\Api\Webhook $webhook */
$webhook = require 'CreateWebhook.php';
$webhookId = $webhook->getId();

// ### Get Webhook
try {
    $output = \PayPal\Api\Webhook::get($webhookId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get a Webhook", "Webhook", null, $webhookId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get a Webhook", "Webhook", $output->getId(), null, $output);

return $output;
