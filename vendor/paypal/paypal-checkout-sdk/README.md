# REST API SDK for PHP V2

![Home Image](homepage.jpg)

### To consolidate support across various channels, we have currently turned off the feature of GitHub issues. Please visit https://www.paypal.com/support to submit your request or ask questions within our community forum.

__Welcome to PayPal PHP SDK__. This repository contains PayPal's PHP SDK and samples for [v2/checkout/orders](https://developer.paypal.com/docs/api/orders/v2/) and [v2/payments](https://developer.paypal.com/docs/api/payments/v2/) APIs.

This is a part of the next major PayPal SDK. It includes a simplified interface to only provide simple model objects and blueprints for HTTP calls. This repo currently contains functionality for PayPal Checkout APIs which includes [Orders V2](https://developer.paypal.com/docs/api/orders/v2/) and [Payments V2](https://developer.paypal.com/docs/api/payments/v2/).

Please refer to the [PayPal Checkout Integration Guide](https://developer.paypal.com/docs/checkout/) for more information. Also refer to [Setup your SDK](https://developer.paypal.com/docs/checkout/reference/server-integration/setup-sdk/) for additional information about setting up the SDK's. 
## Latest Updates
Beginning January 2020, PayPal will require an update on the Personal Home Page (PHP) Checkout Software Developer Kit (SDK) to version 1.0.1. Merchants who have not updated their PHP Checkout SDK to version 1.0.1 will not be able to deserialize responses using outdated SDK integrations.
All PHP Checkout SDK integrations are expected to be updated by March 1, 2020. Merchants are encouraged to prepare for the update as soon as possible to avoid possible service disruption.
The Status Page has been updated with this information. The bulletin can be found [here](https://www.paypal-status.com/history/eventdetails/11015)

## Prerequisites

PHP 5.6 and above

An environment which supports TLS 1.2 (see the TLS-update site for more information)

## Usage

### Binaries

It is not mandatory to fork this repository for using the PayPal SDK. You can refer [PayPal Checkout Server SDK](https://developer.paypal.com/docs/checkout/reference/server-integration) for configuring and working with SDK without forking this code.

For contributing or referring the samples, You can fork/refer this repository. 

### Setting up credentials
Get client ID and client secret by going to https://developer.paypal.com/developer/applications and generating a REST API app. Get <b>Client ID</b> and <b>Secret</b> from there.

```php
require __DIR__ . '/vendor/autoload.php';
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
// Creating an environment
$clientId = "<<PAYPAL-CLIENT-ID>>";
$clientSecret = "<<PAYPAL-CLIENT-SECRET>>";

$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);
```

## Examples
### Creating an Order
#### Code:
```php
// Construct a request object and set desired parameters
// Here, OrdersCreateRequest() creates a POST request to /v2/checkout/orders
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
                     "intent" => "CAPTURE",
                     "purchase_units" => [[
                         "reference_id" => "test_ref_id1",
                         "amount" => [
                             "value" => "100.00",
                             "currency_code" => "USD"
                         ]
                     ]],
                     "application_context" => [
                          "cancel_url" => "https://example.com/cancel",
                          "return_url" => "https://example.com/return"
                     ] 
                 ];

try {
    // Call API with your client and get a response for your call
    $response = $client->execute($request);
    
    // If call returns body in response, you can get the deserialized version from the result attribute of the response
    print_r($response);
}catch (HttpException $ex) {
    echo $ex->statusCode;
    print_r($ex->getMessage());
}
```
#### Example Output:
```
Status Code: 201
Id: 8GB67279RC051624C
Intent: CAPTURE
Gross_amount:
	Currency_code: USD
	Value: 100.00
Purchase_units:
	1:
		Amount:
			Currency_code: USD
			Value: 100.00
Create_time: 2018-08-06T23:34:31Z
Links:
	1:
		Href: https://api.sandbox.paypal.com/v2/checkout/orders/8GB67279RC051624C
		Rel: self
		Method: GET
	2:
		Href: https://www.sandbox.paypal.com/checkoutnow?token=8GB67279RC051624C
		Rel: approve
		Method: GET
	3:
		Href: https://api.sandbox.paypal.com/v2/checkout/orders/8GB67279RC051624C/capture
		Rel: capture
		Method: POST
Status: CREATED
```

## Capturing an Order
Before capture, Order should be approved by the buyer using the approval URL returned in the create order response.
### Code to Execute:
```php
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
// Here, OrdersCaptureRequest() creates a POST request to /v2/checkout/orders
// $response->result->id gives the orderId of the order created above
$request = new OrdersCaptureRequest("APPROVED-ORDER-ID");
$request->prefer('return=representation');
try {
    // Call API with your client and get a response for your call
    $response = $client->execute($request);
    
    // If call returns body in response, you can get the deserialized version from the result attribute of the response
    print_r($response);
}catch (HttpException $ex) {
    echo $ex->statusCode;
    print_r($ex->getMessage());
}
```

#### Example Output:
```
Status Code: 201
Id: 8GB67279RC051624C
Create_time: 2018-08-06T23:39:11Z
Update_time: 2018-08-06T23:39:11Z
Payer:
	Name:
		Given_name: test
		Surname: buyer
	Email_address: test-buyer@paypal.com
	Payer_id: KWADC7LXRRWCE
	Phone:
		Phone_number:
			National_number: 408-411-2134
	Address:
		Country_code: US
Links:
	1:
		Href: https://api.sandbox.paypal.com/v2/checkout/orders/3L848818A2897925Y
		Rel: self
		Method: GET
Status: COMPLETED
```

## Running tests

To run integration tests using your client id and secret, clone this repository and run the following command:
```sh
$ composer install
$ CLIENT_ID=YOUR_SANDBOX_CLIENT_ID CLIENT_SECRET=OUR_SANDBOX_CLIENT_SECRET composer integration
```

## Samples

You can start off by trying out [creating and capturing an order](/samples/CaptureIntentExamples/RunAll.php)

To try out different samples for both create and authorize intent check [this link](/samples)

Note: Update the `PayPalClient.php` with your sandbox client credentials or pass your client credentials as environment variable while executing the samples.


## License
Code released under [SDK LICENSE](LICENSE)  
