<?php

// # Record Refund Sample
// This sample code demonstrate how you can record
// an invoice as refunded.

/** @var Invoice $invoice */
$invoice = require 'RecordPayment.php';

use PayPal\Api\Invoice;
use PayPal\Api\RefundDetail;

try {
    // ### Record Object
    // Create a RefundDetail object, and fill in the required fields
    // You can use the new way of injecting json directly to the object.
    $refund = new RefundDetail(
        '{
          "date" : "2014-07-06 03:30:00 PST",
          "note" : "Refund provided by cash."
        }'
    );

    // ### Record Refund for Invoice
    // Record a refund on invoice object by calling the
    // `recordRefund` method
    // on the Invoice class by passing a valid
    // notification object
    // (See bootstrap.php for more on `ApiContext`)
    $refundStatus = $invoice->recordRefund($refund, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Refund for Invoice", "Invoice", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Refund for Invoice", "Invoice", $invoice->getId(), $refund, null);

// ### Retrieve Invoice
// Retrieve the invoice object by calling the
// static `get` method
// on the Invoice class by passing a valid
// Invoice ID
// (See bootstrap.php for more on `ApiContext`)
try {
    $invoice = Invoice::get($invoice->getId(), $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Invoice (Not Required - For Sample Only)", "Invoice", $invoice->getId(), $invoice->getId(), $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Invoice (Not Required - For Sample Only)", "Invoice", $invoice->getId(), $invoice->getId(), $invoice);

return $invoice;
