# Interacting with the Vimeo API using PHP

This is a simple PHP library for interacting with the [Vimeo API](https://developers.vimeo.com).

## Before you start

### Get started with the Vimeo API

If you’re new to Vimeo APIs, check out [Getting Started: The Basics](https://developer.vimeo.com/api/start) before diving into the content on this page. 

### Understand the PHP hierarchy

The API docs often use dot notation to represent a hierarchy of data, such as `privacy.view`. Since the PHP library sends all data using JSON, you must use nested associative arrays instead of dot notation.

```php
// The documentation refers to the following as `privacy.view`
$params = ['privacy' => ['view' => 'disable']];
```

## Install and access the PHP library

To install the PHP library, run the following command:

```bash
composer require vimeo/vimeo-api
```

After installation is complete, you can access the library by using `$lib = new \Vimeo\Vimeo($client_id, $client_secret)` in a Composer-enabled PHP script.

## Advanced examples

To see examples of the most common use cases of the PHP library, visit our [PHP Library Examples](https://developer.vimeo.com/api/libraries/examples) page. 

## Framework integrations

We have PHP framework integrations for [WordPress](http://vimeography.com/) and [Laravel](https://github.com/vimeo/laravel). 

If you've integrated Vimeo into a popular PHP framework, [let us know](https://vimeo.com/help/contact)!

## Support

To troubleshoot an issue, reach out to [Vimeo Support](https://vimeo.com/help/contact).
