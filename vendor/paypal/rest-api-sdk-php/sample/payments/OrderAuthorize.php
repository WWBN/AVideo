<?php
// #Authorize Order Sample
// To authorize an order payment, pass the orderId in the URI of a POST call. This begins the process of confirming that funds are available until it is time to complete the payment transaction.
// API used: POST /v1/payments/orders/<Order-Id>/authorize

/** @var \PayPal\Api\Payment $payment */
$payment = require __DIR__ . '/ExecutePayment.php';

use PayPal\Api\Amount;
use PayPal\Api\Authorization;

// ### Approval Status
// Determine if the user approved the payment or not
if (isset($_GET['success']) && $_GET['success'] == 'true') {

    // ### Retrieve the order
    // OrderId could be retrieved by parsing the object inside related_resources.
    $transactions = $payment->getTransactions();
    $transaction = $transactions[0];
    $relatedResources = $transaction->getRelatedResources();
    $relatedResource = $relatedResources[0];
    $order = $relatedResource->getOrder();

    // ### Create Authorization Object
    // with Amount in it
    $authorization = new Authorization();
    $authorization->setAmount(new Amount(
        '{
            "total": "2.00",
            "currency": "USD"
        }'
    ));

    try {
        // ### Authorize Order
        // Authorize the order by passing authorization object we created.
        // We will get a new authorization object back.
        $result = $order->authorize($authorization, $apiContext);
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        ResultPrinter::printResult("Authorized Order", "Authorization", $result->getId(), $authorization, $result);
    } catch (Exception $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	    ResultPrinter::printError("Authorized Order", "Authorization", null, $authorization, $ex);
        exit(1);
    }

    return $result;

} else {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printResult("User Cancelled the Approval", null);
    exit;
}
