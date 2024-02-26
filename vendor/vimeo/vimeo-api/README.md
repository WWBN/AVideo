# <img src="https://user-images.githubusercontent.com/33762/33720344-abc20bb8-db31-11e7-8362-59a4985aeff0.png" width="250" />

[![Packagist](https://img.shields.io/packagist/v/vimeo/vimeo-api.svg?style=flat-square)](https://packagist.org/packages/vimeo/vimeo-api)
[![License](https://img.shields.io/packagist/l/vimeo/vimeo-api.svg?style=flat-square)](https://packagist.org/packages/vimeo/vimeo-api)
[![Travis CI](https://img.shields.io/travis/vimeo/vimeo.php.svg?style=flat-square)](https://travis-ci.org/vimeo/vimeo.php)
[![StyleCI](https://styleci.io/repos/9654006/shield?style=flat-square)](https://styleci.io/repos/9654006/)

This is a simple PHP library for interacting with the [Vimeo API](https://developers.vimeo.com).

- [Get Started](#get-started-with-the-vimeo-api)
- [Help](#direct-help)
- [Troubleshooting](#troubleshooting)
- [Installation](#installation)
- [Usage](#usage)
    - [Authentication and access tokens](#generate-your-access-token)
        - [Unauthenticated tokens](#unauthenticated)
        - [Authenticated tokens](#authenticated)
    - [Make requests](#make-requests)
    - [Uploading videos](#uploading-videos)
        - [Upload videos from a server](#upload-videos-from-the-server)
        - [Replace videos from a server](#replace-videos-from-the-server)
        - [Client side uploads](#upload-or-replace-videos-from-the-client)
        - [Upload videos from a URL](#upload-videos-from-a-url)
    - [Upload images](#upload-images)
- [Framework integrations](#framework-integrations)

## Get started with the Vimeo API

There is a lot of information about the Vimeo API at <https://developer.vimeo.com/api/start>. Most of your questions are answered there!

## Direct Help

 * [Stack Overflow](http://stackoverflow.com/questions/tagged/vimeo-api)
 * [Vimeo Support](https://vimeo.com/help/contact)

#### NOTE: How to use the PHP library with Vimeo dot notation documentation.

The API docs often uses dot notation to represent a hierarchy of data, like this:  `privacy.view`. Because this library sends all data using JSON, you must use nested associative arrays, not dot notation.

```php
// The documentation refers to the following as `privacy.view`
$params = ['privacy' => ['view' => 'disable']];
```

## Installation
1. Require this package, with [Composer](https://getcomposer.org/), in the root directory of your project.

```bash
composer require vimeo/vimeo-api
```

Please note that this library requires at least PHP 7.1 installed. If you are on PHP 5.6, or PHP 7.0, please use install the package with the following:

```bash
composer require vimeo/vimeo-api ^2.0
```

2. Use the library `$lib = new \Vimeo\Vimeo($client_id, $client_secret)`.

## Usage
### Generate your access token

All requests require access tokens. There are two types of access tokens:

- [Unauthenticated](#unauthenticated) - Access tokens without a user. These tokens can view only public data.
- [Authenticated](#authenticated) - Access tokens with a user. These tokens interact on behalf of the authenticated user.

#### Unauthenticated

Unauthenticated API requests must generate an access token. You should not generate a new access token for each request. Instead, request an access token once and use it forever.

```php
// `scope` is an array of permissions your token needs to access.
// You can read more at https://developer.vimeo.com/api/authentication#supported-scopes
$token = $lib->clientCredentials(scope);

// usable access token
var_dump($token['body']['access_token']);

// accepted scopes
var_dump($token['body']['scope']);

// use the token
$lib->setToken($token['body']['access_token']);
```

#### Authenticated

1. Build a link to Vimeo so your users can authorize your app.

```php
$url = $lib->buildAuthorizationEndpoint($redirect_uri, $scopes, $state)
```

Name           | Type     | Description
---------------|----------|------------
`redirect_uri` | string   | The URI the user is redirected to in Step 3. This value must be provided to every step of the authorization process, including creating your app, building your authorization endpoint, and exchanging your authorization code for an access token.
`scope`        | array    | An array of permissions your token needs to access. You can read more at https://developer.vimeo.com/api/authentication#supported-scopes.
`state`        | string   | A value unique to this authorization request. You should generate it randomly and validate it in Step 3.

2. Your user needs to access the authorization endpoint (either by clicking the link or through a redirect). On the authorization endpoint, the user will have the option to deny your app any scopes you have requested. If they deny your app, they are redirected back to your `redirect_url` with an `error` parameter.

3. If the user accepts your app, they are redirected back to your `redirect_uri` with a `code` and `state` query parameter (eg. http://yourredirect.com?code=abc&state=xyz).
    1. You must validate that the `state` matches your state from Step 1.
    2. If the state is valid, you can exchange your code and `redirect_uri` for an access token.

```php
// `redirect_uri` must be provided, and must match your configured URI
$token = $lib->accessToken(code, redirect_uri);

// Usable access token
var_dump($token['body']['access_token']);

// Accepted scopes
var_dump($token['body']['scope']);

// Set the token
$lib->setToken($token['body']['access_token']);
```

For additional information, check out the [example](https://github.com/vimeo/vimeo.php/blob/master/example/auth.php).

### Make requests

The API library has a `request` method that takes three parameters. It returns an associative array containing all of the relevant request information.

#### Usage

Name      | Type     | Description
----------|----------|------------
`url`     | string   | The URL path (e.g.: `/users/dashron`).
`params`  | string   | An object containing all of your parameters (e.g.: `{ "per_page": 5, "filter" : "featured"}` ).
`method`  | string   | The HTTP method (e.g.: `GET`).

```php
$response = $lib->request('/me/videos', ['per_page' => 2], 'GET');
```

#### Response

The response array contains three keys.

Name       | Type   | Description
-----------|--------|------------
`body`     | array  | The parsed request body. All responses are JSON, so we parse this for you and give you the result.
`status`   | number | The HTTP status code of the response. This partially informs you about the success of your API request.
`headers`  | array  | An associative array containing all of the response headers.

```php
$response = $lib->request('/me/videos', ['per_page' => 2], 'GET');
var_dump($response['body']);
```

### Uploading videos
#### Upload videos from the server

To upload videos, you must call the `upload` method. It accepts two parameters. It returns the URI of the new video.

Internally, this library executes a `tus` upload approach and sends a file to the server with the [tus](https://tus.io/) upload protocol.

For more information, check out the [example](https://github.com/vimeo/vimeo.php/blob/master/example/upload.php)

Name     | Type    | Description
---------|---------|------------
`file`   | string  | Full path to the upload file on the local system.
`params` | array   | Parameters to send when creating a new video (name, privacy restrictions, etc.). See the [`/me/videos` documentation](https://developer.vimeo.com/api/reference/videos#upload_video) for supported parameters.

```php
$response = $lib->upload('/home/aaron/Downloads/ada.mp4')

// With parameters.
$response = $lib->upload('/home/aaron/Downloads/ada.mp4', [
    'name' => 'Ada',
    'privacy' => [
        'view' => 'anybody'
    ]
])
```

#### Replace videos from the server

To replace the source file of a video, you must call the `replace` method. It accepts two parameters. It returns the URI of the replaced video.

Name        | Type     | Description
------------|----------|------------
`video_uri` | string   | The URI of the original video. Once uploaded and successfully transcoded, your source video file is swapped with this new video file.
`file`      | string   | Full path to the upload file on the local system.

```php
$response = $lib->replace('/videos/12345', '/home/aaron/Downloads/ada-v2.mp4')
```

#### Upload or replace videos from the client

To upload from the client, you must mix some server-side and client-side API requests. We support two workflows, the first of which is much easier than the second.

##### Simple POST uploads

This workflow is well documented on Vimeo's developer site. You can read more here: <https://developer.vimeo.com/api/upload/videos#simple-upload>.

##### Streaming uploads

Streaming uploads support progress bars and resumable uploading. If you want to perform these uploads client-side, you need to start with some server-side requests.

Read through the [Vimeo documentation](https://developer.vimeo.com/api/upload/videos#resumable-upload) first. Steps 1 and 4 should be performed on the server, while Steps 2 and 3 can be performed on the client. With this workflow, the video is never transferred to your servers.

#### Upload videos from a URL

Uploading videos from a public URL (also called "pull uploads") uses a single, simple API call.

```php
$video_response = $lib->request(
    '/me/videos',
    [
        'upload' => [
            'approach' => 'pull',
            'link' => $url
        ],
    ],
    'POST'
);
```

#### Using a custom TusClient

If the standard [TusPhp\Client](https://github.com/ankitpokhrel/tus-php/blob/main/src/Tus/Client.php) doesn't work for you,
you can pass in a custom factory for a client that suits your needs. See the [custom_cache example](https://github.com/vimeo/vimeo.php/blob/master/example/upload_image.php).

### Upload images

To upload an image, call the `uploadImage` method. It takes three parameters.

For more information check out the [example](https://github.com/vimeo/vimeo.php/blob/master/example/upload_image.php).

Name           | Type     | Description
---------------|----------|------------
`pictures_uri` | string   | The URI to the pictures collection for a single resource, such as `/videos/12345/pictures`. You can always find this in the resource representation.
`file`         | string   | Full path to the upload file on the local system.
`activate`     | boolean  | (Optional) Defaults to `false`. If true, this picture will become the default picture for the associated resource.

```php
$response = $lib->uploadImage('/videos/12345/pictures', '/home/aaron/Downloads/ada.png', true)
```

## Troubleshooting

If you have any questions or problems, create a [ticket](https://github.com/vimeo/vimeo.php/issues) or [contact us](https://vimeo.com/help/contact).

## Framework integrations

- **WordPress** - <http://vimeography.com/>
- **Laravel** - <https://github.com/vimeo/laravel>

If you have integrated Vimeo into a popular PHP framework, let us know!

## Contributors

To see the contributors, please visit the [contributors graph](https://github.com/vimeo/vimeo.php/graphs/contributors).
