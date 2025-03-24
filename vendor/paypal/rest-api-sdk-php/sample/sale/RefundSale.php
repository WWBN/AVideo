<?php

// # Sale Refund Sample
// This sample code demonstrate how you can 
// process a refund on a sale transaction created 
// using the Payments API.
// API used: /v1/payments/sale/{sale-id}/refund

/** @var Sale $sale */
$sale = require 'GetSale.php';
$saleId = $sale->getId();

use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\Sale;

// ### Refund amount
// Includes both the refunded amount (to Payer) 
// and refunded fee (to Payee). Use the $amt->details
// field to mention fees refund details.
$amt = new Amount();
$amt->setCurrency('USD')
    ->setTotal(0.01);

// ### Refund object
$refund = new Refund();
$refund->setAmount($amt);

// ###Sale
// A sale transaction.
// Create a Sale object with the
// given sale transaction id.
$sale = new Sale();
$sale->setId($saleId);
try {
    // Create a new apiContext object so we send a new
    // PayPal-Request-Id (idempotency) header for this resource
    $apiContext = getApiContext($clientId, $clientSecret);

    // Refund the sale
    // (See bootstrap.php for more on `ApiContext`)
    $refundedSale = $sale->refund($refund, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Refund Sale", "Sale", $refundedSale->getId(), $refund, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Refund Sale", "Sale", $refundedSale->getId(), $refund, $refundedSale);

return $refundedSale;
