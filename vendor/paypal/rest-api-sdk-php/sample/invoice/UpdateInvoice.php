<?php

// # Update Invoice Sample
// This sample code demonstrate how you can update
// an invoice.

/** @var Invoice $invoice */
$invoice = require 'CreateInvoice.php';
use PayPal\Api\Invoice;

// For Sample Purposes Only.
$request = clone $invoice;

// ### Update Invoice
// Lets update some information
$invoice->setInvoiceDate("2014-12-16 PST");

// ### NOTE: These are the work-around added to the
// sample, to get past the bug in PayPal APIs.
// There is already an internal ticket #PPTIPS-1932 created for it.
$invoice->setDiscount(null);
$billingInfo = $invoice->getBillingInfo()[0];
$billingInfo->setAddress(null);
$invoice->getPaymentTerm()->setDueDate(null);

try {
    // ### Update Invoice
    // Update an invoice by calling the invoice->update() method
    // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
    $invoice->update($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Invoice Updated", "Invoice", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Invoice Updated", "Invoice", $invoice->getId(), $request, $invoice);

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
