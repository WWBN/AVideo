<?php

// # Search Webhook Events Sample
//
// This sample code demonstrate how to use this call to search for all webhook events., as documented here at:
// https://developer.paypal.com/docs/api/#search-webhook-events
// API used: GET /v1/notifications/webhooks-events

// ## Get Webhook Instance
// ## PLEASE NOTE:
// Creating webhook is sample purposes only. In real scenario, you dont need to create a new webhook everytime you want to search
// for a webhook events. This is made in a sample just to make sure there is minimum of one webhook to listen to.
/** @var \PayPal\Api\Webhook $webhook */
$webhook = require __DIR__ . '/../bootstrap.php';

$params = array(
   // 'start_time'=>'2014-12-06T11:00:00Z',
   // 'end_time'=>'2014-12-12T11:00:00Z'
);

// ### Search Webhook events
try {
    $output = \PayPal\Api\WebhookEvent::all($params, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Search Webhook events", "WebhookEventList", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Search Webhook events", "WebhookEventList", null, $params, $output);


return $output;
