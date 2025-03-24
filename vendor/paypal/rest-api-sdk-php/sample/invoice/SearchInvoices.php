<?php

// # Search Invoices Sample
// This sample code demonstrate how you can
// search invoices from history.

/** @var Invoice $invoice */
$invoice = require 'CreateInvoice.php';
use PayPal\Api\Invoice;
use PayPal\Api\Search;

try {
    // ### Search Object
    // Fill up your search criteria for Invoice search.
    // Using the new way to inject raw json string to constructor
    $search = new Search(
        '{
          "start_invoice_date" : "2010-05-10 PST",
          "end_invoice_date" : "2019-05-11 PST",
          "page" : 1,
          "page_size" : 20,
          "total_count_required" : true
        }'
    );

    // ### Search Invoices
    // Retrieve the Invoice History object by calling the
    // static `search` method on the Invoice class.
    // Refer the method doc for valid values for keys
    // (See bootstrap.php for more on `ApiContext`)
    $invoices = Invoice::search($search, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Search Invoice", "Invoice", null, null, $ex);
    exit(1);
}
// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Search Invoice", "Invoice", null, $search, $invoices);
