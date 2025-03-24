<?php

// # List Invoices Sample
// This sample code demonstrate how you can get
// all invoice from history.

/** @var Invoice $invoice */
$invoice = require 'CreateInvoice.php';
use PayPal\Api\Invoice;

try {
    // ### Retrieve Invoices
    // Retrieve the Invoice History object by calling the
    // static `get_all` method on the Invoice class.
    // Refer the method doc for valid values for keys
    // (See bootstrap.php for more on `ApiContext`)
    $invoices = Invoice::getAll(array('page' => 0, 'page_size' => 4, 'total_count_required' => "true"), $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Lookup Invoice History", "Invoice", null, null, $ex);
    exit(1);
}
// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Lookup Invoice History", "Invoice", null, null, $invoices);
