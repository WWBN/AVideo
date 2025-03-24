<?php

// #GetPaymentList
// This sample code demonstrate how you can
// retrieve a list of all Payment resources
// you've created using the Payments API.
// Note various query parameters that you can
// use to filter, and paginate through the
// payments list.
// API used: GET /v1/payments/payments

require 'CreatePayment.php';
use PayPal\Api\Payment;


// ### Retrieve payment
// Retrieve the PaymentHistory object by calling the
// static `get` method on the Payment class, 
// and pass a Map object that contains
// query parameters for paginations and filtering.
// Refer the method doc for valid values for keys
// (See bootstrap.php for more on `ApiContext`)
try {

    $params = array('count' => 10, 'start_index' => 5);

    $payments = Payment::all($params, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("List Payments", "Payment", null, $params, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("List Payments", "Payment", null, $params, $payments);
