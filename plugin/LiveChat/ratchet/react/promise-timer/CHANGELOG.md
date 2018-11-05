# Changelog

## 1.2.0 (2017-08-08)

* Feature: Only start timers if input Promise is still pending and
  return a settled output promise if the input is already settled.
  (#25 by @clue)

* Feature: Cap minimum timer interval at 1µs across all versions
  (#23 by @clue)

* Feature: Forward compatibility with EventLoop v1.0 and v0.5
  (#27 by @clue)

* Improve test suite by adding PHPUnit to require-dev and
  lock Travis distro so new defaults will not break the build
  (#24 and #26 by @clue)

## 1.1.1 (2016-12-27)

* Improve test suite to use PSR-4 autoloader and proper namespaces.
  (#21 by @clue)

## 1.1.0 (2016-02-29)

* Feature: Support promise cancellation for all timer primitives
  (#18 by @clue)

## 1.0.0 (2015-09-29)

* First tagged release
