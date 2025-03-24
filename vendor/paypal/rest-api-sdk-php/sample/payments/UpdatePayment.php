<?php
// # Update Payment Sample
// This sample code demonstrate how you can
// update a Payment resources
// you've created using the Payments API.
// ## NOTE
// Note that it can only be updated before the execute is done. Once, the payment is executed it is not
// possible to udpate that.
// Docs: https://developer.paypal.com/webapps/developer/docs/api/#update-a-payment-resource
// API used: PATCH /v1/payments/payment/<Payment-Id>

/** @var Payment $createdPayment */
$createdPayment = require 'CreatePaymentUsingPayPal.php';
use PayPal\Api\Payment;

$paymentId = $createdPayment->getId();

// #### Create a Patch Request
// This is how the data would look like:
//    [
//            {
//                "op": "replace",
//                "path": "/transactions/0/amount",
//                "value": {
//                "total": "25.00",
//                    "currency": "USD",
//                    "details": {
//                    "subtotal": "17.50",
//                        "shipping": "6.20",
//                        "tax": "1.30"
//                    }
//                }
//            },
//            {
//                "op": "add",
//                "path": "/transactions/0/item_list/shipping_address",
//                "value": {
//                "recipient_name": "Gruneberg, Anna",
//                    "line1": "52 N Main St",
//                    "city": "San Jose",
//                    "postal_code": "95112",
//                    "country_code": "US",
//                    "state": "CA"
//                }
//            }
//        ]
$patchReplace = new \PayPal\Api\Patch();
$patchReplace->setOp('replace')
    ->setPath('/transactions/0/amount')
    ->setValue(json_decode('{
                    "total": "25.00",
                    "currency": "USD",
                    "details": {
                        "subtotal": "17.50",
                        "shipping": "6.20",
                        "tax":"1.30"
                    }
                }'));

$patchAdd = new \PayPal\Api\Patch();
$patchAdd->setOp('add')
    ->setPath('/transactions/0/item_list/shipping_address')
    ->setValue(json_decode('{
                    "recipient_name": "Gruneberg, Anna",
                    "line1": "52 N Main St",
                    "city": "San Jose",
                    "state": "CA",
                    "postal_code": "95112",
                    "country_code": "US"
                }'));

$patchRequest = new \PayPal\Api\PatchRequest();
$patchRequest->setPatches(array($patchReplace, $patchAdd));


// ### Update payment
// Update payment object by calling the
// static `update` method
// on the Payment class by passing a valid
// Payment ID
// (See bootstrap.php for more on `ApiContext`)
try {
    $result = $createdPayment->update($patchRequest, $apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Update Payment", "PatchRequest", null, $patchRequest, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Update Payment", "PatchRequest", $payment->getId(), $patchRequest, null);

// ### Getting Updated Payment Object
if ($result == true) {
    $result = Payment::get($createdPayment->getId(), $apiContext);
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Payment", "Payment", $result->getId(), null, $result);


// ### Get redirect url
// The API response provides the url that you must redirect
// the buyer to. Retrieve the url from the $payment->getLinks()
// method
foreach ($result->getLinks() as $link) {
    if ($link->getRel() == 'approval_url') {
        $approvalUrl = $link->getHref();
        break;
    }
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", "<a href='$approvalUrl' >$approvalUrl</a>", $request, $result);
}

return $result;
