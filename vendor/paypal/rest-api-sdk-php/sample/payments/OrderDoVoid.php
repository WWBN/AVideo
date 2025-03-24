<?php
// #Void Order Sample
// Use this call to void an existing order.
// Note: An order cannot be voided if payment has already been partially or fully captured.
// API used: POST /v1/payments/orders/<Order-Id>/do-void

/** @var \PayPal\Api\Payment $payment */
$payment = require __DIR__ . '/ExecutePayment.php';

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

    try {
        // ### Void Order
        // Call void method on order object. You will get an Order Object back
        $result = $order->void($apiContext);
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        ResultPrinter::printResult("Voided Order", "Order", $result->getId(), null, $result);
    } catch (Exception $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	    ResultPrinter::printError("Voided Order", "Order", null, null, $ex);
        exit(1);
    }

    return $result;

} else {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("User Cancelled the Approval", null);
    exit;
}
