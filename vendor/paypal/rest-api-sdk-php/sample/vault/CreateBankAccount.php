<?php

// # Create Bank Account Sample
// You can store credit card details securely
// with PayPal. You can then use the returned
// Bank Account id to process future payments.
// API used: POST /v1/vault/bank-accounts

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\BankAccount;

// ### Bank Account
// A resource representing a bank account that is
// to be stored with PayPal.
/*
        {
            "account_number": "4417119669820331",
            "account_number_type": "IBAN",
            "account_type": "SAVINGS",
            "account_name": "Ramraj",
            "check_type": "PERSONAL",
            "auth_type": "WEB",
            "bank_name": "CITI",
            "country_code": "US",
            "first_name": "Ramraj",
            "last_name": "K",
            "birth_date": "1987-08-13",
            "billing_address": {
                "line1": "52 N Main ST",
                "city": "Johnstown",
                "country_code": "US",
                "postal_code": "43210",
                "state": "OH",
                "phone": "408-334-8890"
            },
            "external_customer_id": "external_id"
        }
*/
$bankAccount = new BankAccount();
$bankAccount->setAccountNumber("4417119669820331")
    ->setAccountNumberType("IBAN")
    ->setAccountType("SAVINGS")
    ->setAccountName("Ramraj")
    ->setCheckType("PERSONAL")
    ->setAuthType("WEB")
    ->setBankName("CITI")
    ->setCountryCode("US")
    ->setFirstName("Ramraj")
    ->setLastName("K")
    ->setBirthDate("1987-08-13")
    ->setExternalCustomerId(uniqid());

$billingAddress = new \PayPal\Api\Address();
$billingAddress->setLine1("52 N Main St")
    ->setCity("Johnstown")
    ->setState("OH")
    ->setCountryCode("US")
    ->setPostalCode("43210")
    ->setPhone("408-334-8890");

$bankAccount->setBillingAddress($billingAddress);

// For Sample Purposes Only.
$request = clone $bankAccount;

// ### Save bank account
// Creates the bank account as a resource
// in the PayPal vault. The response contains
// an 'id' that you can use to refer to it
// in future payments.
// (See bootstrap.php for more on `ApiContext`)
try {
    $bankAccount->create($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Create Bank Account", "Bank Account", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Create Bank Account", "Bank Account", $bankAccount->getId(), $request, $bankAccount);

return $bankAccount;
