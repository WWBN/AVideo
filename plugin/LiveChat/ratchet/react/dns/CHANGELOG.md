# Changelog

## 0.4.11 (2017-08-25)

*   Feature: Support resolving from default hosts file
    (#75, #76 and #77 by @clue)

    This means that resolving hosts such as `localhost` will now work as
    expected across all platforms with no changes required:

    ```php
    $resolver->resolve('localhost')->then(function ($ip) {
        echo 'IP: ' . $ip;
    });
    ```

    The new `HostsExecutor` exists for advanced usage and is otherwise used
    internally for this feature.

## 0.4.10 (2017-08-10)

* Feature: Forward compatibility with EventLoop v1.0 and v0.5 and 
  lock minimum dependencies and work around circular dependency for tests
  (#70 and #71 by @clue)

* Fix: Work around DNS timeout issues for Windows users
  (#74 by @clue)

* Documentation and examples for advanced usage
  (#66 by @WyriHaximus)

* Remove broken TCP code, do not retry with invalid TCP query
  (#73 by @clue)

* Improve test suite by fixing HHVM build for now again and ignore future HHVM build errors and
  lock Travis distro so new defaults will not break the build and
  fix failing tests for PHP 7.1
  (#68 by @WyriHaximus and #69 and #72 by @clue)

## 0.4.9 (2017-05-01)

* Feature: Forward compatibility with upcoming Socket v1.0 and v0.8
  (#61 by @clue)

## 0.4.8 (2017-04-16)

* Feature: Add support for the AAAA record type to the protocol parser
  (#58 by @othillo)

* Feature: Add support for the PTR record type to the protocol parser
  (#59 by @othillo)

## 0.4.7 (2017-03-31)

* Feature: Forward compatibility with upcoming Socket v0.6 and v0.7 component
  (#57 by @clue)

## 0.4.6 (2017-03-11)

* Fix: Fix DNS timeout issues for Windows users and add forward compatibility
  with Stream v0.5 and upcoming v0.6
  (#53 by @clue)

* Improve test suite by adding PHPUnit to `require-dev`
  (#54 by @clue)

## 0.4.5 (2017-03-02)

* Fix: Ensure we ignore the case of the answer
  (#51 by @WyriHaximus)

* Feature: Add `TimeoutExecutor` and simplify internal APIs to allow internal
  code re-use for upcoming versions.
  (#48 and #49 by @clue)

## 0.4.4 (2017-02-13)

* Fix: Fix handling connection and stream errors
  (#45 by @clue)

* Feature: Add examples and forward compatibility with upcoming Socket v0.5 component
  (#46 and #47 by @clue)

## 0.4.3 (2016-07-31)

* Feature: Allow for cache adapter injection (#38 by @WyriHaximus)

  ```php
  $factory = new React\Dns\Resolver\Factory();

  $cache = new MyCustomCacheInstance();
  $resolver = $factory->createCached('8.8.8.8', $loop, $cache);
  ```

* Feature: Support Promise cancellation (#35 by @clue)

  ```php
  $promise = $resolver->resolve('reactphp.org');

  $promise->cancel();
  ```

## 0.4.2 (2016-02-24)

* Repository maintenance, split off from main repo, improve test suite and documentation
* First class support for PHP7 and HHVM (#34 by @clue)
* Adjust compatibility to 5.3 (#30 by @clue)

## 0.4.1 (2014-04-13)

* Bug fix: Fixed PSR-4 autoload path (@marcj/WyriHaximus)

## 0.4.0 (2014-02-02)

* BC break: Bump minimum PHP version to PHP 5.4, remove 5.3 specific hacks
* BC break: Update to React/Promise 2.0
* Bug fix: Properly resolve CNAME aliases
* Dependency: Autoloading and filesystem structure now PSR-4 instead of PSR-0
* Bump React dependencies to v0.4

## 0.3.2 (2013-05-10)

* Feature: Support default port for IPv6 addresses (@clue)

## 0.3.0 (2013-04-14)

* Bump React dependencies to v0.3

## 0.2.6 (2012-12-26)

* Feature: New cache component, used by DNS

## 0.2.5 (2012-11-26)

* Version bump

## 0.2.4 (2012-11-18)

* Feature: Change to promise-based API (@jsor)

## 0.2.3 (2012-11-14)

* Version bump

## 0.2.2 (2012-10-28)

* Feature: DNS executor timeout handling (@arnaud-lb)
* Feature: DNS retry executor (@arnaud-lb)

## 0.2.1 (2012-10-14)

* Minor adjustments to DNS parser

## 0.2.0 (2012-09-10)

* Feature: DNS resolver
