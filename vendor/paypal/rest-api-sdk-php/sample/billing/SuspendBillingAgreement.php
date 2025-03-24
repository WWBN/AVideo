<?php

// # Suspend an agreement
//
// This sample code demonstrate how you can suspend a billing agreement, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#suspend-an-agreement
// API used: /v1/payments/billing-agreements/<Agreement-Id>/suspend

// Retrieving the Agreement object from Create Agreement Sample to demonstrate the List
/** @var Agreement $createdAgreement */
$createdAgreement = require 'CreateBillingAgreementWithCreditCard.php';

use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;

//Create an Agreement State Descriptor, explaining the reason to suspend.
$agreementStateDescriptor = new AgreementStateDescriptor();
$agreementStateDescriptor->setNote("Suspending the agreement");

try {

    $createdAgreement->suspend($agreementStateDescriptor, $apiContext);

    // Lets get the updated Agreement Object
    $agreement = Agreement::get($createdAgreement->getId(), $apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Suspended the Agreement", "Agreement", null, $agreementStateDescriptor, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Suspended the Agreement", "Agreement", $agreement->getId(), $agreementStateDescriptor, $agreement);

return $agreement;
