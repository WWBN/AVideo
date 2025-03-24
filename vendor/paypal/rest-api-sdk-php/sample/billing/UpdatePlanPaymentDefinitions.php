<?php

// # Update a plan
//
// This sample code demonstrate how you can update a billing plan, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#update-a-plan
// API used:  /v1/payments/billing-plans/<Plan-Id>

// ### Changing Plan Amount
// This example demonstrate how you could change the plan amount

// Retrieving the Plan object from Create Plan Sample to demonstrate the List
/** @var Plan $createdPlan */
$createdPlan = require 'CreatePlan.php';

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Plan;

try {
    $patch = new Patch();

    $paymentDefinitions = $createdPlan->getPaymentDefinitions();
    $paymentDefinitionId = $paymentDefinitions[0]->getId();
    $patch->setOp('replace')
        ->setPath('/payment-definitions/' . $paymentDefinitionId)
        ->setValue(json_decode(
            '{
                    "name": "Updated Payment Definition",
                    "frequency": "Day",
                    "amount": {
                        "currency": "USD",
                        "value": "50"
                    }
            }'
        ));
    $patchRequest = new PatchRequest();
    $patchRequest->addPatch($patch);

    $createdPlan->update($patchRequest, $apiContext);

    $plan = Plan::get($createdPlan->getId(), $apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Updated the Plan Payment Definition", "Plan", null, $patchRequest, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Updated the Plan Payment Definition", "Plan", $plan->getId(), $patchRequest, $plan);

return $plan;
