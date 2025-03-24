<?php

// # Create Payment using PayPal as payment method
// This sample code demonstrates how you can process a 
// PayPal Account based Payment.
// API used: /v1/payments/payment

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

// ## TLS Check
// We will add a separate unique endpoint specifically set for testing TLS check instead of using
// our conventional sandbox endpoint.
// TLS ENDPOINT: https://test-api.sandbox.paypal.com
// To test your own implementation to verify it TLS is successfully supported in your application, you can follow
// the following steps.
// 1. Create an APIContext object as usual. (No Change Required).
// 2. Add Configs as shown below to your apiContext object
$apiContext->setConfig(array('service.EndPoint'=>"https://test-api.sandbox.paypal.com"));
// 3. Thats it. Run your code, and see if it works as normal.
// 4. You can check sdk logs to verify it is infact pointing to the above URL instead of default sandbox one.

// ### Create a Payment for testing
// We will create a conventional paypal payment to verify its creation
$payer = new Payer();
$payer->setPaymentMethod("paypal");
$amount = new Amount();
$amount->setCurrency("USD")
    ->setTotal(20);
$transaction = new Transaction();
$transaction->setAmount($amount);
$baseUrl = getBaseUrl();
$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
    ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");
$payment = new Payment();
$payment->setIntent("sale")
    ->setPayer($payer)
    ->setRedirectUrls($redirectUrls)
    ->setTransactions(array($transaction));


// For Sample Purposes Only.
$request = clone $payment;
$curl_info = curl_version();
try {
    $payment->create($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("FAILURE: SECURITY WARNING: TLSv1.2 is not supported on this system. Please upgrade your curl to atleast 7.34.0.<br /> - Current Curl Version: " . $curl_info['version'] . "<br /> - Current OpenSSL Version:" . $curl_info['ssl_version'], "Payment", null, $request, $ex);
    exit(1);
}


// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("<b>SUCCESS</b>: Your server supports TLS protocols required for secure connection to PayPal Servers. <br /> - Current Curl Version: " . $curl_info['version'] . "<br /> - Current OpenSSL Version:" . $curl_info['ssl_version'], null, null, null, "SUCCESS. Your system supports TLSv1.2");

return $payment;
