<?php

// # Update Credit Card Sample
// The CreditCard resource allows you to
// update previously saved CreditCards.
// API called: PATCH /v1/vault/credit-cards/<Credit-Card-Id>
// The following code takes you through
// the process of updating a saved CreditCard

/** @var CreditCard $card */
$card = require 'CreateCreditCard.php';
$id = $card->getId();

use PayPal\Api\CreditCard;
use PayPal\Api\Patch;

// ### Patch Object
// You could update a credit card by sending patch requests. Each path object would have a specific detail in the object to be updated.
$pathOperation = new Patch();
$pathOperation->setOp("replace")
    ->setPath('/expire_month')
    ->setValue("12");

// ### Another Patch Object
// You could set more than one patch while updating a credit card.
$pathOperation2 = new Patch();
$pathOperation2->setOp('add')
    ->setPath('/billing_address')
    ->setValue(json_decode('{
            "line1": "111 First Street",
            "city": "Saratoga",
            "country_code": "US",
            "state": "CA",
            "postal_code": "95070"
        }'));

$pathRequest = new \PayPal\Api\PatchRequest();
$pathRequest->addPatch($pathOperation)
    ->addPatch($pathOperation2);
/// ### Update Credit Card
// (See bootstrap.php for more on `ApiContext`)
try {
    $card = $card->update($pathRequest, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Updated Credit Card", "Credit Card", $card->getId(), $pathRequest, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Updated Credit Card", "Credit Card", $card->getId(), $pathRequest, $card);

return $card;
