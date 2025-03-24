<?php

// # Record Payment Sample
// This sample code demonstrate how you can record
// an invoice as paid.

/** @var Invoice $invoice */
$invoice = require 'SendInvoice.php';

use PayPal\Api\Invoice;
use PayPal\Api\PaymentDetail;

try {
    // ### Record Object
    // Create a PaymentDetail object, and fill in the required fields
    // You can use the new way of injecting json directly to the object.
    $record = new PaymentDetail(
        '{
          "method" : "CASH",
          "date" : "2014-07-06 03:30:00 PST",
          "note" : "Cash received."
        }'
    );

    // ### Record Payment for Invoice
    // Record a payment on invoice object by calling the
    // `recordPayment` method
    // on the Invoice class by passing a valid
    // notification object
    // (See bootstrap.php for more on `ApiContext`)
    $recordStatus = $invoice->recordPayment($record, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Payment for Invoice", "Invoice", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Payment for Invoice", "Invoice", $invoice->getId(), $record, null);

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
