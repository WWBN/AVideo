<?php

// # Cancel Payout Item Status Sample
//
// Use this call to cancel an existing, unclaimed transaction. If an unclaimed item is not claimed within 30 days, the funds will be automatically returned to the sender. This call can be used to cancel the unclaimed item prior to the automatic 30-day return.
// https://developer.paypal.com/docs/api/#cancel-an-unclaimed-payout-item
// API used: POST /v1/payments/payouts-item/<Payout-Item-Id>/cancel

/** @var \PayPal\Api\PayoutBatch $payoutBatch */
$payoutBatch = require 'CreateSinglePayout.php';
// ## Payout Item ID
// You can replace this with your Payout Batch Id on already created Payout.
$payoutItems = $payoutBatch->getItems();
$payoutItem = $payoutItems[0];
$payoutItemId = $payoutItem->getPayoutItemId();

$output = null;
// ### Cancel Payout Item
// Check if Payout Item is UNCLAIMED, and if so, cancel it.
try {
    if ($payoutItem->getTransactionStatus() == 'UNCLAIMED') {
        // Cancel the Payout Item
        $output = \PayPal\Api\PayoutItem::cancel($payoutItemId, $apiContext);
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        ResultPrinter::printResult("Cancel Unclaimed Payout Item", "PayoutItem", $output->getPayoutItemId(), null, $output);
    } else {
        // The item transaction status is not unclaimed. You can only cancel an unclaimed transaction.
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	    ResultPrinter::printError("Cancel Unclaimed Payout Item", "PayoutItem", null, $payoutItemId, new Exception("Payout Item Status is not UNCLAIMED"));
    }
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Cancel Unclaimed Payout Item", "PayoutItem", null, $payoutItemId, $ex);
    exit(1);
}

return $output;
