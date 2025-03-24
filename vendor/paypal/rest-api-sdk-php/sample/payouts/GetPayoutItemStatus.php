<?php

// # Get Payout Item Status Sample
//
// Use this call to get data about a payout item, including the status, without retrieving an entire batch. You can get the status of an individual payout item in a batch in order to review the current status of a previously-unclaimed, or pending, payout item.
// https://developer.paypal.com/docs/api/#get-the-status-of-a-payout-item
// API used: GET /v1/payments/payouts-item/<Payout-Item-Id>

/** @var \PayPal\Api\PayoutBatch $payoutBatch */
$payoutBatch = require 'GetPayoutBatchStatus.php';
// ## Payout Item ID
// You can replace this with your Payout Batch Id on already created Payout.
$payoutItems = $payoutBatch->getItems();
$payoutItem = $payoutItems[0];
$payoutItemId = $payoutItem->getPayoutItemId();

// ### Get Payout Item Status
try {
    $output = \PayPal\Api\PayoutItem::get($payoutItemId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Payout Item Status", "PayoutItem", null, $payoutItemId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Payout Item Status", "PayoutItem", $output->getPayoutItemId(), null, $output);

return $output;
