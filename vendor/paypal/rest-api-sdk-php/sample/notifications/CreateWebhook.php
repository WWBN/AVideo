<?php

// # Create Webhook Sample
//
// This sample code demonstrate how you can create a webhook, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#create-a-webhook
// API used: POST /v1/notifications/webhooks

require __DIR__ . '/../bootstrap.php';

// Create a new instance of Webhook object
$webhook = new \PayPal\Api\Webhook();

// # Basic Information
//     {
//         "url":"https://requestb.in/10ujt3c1",
//         "event_types":[
//            {
//                "name":"PAYMENT.AUTHORIZATION.CREATED"
//            },
//            {
//                "name":"PAYMENT.AUTHORIZATION.VOIDED"
//            }
//         ]
//      }
// Fill up the basic information that is required for the webhook
// The URL should be actually accessible over the internet. Having a localhost here would not work.
// #### There is an open source tool http://requestb.in/ that allows you to receive any web requests to a url given there.
// #### NOTE: Please note that you need an https url for paypal webhooks. You can however override the url with https, and accept
// any warnings your browser might show you. Also, please note that this is entirely for demo purposes, and you should not
// be using this in production
$webhook->setUrl("https://requestb.in/10ujt3c1?uniqid=" . uniqid());

// # Event Types
// Event types correspond to what kind of notifications you want to receive on the given URL.
$webhookEventTypes = array();
$webhookEventTypes[] = new \PayPal\Api\WebhookEventType(
    '{
        "name":"PAYMENT.AUTHORIZATION.CREATED"
    }'
);
$webhookEventTypes[] = new \PayPal\Api\WebhookEventType(
    '{
        "name":"PAYMENT.AUTHORIZATION.VOIDED"
    }'
);
$webhook->setEventTypes($webhookEventTypes);

// For Sample Purposes Only.
$request = clone $webhook;

// ### Create Webhook
try {
    $output = $webhook->create($apiContext);
} catch (Exception $ex) {
    // ^ Ignore workflow code segment
    if ($ex instanceof \PayPal\Exception\PayPalConnectionException) {
        $data = $ex->getData();
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        ResultPrinter::printError("Created Webhook Failed. Checking if it is Webhook Number Limit Exceeded. Trying to delete all existing webhooks", "Webhook", "Please Use <a style='color: red;' href='DeleteAllWebhooks.php' >Delete All Webhooks</a> Sample to delete all existing webhooks in sample", $request, $ex);
        if (strpos($data,'WEBHOOK_NUMBER_LIMIT_EXCEEDED') !== false) {
            require 'DeleteAllWebhooks.php';
            try {
                $output = $webhook->create($apiContext);
            } catch (Exception $ex) {
                // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
                ResultPrinter::printError("Created Webhook", "Webhook", null, $request, $ex);
                exit(1);
            }
        } else {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	        ResultPrinter::printError("Created Webhook", "Webhook", null, $request, $ex);
            exit(1);
        }
    } else {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        ResultPrinter::printError("Created Webhook", "Webhook", null, $request, $ex);
        exit(1);
    }
    // Print Success Result
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Created Webhook", "Webhook", $output->getId(), $request, $output);

return $output;
