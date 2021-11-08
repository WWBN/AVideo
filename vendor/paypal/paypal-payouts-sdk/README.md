# PayPal Payouts PHP SDK v2

![Home Image](homepage.jpg)

__Welcome to PayPal Payouts__. This repository contains PayPal's PHP SDK for Payouts and samples for [v1/payments/payouts](https://developer.paypal.com/docs/api/payments.payouts-batch/v1/) APIs.

This is a part of the next major PayPal SDK. It includes a simplified interface to only provide simple model objects and blueprints for HTTP calls. This repo currently contains functionality for PayPal Payouts APIs which includes [Payouts](https://developer.paypal.com/docs/api/payments.payouts-batch/v1/).

Please refer to the [PayPal Payouts Integration Guide](https://developer.paypal.com/docs/payouts/) for more information. Also refer to [Setup your SDK](https://developer.paypal.com/docs/payouts/reference/setup-sdk) for additional information about setting up the SDK's. 

## Prerequisites

PHP 5.6 and above

An environment which supports TLS 1.2 (see the TLS-update site for more information)

## Usage
### Binaries

It is not necessary to fork this repository for using the PayPal SDK. Please take a look at [PayPal Payouts Server SDK](https://developer.paypal.com/docs/payouts/reference/setup-sdk/#install-the-sdk) for configuring and working with SDK without forking this code.

For contributing to this repository or using the samples you can fork this repository.

### Setting up credentials

Get client ID and client secret by going to https://developer.paypal.com/developer/applications and generating a REST API app. Get <b>Client ID</b> and <b>Secret</b> from there.

```PHP
require __DIR__ . '/vendor/autoload.php';
use PaypalPayoutsSDK\Core\PayPalHttpClient;
use PaypalPayoutsSDK\Core\SandboxEnvironment;
// Creating an environment
$clientId = "<<PAYPAL-CLIENT-ID>>";
$clientSecret = "<<PAYPAL-CLIENT-SECRET>>";

$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);
```

## Examples
### Creating a Payout
This will create a Payout and print batch id for the created Payouts

```PHP
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
$request = new PayoutsPostRequest();
$body= json_decode(
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
                    "value": "1.00"
                  }
                }]
              }',             
            true);
$request->body = $body;
$client = PayPalClient::client();
$response = $client->execute($request);
print "Status Code: {$response->statusCode}\n";
print "Status: {$response->result->batch_header->batch_status}\n";
print "Batch ID: {$response->result->batch_header->payout_batch_id}\n";
print "Links:\n";
foreach($response->result->links as $link)
 {
   print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
 }
echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        
```

### Retrieve a Payouts Batch
This will retrieve a payouts batch
```PHP
 $request = new PayoutsGetRequest($batchId);
 $response = $client->execute($request);
 echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
```

### Parsing Failure Response
This will execute a Get request to simulate a failure
```PHP
 try{
    $request = new PayoutsGetRequest(null);
    $response = $client->execute($request);
    echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
  } catch(HttpException $e){
    echo $e->getMessage()
    var_dump(json_decode($e->getMessage()));

  }


```
## Running tests

To run integration tests using your client id and secret, clone this repository and run the following command:

```sh
$ composer install
$ CLIENT_ID=YOUR_SANDBOX_CLIENT_ID CLIENT_SECRET=OUR_SANDBOX_CLIENT_SECRET composer unit
```

You may use the client id and secret above for demonstration purposes.


## Samples

You can start off by trying out [Samples](/samples).

Note: Update the `PayPalClient.php` with your sandbox client credentials or pass your client credentials as environment variable while executing the samples.

## License
Code released under [SDK LICENSE](LICENSE)
