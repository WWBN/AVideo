<?php

// # Create Payment using PayPal as payment method
// This sample code demonstrates how you can process a 
// PayPal Account based Payment.
// API used: /v1/payments/payment

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\FuturePayment;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

session_start();

// ### Payer
// A resource representing a Payer that funds a payment
// For paypal account payments, set payment method
// to 'paypal'.
$payer = new Payer();
$payer->setPaymentMethod("paypal");

// ### Amount
// Lets you specify a payment amount.
// You can also specify additional details
// such as shipping, tax.
$amount = new Amount();
$amount->setCurrency("USD")
    ->setTotal("0.17");

// ### Transaction
// A transaction defines the contract of a
// payment - what is the payment for and who
// is fulfilling it. 
$transaction = new Transaction();
$transaction->setAmount($amount)
    ->setDescription("Payment description");

// ### Redirect urls
// Set the urls that the buyer must be redirected to after 
// payment approval/ cancellation.
$baseUrl = getBaseUrl();
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
    ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

// ### Payment
// A Payment Resource; create one using
// the above types and intent set to 'sale'
$payment = new FuturePayment();
$payment->setIntent("authorize")
    ->setPayer($payer)
    ->setRedirectUrls($redirectUrls)
    ->setTransactions(array($transaction));

// ### Get Refresh Token
// You need to get a permanent refresh token from the authorization code, retrieved from the mobile sdk.

// authorization code from mobile sdk
$authorizationCode = 'EK7_MAKlB4QxW1dWKnvnr_CEdLKnpH3vnGAf155Eg8yO8e_7VaQonsqIbTK9CR7tUsoIN2eCc5raOfaGbZDCT0j6k_BDE8GkyLgk8ulcQyR_3S-fgBzjMzBwNqpj3AALgCVR03zw1iT8HTsxZXp3s2U';

// Client Metadata id from mobile sdk
// For more information look for PayPal-Client-Metadata-Id in https://developer.paypal.com/docs/api/#authentication--headers
$clientMetadataId = '123123456';

try {
    // Exchange authorization_code for long living refresh token. You should store
    // it in a database for later use
    $refreshToken = FuturePayment::getRefreshToken($authorizationCode, $apiContext);

    // Update the access token in apiContext
    $payment->updateAccessToken($refreshToken, $apiContext);

    // For Sample Purposes Only.
    $request = clone $payment;

    // ### Create Future Payment
    // Create a payment by calling the 'create' method
    // passing it a valid apiContext.
    // (See bootstrap.php for more on `ApiContext`)
    // The return object contains the state and the
    // url to which the buyer must be redirected to
    // for payment approval
    // Please note that currently future payments works only with PayPal as a funding instrument.
    $payment->create($apiContext, $clientMetadataId);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Future Payment", "Payment", null, $payment, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Future Payment", "Payment", $payment->getId(), $request, $payment);

return $payment;
