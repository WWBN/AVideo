<?php

namespace Sample;

require __DIR__ . '/../vendor/autoload.php';

use Sample\PayPalClient;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use PayPalHttp\HttpException;

class CreatePayoutSample
{

  public static function buildRequestBody()
  {
    return json_decode(
      '{
                "sender_batch_header":
                {
                  "email_subject": "SDK payouts test txn"
                },
                "items": [
                {
                  "recipient_type": "EMAIL",
                  "receiver": "payouts2342@paypal.com",
                  "note": "Your 1$ payout",
                  "sender_item_id": "Test_txn_12",
                  "amount":
                  {
                    "currency": "USD",
                    "value": "1"
                  }
                }]
              }',
      true
    );
  }
  /**
   * This function can be used to create payout. 
   */
  public static function CreatePayout($debug = false)
  {
    try {
      $request = new PayoutsPostRequest();
      $request->body = self::buildRequestBody();
      $client = PayPalClient::client();
      $response = $client->execute($request);
      if ($debug) {
        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->batch_header->batch_status}\n";
        print "Batch ID: {$response->result->batch_header->payout_batch_id}\n";
        print "Links:\n";
        foreach ($response->result->links as $link) {
          print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }
        // To toggle printing the whole response body comment/uncomment below line
        echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
      }
      return $response;
    } catch (HttpException $e) {
      //Parse failure response
      echo $e->getMessage() . "\n";
      $error = json_decode($e->getMessage());
      echo $error->message . "\n";
      echo $error->name . "\n";
      echo $error->debug_id . "\n";
    }
  }
}

if (!count(debug_backtrace())) {
  CreatePayoutSample::CreatePayout(true);
}
