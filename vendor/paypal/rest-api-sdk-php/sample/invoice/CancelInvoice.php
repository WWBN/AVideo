<?php

// # Cancel Invoice Sample
// This sample code demonstrate how you can cancel
// an invoice.

/** @var Invoice $invoice */
$invoice = require 'SendInvoice.php';

use PayPal\Api\CancelNotification;
use PayPal\Api\Invoice;

try {

    // ### Cancel Notification Object
    // This would send a notification to both merchant as well
    // the payer about the cancellation. The information of
    // merchant and payer is retrieved from the invoice details
    $notify = new CancelNotification();
    $notify
        ->setSubject("Past due")
        ->setNote("Canceling invoice")
        ->setSendToMerchant(true)
        ->setSendToPayer(true);


    // ### Cancel Invoice
    // Cancel invoice object by calling the
    // static `cancel` method
    // on the Invoice class by passing a valid
    // notification object
    // (See bootstrap.php for more on `ApiContext`)
    $cancelStatus = $invoice->cancel($notify, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Cancel Invoice", "Invoice", null, $notify, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Cancel Invoice", "Invoice", $invoice->getId(), $notify, null);
