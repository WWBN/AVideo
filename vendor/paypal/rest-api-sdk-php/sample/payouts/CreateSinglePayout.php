<?php

// # Create Single Synchronous Payout Sample
//
// This sample code demonstrate how you can create a synchronous payout sample, as documented here at:
// https://developer.paypal.com/docs/integration/direct/create-single-payout/
// API used: /v1/payments/payouts?sync_mode=true

require __DIR__ . '/../bootstrap.php';

// Create a new instance of Payout object
$payouts = new \PayPal\Api\Payout();

// This is how our body should look like:
/*
 * {
            "sender_batch_header":{
                "sender_batch_id":"2014021801",
                "email_subject":"You have a Payout!"
            },
            "items":[
                {
                    "recipient_type":"EMAIL",
                    "amount":{
                        "value":"1.0",
                        "currency":"USD"
                    },
                    "note":"Thanks for your patronage!",
                    "sender_item_id":"2014031400023",
                    "receiver":"shirt-supplier-one@mail.com"
                }
            ]
        }
 */

$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
// ### NOTE:
// You can prevent duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the batch will not be processed. For items, you can specify a `sender_item_id`. If the value for the `sender_item_id` is a duplicate of a payout item that was processed in the last 30 days, the item will not be processed.

// #### Batch Header Instance
$senderBatchHeader->setSenderBatchId(uniqid())
    ->setEmailSubject("You have a Payout!");

// #### Sender Item
// Please note that if you are using single payout with sync mode, you can only pass one Item in the request
$senderItem = new \PayPal\Api\PayoutItem();
$senderItem->setRecipientType('Email')
    ->setNote('Thanks for your patronage!')
    ->setReceiver('shirt-supplier-one@gmail.com')
    ->setSenderItemId("2014031400023")
    ->setAmount(new \PayPal\Api\Currency('{
                        "value":"1.0",
                        "currency":"USD"
                    }'));

$payouts->setSenderBatchHeader($senderBatchHeader)
    ->addItem($senderItem);


// For Sample Purposes Only.
$request = clone $payouts;

// ### Create Payout
try {
    $output = $payouts->createSynchronous($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Created Single Synchronous Payout", "Payout", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Created Single Synchronous Payout", "Payout", $output->getBatchHeader()->getPayoutBatchId(), $request, $output);

return $output;
