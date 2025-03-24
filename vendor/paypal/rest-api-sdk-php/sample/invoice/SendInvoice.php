<?php

// # Create Invoice Sample
// This sample code demonstrate how you can send
// a legitimate invoice to the payer

/** @var Invoice $invoice */
$invoice = require 'CreateInvoice.php';

use PayPal\Api\Invoice;

try {

    // ### Send Invoice
    // Send a legitimate invoice to the payer
    // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
    $sendStatus = $invoice->send($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Send Invoice", "Invoice", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Send Invoice", "Invoice", $invoice->getId(), null, null);

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
