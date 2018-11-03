# REST API SDK for PHP

![Home Image](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/homepage.jpg)

[![Build Status](https://travis-ci.org/paypal/PayPal-PHP-SDK.svg?branch=master)](https://travis-ci.org/paypal/PayPal-PHP-SDK)
[![Coverage Status](https://coveralls.io/repos/paypal/PayPal-PHP-SDK/badge.svg?branch=master)](https://coveralls.io/r/paypal/PayPal-PHP-SDK?branch=master)

__Welcome to PayPal PHP SDK__. This repository contains PayPal's PHP SDK and samples for REST API.

## Please Note
> **The Payment Card Industry (PCI) Council has [mandated](https://blog.pcisecuritystandards.org/migrating-from-ssl-and-early-tls) that early versions of TLS be retired from service.  All organizations that handle credit card information are required to comply with this standard. As part of this obligation, PayPal is updating its services to require TLS 1.2 for all HTTPS connections. At this time, PayPal will also require HTTP/1.1 for all connections. [Click here](https://github.com/paypal/tls-update) for more information**

> **Connections to the sandbox environment use only TLS 1.2.**

## SDK Documentation

[Our PayPal-PHP-SDK Page](http://paypal.github.io/PayPal-PHP-SDK/) includes all the documentation related to PHP SDK. Everything from SDK Wiki, to Sample Codes, to Releases. Here are few quick links to get you there faster.

* [PayPal-PHP-SDK Home Page](https://paypal.github.io/PayPal-PHP-SDK/)
* [Wiki](https://github.com/paypal/PayPal-PHP-SDK/wiki)
* [Samples](https://paypal.github.io/PayPal-PHP-SDK/sample/)
* [Installation](https://github.com/paypal/PayPal-PHP-SDK/wiki/Installation)
* [Make your First SDK Call](https://github.com/paypal/PayPal-PHP-SDK/wiki/Making-First-Call)
* [PayPal Developer Docs](https://developer.paypal.com/docs/)

## Latest Updates

- SDK now allows injecting your logger implementation. Please read [documentation](https://github.com/paypal/PayPal-PHP-SDK/wiki/Custom-Logger) for more details.
- If you are running into SSL Connect Error talking to sandbox or live, please update your SDK to latest version or, follow instructions as shown [here](https://github.com/paypal/PayPal-PHP-SDK/issues/474)
- Checkout the latest 1.0.0 release. Here are all the [breaking Changes in v1.0.0](https://github.com/paypal/PayPal-PHP-SDK/wiki/Breaking-Changes---1.0.0) if you are migrating from older versions.
- Now we have a [Github Page](https://paypal.github.io/PayPal-PHP-SDK/), that helps you find all helpful resources building applications using PayPal-PHP-SDK.


## Prerequisites

   - PHP 5.3 or above
   - [curl](https://secure.php.net/manual/en/book.curl.php), [json](https://secure.php.net/manual/en/book.json.php) & [openssl](https://secure.php.net/manual/en/book.openssl.php) extensions must be enabled


## Direct Credit Card Support
[Braintree Direct](https://www.braintreepayments.com/products/braintree-direct) is PayPal's preferred integration solution for accepting direct credit card payments in your mobile app or website. Braintree, a PayPal service, is the easiest way to accept credit cards, PayPal, and many other payment methods.


## License

Read [License](LICENSE) for more licensing information.

## Contributing

Read [here](CONTRIBUTING.md) for more information.

## More help
   * [Going Live](https://github.com/paypal/PayPal-PHP-SDK/wiki/Going-Live)
   * [PayPal-PHP-SDK Home Page](http://paypal.github.io/PayPal-PHP-SDK/)
   * [SDK Documentation](https://github.com/paypal/PayPal-PHP-SDK/wiki)
   * [Sample Source Code](http://paypal.github.io/PayPal-PHP-SDK/sample/)
   * [API Reference](https://developer.paypal.com/webapps/developer/docs/api/)
   * [Reporting Issues / Feature Requests](https://github.com/paypal/PayPal-PHP-SDK/issues)
