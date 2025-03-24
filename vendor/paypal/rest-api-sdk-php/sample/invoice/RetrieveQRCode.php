<?php

// # Retrieve QR Code for Invoice Sample
// Specify an invoice ID to get a QR code (image) that corresponds to the invoice ID. A QR code for an invoice can be added to a paper or PDF invoice. When a customer uses their mobile device to scan the QR code, the customer is redirected to the PayPal mobile payment flow, where they can pay online with PayPal or a credit card.

/** @var Invoice $invoice */
$invoice = require 'SendInvoice.php';

use PayPal\Api\Invoice;

try {

    // ### Retrieve QR Code of Sent Invoice
    // Retrieve QR Code of Sent Invoice by calling the
    // `qrCode` method
    // on the Invoice class by passing a valid
    // notification object
    // (See bootstrap.php for more on `ApiContext`)
    $image = Invoice::qrCode($invoice->getId(), array('height' => '300', 'width' => '300'), $apiContext);

    // ### Optionally Save to File
    // This is not a required step. However, if you want to store this image as a file, you can use
    // 'saveToFile' method with proper file name.
    // This will save the image as /samples/invoice/images/sample.png
    $path = $image->saveToFile("images/sample.png");


} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Retrieved QR Code for Invoice", "Invoice", $invoice->getId(), null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Retrieved QR Code for Invoice", "Invoice", $invoice->getId(), null, $image);

// ### Show the Image
// In PHP, there are many ways to present an images.
// One of the ways, you could directly inject the base64-encoded string
// with proper image information in front of it.
echo '<img src="data:image/png;base64,'. $image->getImage() . '" alt="Invoice QR Code" />';

