<?php

// # Get Payout Batch Status Sample
//
// This sample code demonstrate how you can get the batch payout status of a created batch payout, as documented here at:
// https://developer.paypal.com/docs/api/#get-the-status-of-a-batch-payout
// API used: GET /v1/payments/payouts/<Payout-Batch-Id>

/** @var \PayPal\Api\PayoutBatch $payoutBatch */
$payoutBatch = require 'CreateBatchPayout.php';
// ## Payout Batch ID
// You can replace this with your Payout Batch Id on already created Payout.
$payoutBatchId = $payoutBatch->getBatchHeader()->getPayoutBatchId();

// ### Get Payout Batch Status
try {
    $output = \PayPal\Api\Payout::get($payoutBatchId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Payout Batch Status", "PayoutBatch", null, $payoutBatchId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Payout Batch Status", "PayoutBatch", $output->getBatchHeader()->getPayoutBatchId(), null, $output);

return $output;
