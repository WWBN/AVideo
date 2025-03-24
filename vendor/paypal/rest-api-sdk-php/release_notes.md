PayPal PHP SDK release notes
============================

v1.6.4
----
* SSL Connect Error Fix
* Fixes #474

v1.6.3
----
* Fixes Continue 100 Header
* Minor Bug Fixes #452

v1.6.2
----
* TLS Check Sample Added
* Updated README

v1.6.1
----
* User Agent Changes
* SDK Version Fix

v1.6.0
----
* Updated Payments API to latest version
* Removed ModelAccessValidator
* Minor Bug Fixes #399

v1.5.1
----
* Fixed a bug #343 in Future Payment
* Minor Improvements
* Updates to Sample Docs

v1.5.0
----
* Enabled Vault List API
* Added More Fields to Vault Credit Card Object
* Minor Fixes

v1.4.0
----
* Ability to validate Webhook
* Fixes to Logging Manager to skip if mode is not set
* SDK updates and fixes

v1.3.2
----
* Minor Fix for Agreement Details

v1.3.1
----
* PayPalModel to differentiate between empty objects and array
* Fixed CURLINFO_HEADER_SIZE miscalculations if Proxy Enabled

v1.3.0
----
* Updated Payment APIs
* Updating ModelAccessValidator to be disabled if not set explicitly

v1.2.1
----
* Ability to handle missing accesors for unknown objects in json

v1.2.0
----
* Order API Support
* Introduced DEBUG mode in Logging. Deprecated FINE.
* Ability to not Log on DEBUG, while on live environment
* Vault APIs Update API Support
* Transaction Fee Added in Sale Object
* Fixed #237, #234, #233, #215

v1.1.1
----
* Fix to Cipher Encryption (Critical)

v1.1.0
----
* Enabled Payouts Cancel API Support for Unclaimed Payouts
* Encrypting Access Token in Cached Storage
* Updated Billing Agreement Search Transaction code to pass start_date and end_date
* Updated OAuthToken to throw proper error on not receiving access token
* Minor Bug Fixes and Documentation Updates

v1.0.0
----
* Enabled Payouts API Support
* Authorization Cache Custom Path Directory Configuration
* Helper Functions to retrieve specific HATEOS Links
* Default Mode set to Sandbox
* Enabled Rest SDK to work nicely with Classic SDKs.
* If missing annotation of return type in Getters, it throws a proper exception
* `echo` on PayPalModel Objects will print nice looking JSON
* Updated Invoice Object to retrieve payments and refunds

> ## Breaking Changes
* Removed Deprecated Getter Setters from all Model Classes
  * All Camelcase getters and setters are removed. Please use first letter uppercase syntax
  * E.g. instead of using get_notify_url(), use getNotifyUrl() instead
* Renamed Classes
  * PayPal\Common\PPModel => PayPal\Common\PayPalModel
  * PayPal\Common\ResourceModel => PayPal\Common\PayPalResourceModel
  * PayPal\Common\PPUserAgent => PayPal\Common\PayPalUserAgent
  * PayPal\Core\PPConfigManager => PayPal\Core\PayPalConfigManager
  * PayPal\Core\PPConstants  => PayPal\Core\PayPalConstants
  * PayPal\Core\PPCredentialManager => PayPal\Core\PayPalCredentialManager
  * PayPal\Core\PPHttpConfig => PayPal\Core\PayPalHttpConfig
  * PayPal\Core\PPHttpConnection => PayPal\Core\PayPalHttpConnection
  * PayPal\Core\PPLoggingLevel => PayPal\Core\PayPalLoggingLevel
  * PayPal\Core\PPLoggingManager => PayPal\Core\PayPalLoggingManager
  * PayPal\Exception\PPConfigurationException => PayPal\Exception\PayPalConfigurationException
  * PayPal\Exception\PPConnectionException => PayPal\Exception\PayPalConnectionException
  * PayPal\Exception\PPInvalidCredentialException => PayPal\Exception\PayPalInvalidCredentialException
  * PayPal\Exception\PPMissingCredentialException => PayPal\Exception\PayPalMissingCredentialException
  * PayPal\Handler\IPPHandler => PayPal\Handler\IPayPalHandler
  * PayPal\Transport\PPRestCall => PayPal\Transport\PayPalRestCall
* Namespace Changes and Class Naming Convention
  * PayPal\Common\FormatConverter => PayPal\Converter\FormatConverter
  * PayPal\Rest\RestHandler => PayPal\Handler\RestHandler
  * PayPal\Rest\OauthHandler => PayPal\Handler\OauthHandler
* Fixes to Methods
  * PayPal\Api\Invoice->getPaymentDetails() was renamed to getPayments()
  * PayPal\Api\Invoice->getRefundDetails() was renamed to getRefunds()

v1.0.0-beta
----
* Namespace Changes and Class Naming Convention
* Helper Functions to retrieve specific HATEOS Links
* Default Mode set to Sandbox

v0.16.1
----
* Configurable Headers for all requests to PayPal
* Allows adding additional headers to every call to PayPal APIs
* SDK Config to add headers with http.headers.* syntax

v0.16.0
----
* Enabled Webhook Management Capabilities
* Enabled Caching Abilities for Access Tokens

v0.15.1
----
* Enabled Deleting Billing Plans
* Updated Samples

v0.15.0
----
* Extended Invoicing Capabilities
* Allows QR Code Generation for Invoices
* Updated Formatter to work with multiple locales
* Removed Future Payments mandate on Correlation Id

v0.14.2
----
* Quick Patch to Unset Cipher List for NSS

v0.14.1
----
* Updated HttpConfig to use TLSv1 as Cipher List
* Added resetRequestId in ApiContext to enable multiple create calls in succession
* Sanitize Input for Price Variables
* Made samples look better and work best

v0.14.0
----
* Enabled Billing Plans and Agreements APIs
* Renamed SDK name to PayPal-PHP-SDK

v0.13.2
----
* Updated Future Payments and LIPP Support
* Updated Logging Syntax

v0.13.1
----
* Enabled TLS version 1.x for SSL Negotiation
* Updated Identity Support from SDK Core
* Fixed Backward Compatibility changes

v0.13.0
----
* Enabled Payment Experience

v0.12.0
----
* Enabled EC Parameters Support for Payment APIs
* Enabled Validation for Missing Accessors

v0.11.1
----
* Removed Dependency from SDK Core Project
* Enabled Future Payments

v0.11.0
----
* Ability for PUT and PATCH requests
* Invoice number, custom and soft descriptor
* Order API and tests, more Authorization tests
* remove references to sdk-packages
* patch for retrieving paid invoices
* Shipping address docs patch
* Remove @array annotation
* Validate return cancel url
* type hinting, comment cleaning, and getters and setters for Shipping

v0.8.0
-----
* Invoicing API support added

v0.7.1
-----
* Added support for Reauthorization

v0.7.0
-----
* Added support for Auth and Capture APIs
* Types modified to match the API Spec
* Updated SDK to use namespace supported core library

v0.6.0
-----
* Adding support for dynamic configuration of SDK (Upgrading sdk-core-php dependency to V1.4.0)
* Deprecating the setCredential method and changing resource class methods to take an ApiContext argument instead of a OauthTokenCredential argument.

v0.5.0
-----
* Initial Release
