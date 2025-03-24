<?php

// # Delete All Webhook Sample
// This is a sample helper method, to delete all existing webhooks, because of limited number of webhooks that are allowed per app.
// To properly use the sample, change the clientId and Secret from bootstrap.php file with your own app ClientId and Secret.

// ## Get Webhook Instance

/** @var \PayPal\Api\WebhookList $webhookList */
$webhookList = require 'ListWebhooks.php';

// ### Delete Webhook
try {
    foreach ($webhookList->getWebhooks() as $webhook) {
        $webhook->delete($apiContext);
    }
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Deleted all Webhooks", "WebhookList", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Delete all Webhook, as it may have exceed the maximum count.", "WebhookList", null, null, null);

return $output;
