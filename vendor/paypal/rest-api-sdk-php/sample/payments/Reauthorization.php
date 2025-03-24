<?php
// ##Reauthorization Sample
// This sample code demonstrates how you can reauthorize a PayPal
// account payment.
// API used: v1/payments/authorization/{authorization_id}/reauthorize
/** @var Authorization $authorization */
$authorization = require 'AuthorizePayment.php';
use PayPal\Api\Amount;
use PayPal\Api\Authorization;

// ### Reauthorization
// Reauthorization is available only for PayPal account payments
// and not for credit card payments.

// You can reauthorize a payment only once 4 to 29
// days after the 3-day honor period for the original authorization
// has expired.

try {

    $amount = new Amount();
    $amount->setCurrency("USD");
    $amount->setTotal(1);

    // ### Reauthorize with amount being reauthorized
    $authorization->setAmount($amount);

    $reAuthorization = $authorization->reauthorize($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Reauthorize Payment", "Payment", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("Reauthorize Payment", "Payment", $authorization->getId(), null, $reAuthorization);
