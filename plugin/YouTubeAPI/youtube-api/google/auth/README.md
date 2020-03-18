# Google Auth Library for PHP

<dl>
  <dt>Homepage</dt><dd><a href="http://www.github.com/google/google-auth-library-php">http://www.github.com/google/google-auth-library-php</a></dd>
  <dt>Authors</dt>
    <dd><a href="mailto:temiola@google.com">Tim Emiola</a></dd>
    <dd><a href="mailto:stanleycheung@google.com">Stanley Cheung</a></dd>
    <dd><a href="mailto:betterbrent@google.com">Brent Shaffer</a></dd>
  <dt>Copyright</dt><dd>Copyright © 2015 Google, Inc.</dd>
  <dt>License</dt><dd>Apache 2.0</dd>
</dl>

## Description

This is Google's officially supported PHP client library for using OAuth 2.0
authorization and authentication with Google APIs.

### Installing via Composer

The recommended way to install the google auth library is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version:

```bash
composer.phar require google/auth
```

## Application Default Credentials

This library provides an implementation of
[application default credentials][application default credentials] for PHP.

The Application Default Credentials provide a simple way to get authorization
credentials for use in calling Google APIs.

They are best suited for cases when the call needs to have the same identity
and authorization level for the application independent of the user. This is
the recommended approach to authorize calls to Cloud APIs, particularly when
you're building an application that uses Google Compute Engine.

#### Download your Service Account Credentials JSON file

To use `Application Default Credentials`, You first need to download a set of
JSON credentials for your project. Go to **APIs & Auth** > **Credentials** in
the [Google Developers Console](developer console) and select
**Service account** from the **Add credentials** dropdown.

> This file is your *only copy* of these credentials. It should never be
> committed with your source code, and should be stored securely.

Once downloaded, store the path to this file in the
`GOOGLE_APPLICATION_CREDENTIALS` environment variable.

```php
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');
```

> PHP's `putenv` function is just one way to set an environment variable.
> Consider using `.htaccess` or apache configuration files as well.

#### Enable the API you want to use

Before making your API call, you must be sure the API you're calling has been
enabled. Go to **APIs & Auth** > **APIs** in the
[Google Developers Console](developer console) and enable the APIs you'd like to
call. For the example below, you must enable the `Drive API`.

#### Call the APIs

As long as you update the environment variable below to point to *your* JSON
credentials file, the following code should output a list of your Drive files.

```php
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

// specify the path to your application credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');

// define the scopes for your API call
$scopes = ['https://www.googleapis.com/auth/drive.readonly'];

// create middleware
$middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
$stack = HandlerStack::create();
$stack->push($middleware);

// create the HTTP client
$client = new Client([
  'handler' => $stack,
  'base_url' => 'https://www.googleapis.com',
  'auth' => 'google_auth'  // authorize all requests
]);

// make the request
$response = $client->get('drive/v2/files');

// show the result!
print_r((string) $response->getBody());
```

## What about auth in google-apis-php-client?

The goal is for auth done by
[google-apis-php-client][google-apis-php-client] to be be performed
by this library.

Eventually, google-apis-php-client should have a dependency on this library.
At the moment, there is no ETA for this, a key prequisite being for google-apis-php-client
itself take a dependency on [Guzzle][Guzzle] so that it can use the Guzzle
subscribers that this package provides. That's currently [being discussed](http://github.com/google/google-api-php-client#473).
This package's availability should make that transition simpler as there is one
less thing that need to be handled.

## License

This library is licensed under Apache 2.0. Full license text is
available in [COPYING][copying].

## Contributing

See [CONTRIBUTING][contributing].

## Support

Please
[report bugs at the project on Github](https://github.com/google/google-auth-library-php/issues). Don't
hesitate to
[ask questions](http://stackoverflow.com/questions/tagged/google-auth-library-php)
about the client or APIs on [StackOverflow](http://stackoverflow.com).

[google-apis-php-client]: https://github.com/google/google-api-php-client
[application default credentials]: https://developers.google.com/accounts/docs/application-default-credentials
[contributing]: https://github.com/google/google-auth-library-php/tree/master/CONTRIBUTING.md
[copying]: https://github.com/google/google-auth-library-php/tree/master/COPYING
[Guzzle]: https://github.com/guzzle/guzzle
[developer console]: https://console.developers.google.com
