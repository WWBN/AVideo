# razorpay-php

[![Build Status](https://travis-ci.org/razorpay/razorpay-php.svg?branch=master)](https://travis-ci.org/razorpay/razorpay-php) [![Latest Stable Version](https://poser.pugx.org/razorpay/razorpay/v/stable.svg)](https://packagist.org/packages/razorpay/razorpay) [![License](https://poser.pugx.org/razorpay/razorpay/license.svg)](https://packagist.org/packages/razorpay/razorpay)

Razorpay client PHP API. The Api follows the following practices:

-   Namespaced under `Razorpay\Api`
-   Call `$api->class->function()` to access the API
-   API throws exceptions instead of returning errors
-   Options are passed as an array instead of multiple arguments wherever possible
-   All requests and responses are communicated over JSON
-   A minimum of PHP 5.3 is required

# Installation

-   If your project uses composer, run the below command

```
composer require razorpay/razorpay:2.*
```

-   If you are not using composer, download the latest release from [the releases section](https://github.com/razorpay/razorpay-php/releases).
    **You should download the `razorpay-php.zip` file**.
    After that, include `Razorpay.php` in your application and you can use the API as usual.

# Usage

```php
use Razorpay\Api\Api;

$api = new Api($api_key, $api_secret);

// Orders
$order  = $api->order->create(array('receipt' => '123', 'amount' => 100, 'currency' => 'INR')); // Creates order
$order  = $api->order->fetch($orderId); // Returns a particular order
$orders = $api->order->all($options); // Returns array of order objects
$payments = $api->order->fetch($orderId)->payments(); // Returns array of payment objects against an order

// Payments
$payments = $api->payment->all($options); // Returns array of payment objects
$payment  = $api->payment->fetch($id); // Returns a particular payment
$payment  = $api->payment->fetch($id)->capture(array('amount'=>$amount)); // Captures a payment

// To get the payment details
echo $payment->amount;
echo $payment->currency;
// And so on for other attributes

// Refunds
$refund = $api->refund->create(array('payment_id' => $id)); // Creates refund for a payment
$refund = $api->refund->create(array('payment_id' => $id, 'amount'=>$refundAmount)); // Creates partial refund for a payment
$refund = $api->refund->fetch($refundId); // Returns a particular refund

// Cards
$card = $api->card->fetch($cardId); // Returns a particular card

// Customers
$customer = $api->customer->create(array('name' => 'Razorpay User', 'email' => 'customer@razorpay.com')); // Creates customer
$customer = $api->customer->fetch($customerId); // Returns a particular customer
$customer = $api->customer->edit(array('name' => 'Razorpay User', 'email' => 'customer@razorpay.com')); // Edits customer

// Tokens
$token  = $api->customer->token()->fetch($tokenId); // Returns a particular token
$tokens = $api->customer->token()->all($options); // Returns array of token objects
$api->customer->token()->delete($tokenId); // Deletes a token


// Transfers
$transfer  = $api->payment->fetch($paymentId)->transfer(array('transfers' => [ ['account' => $accountId, 'amount' => 100, 'currency' => 'INR']])); // Create transfer
$transfers = $api->transfer->all(); // Fetch all transfers
$transfers = $api->payment->fetch($paymentId)->transfers(); // Fetch all transfers created on a payment
$transfer  = $api->transfer->fetch($transferId)->edit($options); // Edit a transfer
$reversal  = $api->transfer->fetch($transferId)->reverse(); // Reverse a transfer

// Payment Links
$links = $api->invoice->all();
$link  = $api->invoice->fetch('inv_00000000000001');
$link  = $api->invoice->create(arary('type' => 'link', 'amount' => 500, 'description' => 'For XYZ purpose', 'customer' => array('email' => 'test@test.test')));
$link->cancel();
$link->notifyBy('sms');

// Invoices
$invoices = $api->invoice->all();
$invoice  = $api->invoice->fetch('inv_00000000000001');
$invoice  = $api->invoice->create($params); // Ref: razorpay.com/docs/invoices for request params example
$invoice  = $invoice->edit($params);
$invoice->issue();
$invoice->notifyBy('email');
$invoice->cancel();
$invoice->delete();

// Virtual Accounts
$virtualAccount  = $api->virtualAccount->create(array('receiver_types' => array('bank_account'), 'description' => 'First Virtual Account', 'notes' => array('receiver_key' => 'receiver_value')));
$virtualAccounts = $api->virtualAccount->all();
$virtualAccount  = $api->virtualAccount->fetch('va_4xbQrmEoA5WJ0G');
$virtualAccount  = $virtualAccount->close();
$payments        = $virtualAccount->payments();
$bankTransfer    = $api->payment->fetch('pay_8JpVEWsoNPKdQh')->bankTransfer();

// Bharat QR
$bharatQR = $api->virtualAccount->create(array('receivers' => array('types' => array('qr_code')), 'description' => 'First QR code', 'amount_expected' => 100, 'notes' => array('receiver_key' => 'receiver_value'))); // Create Static QR
$bharatQR = $api->virtualAccount->create(array('receivers' => array('types' => array('qr_code')), 'description' => 'First QR code', 'notes' => array('receiver_key' => 'receiver_value'))); // Create Dynamic QR

// Subscriptions
$plan          = $api->plan->create(array('period' => 'weekly', 'interval' => 1, 'item' => array('name' => 'Test Weekly 1 plan', 'description' => 'Description for the weekly 1 plan', 'amount' => 600, 'currency' => 'INR')));
$plan          = $api->plan->fetch('plan_7wAosPWtrkhqZw');
$plans         = $api->plan->all();
$subscription  = $api->subscription->create(array('plan_id' => 'plan_7wAosPWtrkhqZw', 'customer_notify' => 1, 'total_count' => 6, 'start_at' => 1495995837, 'addons' => array(array('item' => array('name' => 'Delivery charges', 'amount' => 30000, 'currency' => 'INR')))));
$subscription  = $api->subscription->fetch('sub_82uBGfpFK47AlA');
$subscriptions = $api->subscription->all();
$subscription  = $api->subscription->fetch('sub_82uBGfpFK47AlA')->cancel($options); //$options = ['cancel_at_cycle_end' => 1];
$addon         = $api->subscription->fetch('sub_82uBGfpFK47AlA')->createAddon(array('item' => array('name' => 'Extra Chair', 'amount' => 30000, 'currency' => 'INR'), 'quantity' => 2));
$addon         = $api->addon->fetch('ao_8nDvQYYGQI5o4H');
$addon         = $api->addon->fetch('ao_8nDvQYYGQI5o4H')->delete();

// Settlements
$settlement    = $api->settlement->fetch('setl_7IZKKI4Pnt2kEe');
$settlements   = $api->settlement->all();
$reports       = $api->settlement->reports(array('year' => 2018, 'month' => 2));
```

For further help, see our documentation on <https://docs.razorpay.com>.

[composer-install]: https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx

## Developing

See the [doc.md](doc.md) file for getting started with development.

## License

The Razorpay PHP SDK is released under the MIT License. See [LICENSE](LICENSE) file for more details.

## Release

Steps to follow for a release:

0.  Merge the branch with the new code to master.
1.  Bump the Version in `src/Api.php`.
1.  Rename Unreleased to the new tag in `CHANGELOG.md`
1.  Add a new empty "Unreleased" section at the top of `CHANGELOG.md`
1.  Fix links at bottom in `CHANGELOG.md`
1.  Commit
1.  Tag the release and push to GitHub
1.  A release should automatically be created once the travis build passes. Edit the release to add some description.
