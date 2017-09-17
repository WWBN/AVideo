# Dns Component

[![Build Status](https://secure.travis-ci.org/reactphp/dns.png?branch=master)](http://travis-ci.org/reactphp/dns) [![Code Climate](https://codeclimate.com/github/reactphp/dns/badges/gpa.svg)](https://codeclimate.com/github/reactphp/dns)

Async DNS resolver for [ReactPHP](https://reactphp.org/)

The main point of the DNS component is to provide async DNS resolution.
However, it is really a toolkit for working with DNS messages, and could
easily be used to create a DNS server.

**Table of contents**

* [Basic usage](#basic-usage)
* [Caching](#caching)
  * [Custom cache adapter](#custom-cache-adapter)
* [Advanced usage](#advanced-usage)
  * [HostsFileExecutor](#hostsfileexecutor)
* [Install](#install)
* [Tests](#tests)
* [License](#license)
* [References](#references)

## Basic usage

The most basic usage is to just create a resolver through the resolver
factory. All you need to give it is a nameserver, then you can start resolving
names, baby!

```php
$loop = React\EventLoop\Factory::create();
$factory = new React\Dns\Resolver\Factory();
$dns = $factory->create('8.8.8.8', $loop);

$dns->resolve('igor.io')->then(function ($ip) {
    echo "Host: $ip\n";
});

$loop->run();
```

See also the [first example](examples).

> Note that the factory loads the hosts file from the filesystem once when
  creating the resolver instance.
  Ideally, this method should thus be executed only once before the loop starts
  and not repeatedly while it is running.

Pending DNS queries can be cancelled by cancelling its pending promise like so:

```php
$promise = $resolver->resolve('reactphp.org');

$promise->cancel();
```

But there's more.

## Caching

You can cache results by configuring the resolver to use a `CachedExecutor`:

```php
$loop = React\EventLoop\Factory::create();
$factory = new React\Dns\Resolver\Factory();
$dns = $factory->createCached('8.8.8.8', $loop);

$dns->resolve('igor.io')->then(function ($ip) {
    echo "Host: $ip\n";
});

...

$dns->resolve('igor.io')->then(function ($ip) {
    echo "Host: $ip\n";
});

$loop->run();
```

If the first call returns before the second, only one query will be executed.
The second result will be served from an in memory cache.
This is particularly useful for long running scripts where the same hostnames
have to be looked up multiple times.

See also the [third example](examples).

### Custom cache adapter

By default, the above will use an in memory cache.

You can also specify a custom cache implementing [`CacheInterface`](https://github.com/reactphp/cache) to handle the record cache instead:

```php
$cache = new React\Cache\ArrayCache();
$loop = React\EventLoop\Factory::create();
$factory = new React\Dns\Resolver\Factory();
$dns = $factory->createCached('8.8.8.8', $loop, $cache);
```

See also the wiki for possible [cache implementations](https://github.com/reactphp/react/wiki/Users#cache-implementations).

## Advanced Usage

For more advanced usages one can utilize the `React\Dns\Query\Executor` directly.
The following example looks up the `IPv6` address for `igor.io`.

```php
$loop = Factory::create();

$executor = new Executor($loop, new Parser(), new BinaryDumper(), null);

$executor->query(
    '8.8.8.8:53', 
    new Query($name, Message::TYPE_AAAA, Message::CLASS_IN, time())
)->done(function (Message $message) {
    foreach ($message->answers as $answer) {
        echo 'IPv6: ' . $answer->data . PHP_EOL;
    }
}, 'printf');

$loop->run();

```

See also the [fourth example](examples).

### HostsFileExecutor

Note that the above `Executor` class always performs an actual DNS query.
If you also want to take entries from your hosts file into account, you may
use this code:

```php
$hosts = \React\Dns\Config\HostsFile::loadFromPathBlocking();

$executor = new Executor($loop, new Parser(), new BinaryDumper(), null);
$executor = new HostsFileExecutor($hosts, $executor);

$executor->query(
    '8.8.8.8:53', 
    new Query('localhost', Message::TYPE_A, Message::CLASS_IN, time())
);
```

## Install

The recommended way to install this library is [through Composer](http://getcomposer.org).
[New to Composer?](http://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require react/dns:^0.4.11
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

This project aims to run on any platform and thus does not require any PHP
extensions and supports running on legacy PHP 5.3 through current PHP 7+ and
HHVM.
It's *highly recommended to use PHP 7+* for this project.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](http://getcomposer.org).
Because the test suite contains some circular dependencies, you may have to
manually specify the root package version like this:

```bash
$ COMPOSER_ROOT_VERSION=`git describe --abbrev=0` composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).

## References

* [RFC 1034](http://tools.ietf.org/html/rfc1034) Domain Names - Concepts and Facilities
* [RFC 1035](http://tools.ietf.org/html/rfc1035) Domain Names - Implementation and Specification
