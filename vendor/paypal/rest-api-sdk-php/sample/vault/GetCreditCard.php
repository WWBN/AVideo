<?php

// # Get Credit Card Sample
// The CreditCard resource allows you to
// retrieve previously saved CreditCards.
// API called: '/v1/vault/credit-card'
// The following code takes you through
// the process of retrieving a saved CreditCard
/** @var CreditCard $card */
$card = require 'CreateCreditCard.php';
$id = $card->getId();

use PayPal\Api\CreditCard;

/// ### Retrieve card
// (See bootstrap.php for more on `ApiContext`)
try {
    $card = CreditCard::get($card->getId(), $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Credit Card", "Credit Card", $card->getId(), null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Credit Card", "Credit Card", $card->getId(), null, $card);

return $card;
