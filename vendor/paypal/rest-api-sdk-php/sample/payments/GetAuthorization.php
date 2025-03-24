<?php
// # GetAuthorization
// This sample code demonstrates how you can get details 
// of an authorized payment.
// API used: /v1/payments/authorization/<$authorizationId>

/** @var Authorization $authorization */
$authorization = require 'AuthorizePayment.php';
$authorizationId = $authorization->getId();
use PayPal\Api\Authorization;

// ### GetAuthorization
// You can retrieve info about an Authorization
// by invoking the Authorization::get method
// with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
// The return object contains the authorization state.

try {
    // Retrieve the authorization
    $result = Authorization::get($authorizationId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Authorization", "Authorization", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Authorization", "Authorization", $authorizationId, null, $result);

return $result;
