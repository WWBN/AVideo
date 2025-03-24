<?php

// #Execute Agreement
// This is the second part of CreateAgreement Sample.
// Use this call to execute an agreement after the buyer approves it
require __DIR__ . '/../bootstrap.php';

// ## Approval Status
// Determine if the user accepted or denied the request
if (isset($_GET['success']) && $_GET['success'] == 'true') {

    $token = $_GET['token'];
    $agreement = new \PayPal\Api\Agreement();
    try {
        // ## Execute Agreement
        // Execute the agreement by passing in the token
        $agreement->execute($token, $apiContext);
    } catch (Exception $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	    ResultPrinter::printError("Executed an Agreement", "Agreement", $agreement->getId(), $_GET['token'], $ex);
        exit(1);
    }

    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printResult("Executed an Agreement", "Agreement", $agreement->getId(), $_GET['token'], $agreement);

    // ## Get Agreement
    // Make a get call to retrieve the executed agreement details
    try {
        $agreement = \PayPal\Api\Agreement::get($agreement->getId(), $apiContext);
    } catch (Exception $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	    ResultPrinter::printError("Get Agreement", "Agreement", null, null, $ex);
        exit(1);
    }

    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printResult("Get Agreement", "Agreement", $agreement->getId(), null, $agreement);

} else {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printResult("User Cancelled the Approval", null);
}
