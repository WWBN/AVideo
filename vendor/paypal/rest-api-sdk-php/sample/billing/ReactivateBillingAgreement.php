<?php

// # Reactivate an agreement
//
// This sample code demonstrate how you can reactivate a billing agreement, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#suspend-an-agreement
// API used: /v1/payments/billing-agreements/<Agreement-Id>/suspend

// Retrieving the Agreement object from Suspend Agreement Sample to demonstrate the List
/** @var Agreement $suspendedAgreement */
$suspendedAgreement = require 'SuspendBillingAgreement.php';

use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;

//Create an Agreement State Descriptor, explaining the reason to suspend.
$agreementStateDescriptor = new AgreementStateDescriptor();
$agreementStateDescriptor->setNote("Reactivating the agreement");

try {

    $suspendedAgreement->reActivate($agreementStateDescriptor, $apiContext);

    // Lets get the updated Agreement Object
    $agreement = Agreement::get($suspendedAgreement->getId(), $apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printResult("Reactivate the Agreement", "Agreement", $agreement->getId(), $suspendedAgreement, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("Reactivate the Agreement", "Agreement", $agreement->getId(), $suspendedAgreement, $agreement);

return $agreement;
