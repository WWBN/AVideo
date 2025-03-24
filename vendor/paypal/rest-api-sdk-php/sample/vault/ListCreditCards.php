<?php

// # List Credit Card Sample
// The CreditCard resource allows you to
// retrieve all previously saved CreditCards.
// API called: '/v1/vault/credit-cards'
// Documentation: https://developer.paypal.com/webapps/developer/docs/api/#list-credit-card-resources

// Creating a Credit Card just in case
/** @var CreditCard $card */
$card = require 'CreateCreditCard.php';

use PayPal\Api\CreditCard;

/// ### List All Credit Cards
// (See bootstrap.php for more on `ApiContext`)
try {
    // ### Parameters to Filter
    // There are many possible filters that you could apply to it. For complete list, please refere to developer docs at above link.

    $params = array(
        "sort_by" => "create_time",
        "sort_order" => "desc",
        "merchant_id" => "MyStore1"  // Filtering by MerchantId set during CreateCreditCard.
    );
    $cards = CreditCard::all($params, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("List All Credit Cards", "CreditCardList", null, $params, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("List All Credit Cards", "CreditCardList", null, $params, $cards);

return $card;
