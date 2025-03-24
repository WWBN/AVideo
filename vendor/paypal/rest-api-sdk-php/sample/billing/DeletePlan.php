<?php

// # Delete Plan Sample
//
// This sample code demonstrate how you can delete a billing plan, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#retrieve-a-plan
// API used: /v1/payments/billing-plans

// Retrieving the Plan object from Create Plan Sample
/** @var Plan $createdPlan */
$createdPlan = require 'CreatePlan.php';

use PayPal\Api\Plan;

try {
    $result = $createdPlan->delete($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Deleted a Plan", "Plan", $createdPlan->getId(), null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Deleted a Plan", "Plan", $createdPlan->getId(), null, null);

return $createdPlan;
