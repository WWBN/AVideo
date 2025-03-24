<?php

// # Update an agreement
//
// This sample code demonstrate how you can update a billing agreement, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#update-an-agreement
// API used: /v1/payments/billing-agreements/<Agreement-Id>

// Retrieving the Agreement object from Create Agreement Sample to demonstrate the List
/** @var Agreement $createdAgreement */
$createdAgreement = require 'CreateBillingAgreementWithCreditCard.php';

use PayPal\Api\Agreement;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;

$patch = new Patch();

$patch->setOp('replace')
    ->setPath('/')
    ->setValue(json_decode('{
            "description": "New Description",
            "shipping_address": {
                "line1": "2065 Hamilton Ave",
                "city": "San Jose",
                "state": "CA",
                "postal_code": "95125",
                "country_code": "US"
            }
        }'));
$patchRequest = new PatchRequest();
$patchRequest->addPatch($patch);
try {
    $createdAgreement->update($patchRequest, $apiContext);

    // Lets get the updated Agreement Object
    $agreement = Agreement::get($createdAgreement->getId(), $apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Updated the Agreement with new Description and Updated Shipping Address", "Agreement", null, $patchRequest, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Updated the Agreement with new Description and Updated Shipping Address", "Agreement", $agreement->getId(), $patchRequest, $agreement);

return $agreement;
