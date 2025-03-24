<?php

// # Create Bulk Payout Sample
//
// This sample code demonstrate how you can create a synchronous payout sample, as documented here at:
// https://developer.paypal.com/docs/integration/direct/create-batch-payout/
// API used: /v1/payments/payouts

require __DIR__ . '/../bootstrap.php';

// Create a new instance of Payout object
$payouts = new \PayPal\Api\Payout();

// This is how our body should look like:
/*
 *{
    "sender_batch_header": {
        "sender_batch_id": "random_uniq_id",
        "email_subject": "You have a payment"
    },
    "items": [
        {
            "recipient_type": "EMAIL",
            "amount": {
                "value": 0.99,
                "currency": "USD"
            },
            "receiver": "shirt-supplier-one@mail.com",
            "note": "Thank you.",
            "sender_item_id": "item_1"
        },
        {
            "recipient_type": "EMAIL",
            "amount": {
                "value": 0.90,
                "currency": "USD"
            },
            "receiver": "shirt-supplier-two@mail.com",
            "note": "Thank you.",
            "sender_item_id": "item_2"
        },
        {
            "recipient_type": "EMAIL",
            "amount": {
                "value": 2.00,
                "currency": "USD"
            },
            "receiver": "shirt-supplier-three@mail.com",
            "note": "Thank you.",
            "sender_item_id": "item_3"
        }
    ]
}
 */

$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
// ### NOTE:
// You can prevent duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the batch will not be processed. For items, you can specify a `sender_item_id`. If the value for the `sender_item_id` is a duplicate of a payout item that was processed in the last 30 days, the item will not be processed.

// #### Batch Header Instance
$senderBatchHeader->setSenderBatchId(uniqid())
    ->setEmailSubject("You have a payment");

// #### Sender Item
// Please note that if you are using single payout with sync mode, you can only pass one Item in the request
$senderItem1 = new \PayPal\Api\PayoutItem();
$senderItem1->setRecipientType('Email')
    ->setNote('Thanks you.')
    ->setReceiver('shirt-supplier-one@gmail.com')
    ->setSenderItemId("item_1" . uniqid())
    ->setAmount(new \PayPal\Api\Currency('{
                        "value":"0.99",
                        "currency":"USD"
                    }'));

// #### Sender Item 2
// There are many different ways of assigning values in PayPal SDK. Here is another way where you could directly inject json string.
$senderItem2 = new \PayPal\Api\PayoutItem(
    '{
            "recipient_type": "EMAIL",
            "amount": {
                "value": 0.90,
                "currency": "USD"
            },
            "receiver": "shirt-supplier-two@mail.com",
            "note": "Thank you.",
            "sender_item_id": "item_2"
        }'
);

// #### Sender Item 3
// One more way of assigning values in constructor when creating instance of PayPalModel object. Injecting array.
$senderItem3 = new \PayPal\Api\PayoutItem(
    array(
        "recipient_type" => "EMAIL",
        "receiver" => "shirt-supplier-three@mail.com",
        "note" => "Thank you.",
        "sender_item_id" => uniqid(),
        "amount" => array(
            "value" => "0.90",
            "currency" => "USD"
        )

    )
);

$payouts->setSenderBatchHeader($senderBatchHeader)
    ->addItem($senderItem1)->addItem($senderItem2)->addItem($senderItem3);


// For Sample Purposes Only.
$request = clone $payouts;

// ### Create Payout
try {
    $output = $payouts->create(null, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Created Batch Payout", "Payout", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Created Batch Payout", "Payout", $output->getBatchHeader()->getPayoutBatchId(), $request, $output);

return $output;
