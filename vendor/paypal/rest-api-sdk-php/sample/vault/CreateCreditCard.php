<?php

// # Create Credit Card Sample
// You can store credit card details securely
// with PayPal. You can then use the returned
// Credit card id to process future payments.
// API used: POST /v1/vault/credit-card


require __DIR__ . '/../bootstrap.php';
use PayPal\Api\CreditCard;

// ### CreditCard
// A resource representing a credit card that is 
// to be stored with PayPal.
$card = new CreditCard();
$card->setType("visa")
    ->setNumber("4917912523797702")
    ->setExpireMonth("11")
    ->setExpireYear("2019")
    ->setCvv2("012")
    ->setFirstName("Joe")
    ->setLastName("Shopper");

// ### Additional Information
// Now you can also store the information that could help you connect
// your users with the stored credit cards.
// All these three fields could be used for storing any information that could help merchant to point the card.
// However, Ideally, MerchantId could be used to categorize stores, apps, websites, etc.
// ExternalCardId could be used for uniquely identifying the card per MerchantId. So, combination of "MerchantId" and "ExternalCardId" should be unique.
// ExternalCustomerId could be userId, user email, etc to group multiple cards per user.
$card->setMerchantId("MyStore1");
$card->setExternalCardId("CardNumber123" . uniqid());
$card->setExternalCustomerId("123123-myUser1@something.com");

// For Sample Purposes Only.
$request = clone $card;

// ### Save card
// Creates the credit card as a resource
// in the PayPal vault. The response contains
// an 'id' that you can use to refer to it
// in future payments.
// (See bootstrap.php for more on `ApiContext`)
try {
    $card->create($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Create Credit Card", "Credit Card", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Create Credit Card", "Credit Card", $card->getId(), $request, $card);

return $card;
