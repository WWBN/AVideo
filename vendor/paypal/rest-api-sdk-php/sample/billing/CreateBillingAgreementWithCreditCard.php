<?php

// # Create Billing Agreement with Credit Card as Payment Source
//
// This sample code demonstrate how you can create a billing agreement, as documented here at:
// https://developer.paypal.com/webapps/developer/docs/api/#create-an-agreement
// API used: /v1/payments/billing-agreements

// Retrieving the Plan from the Create Update Sample. This would be used to
// define Plan information to create an agreement. Make sure the plan you are using is in active state.
/** @var Plan $createdPlan */
$createdPlan = require 'UpdatePlan.php';

use PayPal\Api\Agreement;
use PayPal\Api\CreditCard;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Plan;
use PayPal\Api\ShippingAddress;

/* Create a new instance of Agreement object
{
    "name": "DPRP",
    "description": "Payment with credit Card ",
    "start_date": "2015-06-17T9:45:04Z",
    "plan": {
      "id": "P-1WJ68935LL406420PUTENA2I"
    },
    "shipping_address": {
        "line1": "111 First Street",
        "city": "Saratoga",
        "state": "CA",
        "postal_code": "95070",
        "country_code": "US"
    },
    "payer": {
        "payment_method": "credit_card",
        "payer_info": {
          "email": "jaypatel512-facilitator@hotmail.com"
        },
        "funding_instruments": [
            {
                "credit_card": {
                    "type": "visa",
                    "number": "4417119669820331",
                    "expire_month": "12",
                    "expire_year": "2017",
                    "cvv2": "128"
                }
            }
        ]
    }
}*/
$agreement = new Agreement();

$agreement->setName('DPRP')
    ->setDescription('Payment with credit Card')
    ->setStartDate('2019-06-17T9:45:04Z');

// Add Plan ID
// Please note that the plan Id should be only set in this case.
$plan = new Plan();
$plan->setId($createdPlan->getId());
$agreement->setPlan($plan);

// Add Payer
$payer = new Payer();
$payer->setPaymentMethod('credit_card')
    ->setPayerInfo(new PayerInfo(array('email' => 'jaypatel512-facilitator@hotmail.com')));

// Add Credit Card to Funding Instruments
$creditCard = new CreditCard();
$creditCard->setType('visa')
    ->setNumber('4491759698858890')
    ->setExpireMonth('12')
    ->setExpireYear('2017')
    ->setCvv2('128');

$fundingInstrument = new FundingInstrument();
$fundingInstrument->setCreditCard($creditCard);
$payer->setFundingInstruments(array($fundingInstrument));
//Add Payer to Agreement
$agreement->setPayer($payer);

// Add Shipping Address
$shippingAddress = new ShippingAddress();
$shippingAddress->setLine1('111 First Street')
    ->setCity('Saratoga')
    ->setState('CA')
    ->setPostalCode('95070')
    ->setCountryCode('US');
$agreement->setShippingAddress($shippingAddress);

// For Sample Purposes Only.
$request = clone $agreement;

// ### Create Agreement
try {
    // Please note that as the agreement has not yet activated, we wont be receiving the ID just yet.
    $agreement = $agreement->create($apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Created Billing Agreement.", "Agreement", $agreement->getId(), $request, $ex);
    exit(1);
}

 // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Created Billing Agreement.", "Agreement", $agreement->getId(), $request, $agreement);

return $agreement;
