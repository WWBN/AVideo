PayPal PHP SDK release notes
============================

1.13.0
-----
* Add HUF as a non-decimal currency [#974](https://github.com/paypal/PayPal-PHP-SDK/pull/974).
* Add `purchaseOrder` in `CartBase` [#939](https://github.com/paypal/PayPal-PHP-SDK/pull/939).
* Fixed annotation bug [#872](https://github.com/paypal/PayPal-PHP-SDK/pull/872).
* Update PHPUnit [#979](https://github.com/paypal/PayPal-PHP-SDK/pull/979).

1.12.0
-----
* Add `getToken` method to `Payment` class to retrieve EC token from approval URL.
* Add TLSv1.2 to cipher list [#844](https://github.com/paypal/PayPal-PHP-SDK/pull/844).
* Use restCall object for function that makes REST requests [#841](https://github.com/paypal/PayPal-PHP-SDK/pull/841).
* Minor bugfixes [#766](https://github.com/paypal/PayPal-PHP-SDK/issues/766), [#798](https://github.com/paypal/PayPal-PHP-SDK/issues/798), [#845](https://github.com/paypal/PayPal-PHP-SDK/pull/845).
* Updated samples.

1.11.0
-----
* Update third party payment sample with PayPal payment.
* Prevent error in SSL version check if curl is not available [#706](https://github.com/paypal/PayPal-PHP-SDK/pull/706).
* Stop auto-generating PayPal-Request-Id header values and allow SDK users to optionally set the value [#747](https://github.com/paypal/PayPal-PHP-SDK/pull/747).
* Remove automatic retries on failed requests [#747](https://github.com/paypal/PayPal-PHP-SDK/pull/747).

1.10.0
-----
* Updated Payments APIs [#700](https://github.com/paypal/PayPal-PHP-SDK/pull/700).
* Minor bug fixes.

1.9.0
-----
* Updated Payouts APIs [#692](https://github.com/paypal/PayPal-PHP-SDK/pull/692).
* Updated Payment Experience APIs [#682](https://github.com/paypal/PayPal-PHP-SDK/pull/682).
* Updated Payments API to use Payment Card instead of credit card [#696](https://github.com/paypal/PayPal-PHP-SDK/pull/696).
* Fixed bug on failed Access token call. [#665](https://github.com/paypal/PayPal-PHP-SDK/pull/665).

1.8.0
-----
* Updated Webhooks APIs [#653](https://github.com/paypal/PayPal-PHP-SDK/pull/653).
* Updated Invoicing APIs [#657](https://github.com/paypal/PayPal-PHP-SDK/pull/657).
* UTF-8 encoding bug fix [#655](https://github.com/paypal/PayPal-PHP-SDK/pull/655).
* Updated PSR log [#654](https://github.com/paypal/PayPal-PHP-SDK/pull/654).

1.7.4
-----
*  Fixed Duplicate conditional expression in PayPalCredentialManager.php [#594](https://github.com/paypal/PayPal-PHP-SDK/pull/594).
*  Updated Invoicing APIs [#605](https://github.com/paypal/PayPal-PHP-SDK/pull/605).
*  Fixed PSR code style errors [#607](https://github.com/paypal/PayPal-PHP-SDK/pull/607).

1.7.3
-----
* Enabled Third Party Invoicing [#581](https://github.com/paypal/PayPal-PHP-SDK/pull/581).

1.7.2
----
* Vault API updates.
* Fixes #575.

1.7.1
----
* Fixes #559.

1.7.0
----
* Enable custom logger injection.
* Minor bug fixes.

1.6.4
----
* SSL Connect Error Fix.
* Fixes #474.

1.6.3
----
* Fixes Continue 100 Header.
* Minor Bug Fixes #452.

1.6.2
----
* TLS Check Sample Added.
* Updated README.

1.6.1
----
* User Agent Changes.
* SDK Version Fix.

1.6.0
----
* Updated Payments API to latest version.
* Removed ModelAccessValidator.
* Minor Bug Fixes #399.

1.5.1
----
* Fixed a bug #343 in Future Payment.
* Minor Improvements.
* Updates to Sample Docs.

1.5.0
----
* Enabled Vault List API.
* Added More Fields to Vault Credit Card Object.
* Minor Fixes.

1.4.0
----
* Ability to validate Webhook.
* Fixes to Logging Manager to skip if mode is not set.
* SDK updates and fixes.

1.3.2
----
* Minor Fix for Agreement Details.

1.3.1
----
* PayPalModel to differentiate between empty objects and array.
* Fixed CURLINFO_HEADER_SIZE miscalculations if Proxy Enabled.

1.3.0
----
* Updated Payment APIs.
* Updating ModelAccessValidator to be disabled if not set explicitly.

1.2.1
----
* Ability to handle missing accessors for unknown objects in json.

1.2.0
----
* Order API Support.
* Introduced DEBUG mode in Logging. Deprecated FINE.
* Ability to not Log on DEBUG, while on live environment.
* Vault APIs Update API Support.
* Transaction Fee Added in Sale Object.
* Fixed #237, #234, #233, #215.

1.1.1
----
* Fix to Cipher Encryption (Critical).

1.1.0
----
* Enabled Payouts Cancel API Support for Unclaimed Payouts.
* Encrypting Access Token in Cached Storage.
* Updated Billing Agreement Search Transaction code to pass start_date and end_date.
* Updated OAuthToken to throw proper error on not receiving access token.
* Minor Bug Fixes and Documentation Updates.

1.0.0
----
* Enabled Payouts API Support.
* Authorization Cache Custom Path Directory Configuration.
* Helper Functions to retrieve specific HATEOS Links.
* Default Mode set to Sandbox.
* Enabled Rest SDK to work nicely with Classic SDKs.
* If missing annotation of return type in Getters, it throws a proper exception.
* `echo` on PayPalModel Objects will print nice looking JSON.
* Updated Invoice Object to retrieve payments and refunds.

> ## Breaking Changes
* Removed Deprecated Getter Setters from all Model Classes.
  * All Camelcase getters and setters are removed. Please use first letter uppercase syntax.
  * E.g. instead of using get_notify_url(), use getNotifyUrl() instead.
* Renamed Classes.
  * PayPal\Common\PPModel => PayPal\Common\PayPalModel.
  * PayPal\Common\ResourceModel => PayPal\Common\PayPalResourceModel.
  * PayPal\Common\PPUserAgent => PayPal\Common\PayPalUserAgent.
  * PayPal\Core\PPConfigManager => PayPal\Core\PayPalConfigManager.
  * PayPal\Core\PPConstants  => PayPal\Core\PayPalConstants.
  * PayPal\Core\PPCredentialManager => PayPal\Core\PayPalCredentialManager.
  * PayPal\Core\PPHttpConfig => PayPal\Core\PayPalHttpConfig.
  * PayPal\Core\PPHttpConnection => PayPal\Core\PayPalHttpConnection.
  * PayPal\Core\PPLoggingLevel => PayPal\Core\PayPalLoggingLevel.
  * PayPal\Core\PPLoggingManager => PayPal\Core\PayPalLoggingManager.
  * PayPal\Exception\PPConfigurationException => PayPal\Exception\PayPalConfigurationException.
  * PayPal\Exception\PPConnectionException => PayPal\Exception\PayPalConnectionException.
  * PayPal\Exception\PPInvalidCredentialException => PayPal\Exception\PayPalInvalidCredentialException.
  * PayPal\Exception\PPMissingCredentialException => PayPal\Exception\PayPalMissingCredentialException.
  * PayPal\Handler\IPPHandler => PayPal\Handler\IPayPalHandler.
  * PayPal\Transport\PPRestCall => PayPal\Transport\PayPalRestCall.
* Namespace Changes and Class Naming Convention.
  * PayPal\Common\FormatConverter => PayPal\Converter\FormatConverter.
  * PayPal\Rest\RestHandler => PayPal\Handler\RestHandler.
  * PayPal\Rest\OauthHandler => PayPal\Handler\OauthHandler.
* Fixes to Methods.
  * PayPal\Api\Invoice->getPaymentDetails() was renamed to getPayments().
  * PayPal\Api\Invoice->getRefundDetails() was renamed to getRefunds().

1.0.0-beta
----
* Namespace Changes and Class Naming Convention.
* Helper Functions to retrieve specific HATEOS Links.
* Default Mode set to Sandbox.

0.16.1
----
* Configurable Headers for all requests to PayPal.
* Allows adding additional headers to every call to PayPal APIs.
* SDK Config to add headers with http.headers.* syntax.

0.16.0
----
* Enabled Webhook Management Capabilities.
* Enabled Caching Abilities for Access Tokens.

0.15.1
----
* Enabled Deleting Billing Plans.
* Updated Samples.

0.15.0
----
* Extended Invoicing Capabilities.
* Allows QR Code Generation for Invoices.
* Updated Formatter to work with multiple locales.
* Removed Future Payments mandate on Correlation Id.

0.14.2
----
* Quick Patch to Unset Cipher List for NSS.

0.14.1
----
* Updated HttpConfig to use TLSv1 as Cipher List.
* Added resetRequestId in ApiContext to enable multiple create calls in succession.
* Sanitize Input for Price Variables.
* Made samples look better and work best.

0.14.0
----
* Enabled Billing Plans and Agreements APIs.
* Renamed SDK name to PayPal-PHP-SDK.

0.13.2
----
* Updated Future Payments and LIPP Support.
* Updated Logging Syntax.

0.13.1
----
* Enabled TLS version 1.x for SSL Negotiation.
* Updated Identity Support from SDK Core.
* Fixed Backward Compatibility changes.

0.13.0
----
* Enabled Payment Experience.

0.12.0
----
* Enabled EC Parameters Support for Payment APIs.
* Enabled Validation for Missing Accessors.

0.11.1
----
* Removed Dependency from SDK Core Project.
* Enabled Future Payments.

0.11.0
----
* Ability for PUT and PATCH requests.
* Invoice number, custom and soft descriptor.
* Order API and tests, more Authorization tests.
* remove references to sdk-packages.
* patch for retrieving paid invoices.
* Shipping address docs patch.
* Remove @array annotation.
* Validate return cancel url.
* type hinting, comment cleaning, and getters and setters for Shipping.

0.10.0
-----
* N/A.

0.9.0
-----
* N/A.

0.8.0
-----
* Invoicing API support added.

0.7.1
-----
* Added support for Reauthorization.

0.7.0
-----
* Added support for Auth and Capture APIs.
* Types modified to match the API Spec.
* Updated SDK to use namespace supported core library.

0.6.0
-----
* Adding support for dynamic configuration of SDK (Upgrading sdk-core-php dependency to V1.4.0).
* Deprecating the setCredential method and changing resource class methods to take an ApiContext argument instead of a OauthTokenCredential argument.

0.5.0
-----
* Initial Release.
