# OneSignal API for PHP

[![Latest Stable Version](https://img.shields.io/packagist/v/norkunas/onesignal-php-api.svg?color=%23039be5)](https://packagist.org/packages/norkunas/onesignal-php-api)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/norkunas/onesignal-php-api.svg?color=%23039be5)](https://scrutinizer-ci.com/g/norkunas/onesignal-php-api)
[![Total Downloads](https://img.shields.io/packagist/dt/norkunas/onesignal-php-api.svg?color=%23039be5)](https://packagist.org/packages/norkunas/onesignal-php-api/stats)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/norkunas/onesignal-php-api/CI?color=%23039be5)
[![Software License](https://img.shields.io/github/license/norkunas/onesignal-php-api?color=%23039be5)](LICENSE)

## Install

Note: All examples are for v2, if you are using PHP <7.3 please read [v1 documentation](https://github.com/norkunas/onesignal-php-api/blob/1.0/README.md).

This packages requires a PSR-18 HTTP client and PSR-17 HTTP factories to work. You can choose any from
[psr/http-client-implementation](https://packagist.org/providers/psr/http-client-implementation)
and [psr/http-factory-implementation](https://packagist.org/providers/psr/http-factory-implementation)

Example with Symfony HttpClient and nyholm/psr7 http factories, install it with [Composer](https://getcomposer.org/):

```
composer require symfony/http-client nyholm/psr7 norkunas/onesignal-php-api
```

And now configure the OneSignal api client:

```php
<?php

declare(strict_types=1);

use OneSignal\Config;
use OneSignal\OneSignal;
use Symfony\Component\HttpClient\Psr18Client;
use Nyholm\Psr7\Factory\Psr17Factory;

require __DIR__ . '/vendor/autoload.php';

$config = new Config('your_application_id', 'your_application_auth_key', 'your_auth_key');
$httpClient = new Psr18Client();
$requestFactory = $streamFactory = new Psr17Factory();

$oneSignal = new OneSignal($config, $httpClient, $requestFactory, $streamFactory);
```

## How to use this library

### Applications API

View the details of all of your current OneSignal applications ([official documentation](https://documentation.onesignal.com/reference#view-apps-apps)):

```php
$myApps = $oneSignal->apps()->getAll();
```

View the details of a single OneSignal application ([official documentation](https://documentation.onesignal.com/reference#view-an-app)):

```php
$myApp = $oneSignal->apps()->getOne('application_id');
```

Create a new OneSignal app ([official documentation](https://documentation.onesignal.com/reference#create-an-app)):

```php
$newApp = $oneSignal->apps()->add([
    'name' => 'app name',
    'gcm_key' => 'key'
]);
```

Update the name or configuration settings of OneSignal application ([official documentation](https://documentation.onesignal.com/reference#update-an-app)):

```php
$oneSignal->apps()->update('application_id', [
    'name' => 'new app name'
]);
```

Create Segments ([official documentation](https://documentation.onesignal.com/reference#create-segments)):

```php
$oneSignal->apps()->createSegment('application_id', [
    'name' => 'Segment Name',
    'filters' => [
        ['field' => 'session_count', 'relation' => '>', 'value' => 1],
        ['operator' => 'AND'],
        ['field' => 'tag', 'relation' => '!=', 'key' => 'tag_key', 'value' => '1'],
        ['operator' => 'OR'],
        ['field' => 'last_session', 'relation' => '<', 'value' => '30,'],
    ],
]);
```

Delete Segments ([official documentation](https://documentation.onesignal.com/reference#delete-segments)):

```php
$oneSignal->apps()->deleteSegment('application_id', 'segment_id');
```

View the details of all the outcomes associated with your app ([official documentation](https://documentation.onesignal.com/reference/view-outcomes)):

```php
use OneSignal\Apps;
use OneSignal\Devices;

$outcomes = $oneSignal->apps()->outcomes('application_id', [
    'outcome_names' => [
        'os__session_duration.count',
        'os__click.count',
        'Sales, Purchase.sum',
    ],
    'outcome_time_range' => Apps::OUTCOME_TIME_RANGE_MONTH,
    'outcome_platforms' => [Devices::IOS, Devices::ANDROID],
    'outcome_attribution' => Apps::OUTCOME_ATTRIBUTION_DIRECT,
]);
```

### Devices API

View the details of multiple devices in one of your OneSignal apps ([official documentation](https://documentation.onesignal.com/reference#view-devices)):

```php
$devices = $oneSignal->devices()->getAll();
```

View the details of an existing device in your configured OneSignal application ([official documentation](https://documentation.onesignal.com/reference#view-device)):

```php
$device = $oneSignal->devices()->getOne('device_id');
```

Register a new device to your configured OneSignal application ([official documentation](https://documentation.onesignal.com/reference#add-a-device)):

```php
use OneSignal\Devices;

$newDevice = $oneSignal->devices()->add([
    'device_type' => Devices::ANDROID,
    'identifier' => 'abcdefghijklmn',
]);
```

Update an existing device in your configured OneSignal application ([official documentation](https://documentation.onesignal.com/reference#edit-device)):

```php
$oneSignal->devices()->update('device_id', [
    'session_count' => 2,
    'ip' => '127.0.0.1', // Optional. New IP Address of your device
]);
```

Update an existing device's tags in one of your OneSignal apps using the External User ID ([official documentation](https://documentation.onesignal.com/reference/edit-tags-with-external-user-id)):

```php
$externalUserId = '12345';
$response = $oneSignal->devices()->editTags($externalUserId, [
    'tags' => [
        'a' => '1',
        'foo' => '',
    ],
]);
```

### Notifications API

View the details of multiple notifications ([official documentation](https://documentation.onesignal.com/reference#view-notifications)):

```php
$notifications = $oneSignal->notifications()->getAll();
```

Get the details of a single notification ([official documentation](https://documentation.onesignal.com/reference#view-notification)):

```php
$notification = $oneSignal->notifications()->getOne('notification_id');
```

Create and send notifications or emails to a segment or individual users.
You may target users in one of three ways using this method: by Segment, by
Filter, or by Device (at least one targeting parameter must be specified) ([official documentation](https://documentation.onesignal.com/reference#create-notification)):

```php
$oneSignal->notifications()->add([
    'contents' => [
        'en' => 'Notification message'
    ],
    'included_segments' => ['All'],
    'data' => ['foo' => 'bar'],
    'isChrome' => true,
    'send_after' => new \DateTime('1 hour'),
    'filters' => [
        [
            'field' => 'tag',
            'key' => 'is_vip',
            'relation' => '!=',
            'value' => 'true',
        ],
        [
            'operator' => 'OR',
        ],
        [
            'field' => 'tag',
            'key' => 'is_admin',
            'relation' => '=',
            'value' => 'true',
        ],
    ],
    // ..other options
]);
```

Mark notification as opened ([official documentation](https://documentation.onesignal.com/reference#track-open)):

```php
$oneSignal->notifications()->open('notification_id');
```

Stop a scheduled or currently outgoing notification ([official documentation](https://documentation.onesignal.com/reference#cancel-notification)):

```php
$oneSignal->notifications()->cancel('notification_id');
```

Notification History ([official documentation](https://documentation.onesignal.com/reference#notification-history)):

```php
$oneSignal->notifications()->history('notification_id', [
    'events' => 'clicked', // or 'sent'
    'email' => 'your_email@email.com',
]);
```

## Questions?

If you have any questions please [open a discussion](https://github.com/norkunas/onesignal-php-api/discussions/new).

## License

This library is released under the MIT License. See the bundled [LICENSE](https://github.com/norkunas/onesignal-php-api/blob/master/LICENSE) file for details.
