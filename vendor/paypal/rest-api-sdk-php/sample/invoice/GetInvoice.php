<?php

// # Get Invoice Sample
// This sample code demonstrate how you can retrieve
// an invoice.

/** @var Invoice $invoice */
$invoice = require 'CreateInvoice.php';
use PayPal\Api\Invoice;

$invoiceId = $invoice->getId();

// ### Retrieve Invoice
// Retrieve the invoice object by calling the
// static `get` method
// on the Invoice class by passing a valid
// Invoice ID
// (See bootstrap.php for more on `ApiContext`)
try {
    $invoice = Invoice::get($invoiceId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Invoice", "Invoice", $invoice->getId(), $invoiceId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Invoice", "Invoice", $invoice->getId(), $invoiceId, $invoice);

return $invoice;
