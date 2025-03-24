<?php

// # Search Billing Transactions Sample
//
// This sample code demonstrate how you can search all billing transactions, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#search-for-transactions
// API used: GET /v1/payments/billing-agreements/<Agreement-Id>/transactions? start-date=yyyy-mm-dd&end-date=yyyy-mm-dd

// Retrieving the Agreement object from Get Billing Agreement. This may not be necessary if you are trying to search for transactions of already created Agreement.
/** @var Agreement $agreement */
$agreement = require 'GetBillingAgreement.php';

// Replace this with your AgreementId to search transactions based on your agreement.
$agreementId = $agreement->getId();

use PayPal\Api\Agreement;

// Adding Params to search transaction within a given time frame.
$params = array('start_date' => date('Y-m-d', strtotime('-15 years')), 'end_date' => date('Y-m-d', strtotime('+5 days')));

try {
    $result = Agreement::searchTransactions($agreementId, $params, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Search for Transactions", "AgreementTransaction", $agreementId, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Search for Transactions", "AgreementTransaction", $agreementId, $params, $result);

return $agreement;
