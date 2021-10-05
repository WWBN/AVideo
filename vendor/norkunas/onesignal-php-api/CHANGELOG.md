# Changelog

## Version 2.7.0

- Additional SMS related fields (on the Notifications payload)

## Version 2.6.0

- Add ability to update device IP address
- add "name" to Notifications payload

## Version 2.5.0

- Implement 'Edit tags with external user id' API endpoint
- Implement View Outcomes endpoint

## Version 2.4.0

- Add new device types

## Version 2.3.0

- Add app_url option in NotificationResolver

## Version 2.2.0

- Add channel_for_external_user_ids option in NotificationResolver

## Version 2.1.1

- Allow to install on php 8.0

## Version 2.1.0

- Add "kind" argument to notifications getAll method

## Version 2.0.3

- Add field "include_email_tokens"

## Version 2.0.2

- Add field "apns_push_type_override"

## Version 2.0.1

- Add missed chrome_web_badge option to NotificationResolver
- Add create/update segments and notification history examples to readme

## Version 2.0.0

- At least PHP 7.3 version is now required.
- `OneSignal\OneSignal` client now requires always to provide `OneSignal\Config`.
- `OneSignal\OneSignal` client now expects `Psr\Http\Client\ClientInterface` as a second arguments instead of `Http\Client\Common\HttpMethodsClient` and is mandatory.
- `OneSignal\OneSignal` client now requires always to provide `Psr\Http\Message\RequestFactoryInterface` as a third argument and `Psr\Http\Message\StreamFactoryInterface` as a fourth argument.
- Replaced magic __get method with __call in `OneSignal\OneSignal`, so from now calls like
`$oneSignal->apps` must be used as `$oneSignal->apps()`. It is better to use Dependency injection, because these calls will construct new instances.
- Removed `OneSignal\Exception\OneSignalException` and added `OneSignal\Exception\OneSignalExceptionInterface`.
- Removed `setConfig` and `setClient` methods in `OneSignal\OneSignal`. You can build new instances with different configs or http clients.
