<?php
// # Delete Bank Account Sample
// This sample code demonstrate how you can
// delete a saved bank account
// API used: /v1/vault/bank-accounts/{<bankAccountId>}
// NOTE: HTTP method used here is DELETE

/** @var \PayPal\Api\BankAccount $card */
$bankAccount = require 'CreateBankAccount.php';

try {
    // ### Delete Card
    // Lookup and delete a saved credit card.
    // (See bootstrap.php for more on `ApiContext`)
    $bankAccount->delete($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Delete Bank Account", "Bank Account", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Delete Bank Account", "Bank Account", $bankAccount->getId(), null, null);
