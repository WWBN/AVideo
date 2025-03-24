<?php

// # Get List of Plan Sample
//
// This sample code demonstrate how you can get a list of billing plan, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#list-plans
// API used: /v1/payments/billing-plans

// Retrieving the Plan object from Create Plan Sample to demonstrate the List
/** @var Plan $createdPlan */
$createdPlan = require 'CreatePlan.php';

use PayPal\Api\Plan;

try {
    // Get the list of all plans
    // You can modify different params to change the return list.
    // The explanation about each pagination information could be found here
    // at https://developer.paypal.com/webapps/developer/docs/api/#list-plans
    $params = array('page_size' => '2');
    $planList = Plan::all($params, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("List of Plans", "Plan", null, $params, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("List of Plans", "Plan", null, $params, $planList);

return $planList;
