<?php

// # Get All Webhooks Sample
//
// Use this call to list all the webhooks, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#list-all-webhooks
// API used: GET /v1/notifications/webhooks

// ## List Webhooks

// This step is not necessarily required. We are creating a webhook for sample purpose only, so that we would not
// get an empty list at any point.
// In real case, you dont need to create any webhook to make this API call.
/** @var \PayPal\Api\Webhook $webhook */
$webhook = require_once __DIR__ . '/../bootstrap.php';

// ### Get List of All Webhooks
try {
    $output = \PayPal\Api\Webhook::getAll($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("List all webhooks", "WebhookList", null, $webhookId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("List all webhooks", "WebhookList",null, null, $output);

return $output;
