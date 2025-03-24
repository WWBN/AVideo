<?php

// # Get Bank Account Sample
// The Bank Account resource allows you to
// retrieve previously saved Bank Accounts.
// API called: '/v1/vault/bank-accounts'

// The following code takes you through
// the process of retrieving a saved Bank Account

/** @var \PayPal\Api\BankAccount $bankAccount */
$bankAccount = require 'CreateBankAccount.php';

/// ### Retrieve Bank Account
// (See bootstrap.php for more on `ApiContext`)
try {
    $bankAccount = \PayPal\Api\BankAccount::get($bankAccount->getId(), $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Bank Account", "Bank Account", $bankAccount->getId(), null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Bank Account", "Bank Account", $bankAccount->getId(), null, $bankAccount);

return $bankAccount;
