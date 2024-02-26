# Changelog
## [3.0.8] - 2021-05-03
### Fixed
- Fix user-agent in vimeo.php

## [3.0.7] - 2021-04-28
### Fixed
- Fix an issue with SSL certificates on certain requests ([#286](https://github.com/vimeo/vimeo.php/issues/286))

## [3.0.6] - 2020-06-20
### Fixed
- Add TUS 2.0

## [3.0.5] - 2019-12-11
### Fixed
- Fixes `setUrl` method not found error.([#239](https://github.com/vimeo/vimeo.php/pull/239)) 
- Adds `TusHelper` class.

## [3.0.4] - 2019-12-02
### Fixed
- Fixes "invalid bucket" error when uploading a new video. ([#237](https://github.com/vimeo/vimeo.php/pull/237))

## [3.0.3] - 2019-11-15
### Changed
- Updating `ankitpokhrel/tus-php` to stable `v1.0.0` release.

## [3.0.2] - 2018-12-18
### Fixed
- Locking our `ankitpokhrel/tus-php` at v0.1.0 instead of a specific commit. ([#189](https://github.com/vimeo/vimeo.php/issues/189))

## [3.0.1] - 2018-11-19
### Changed
- Updating the user agent version string to match the library version.

## [3.0.0] - 2018-11-19
### Added
- Added [Psalm](https://github.com/vimeo/psalm) into our test process for static analysis. ([#186](https://github.com/vimeo/vimeo.php/pull/186))

### Changed
- Rewrote the Tus upload integration to use [TusPHP](https://github.com/ankitpokhrel/tus-php). ([#186](https://github.com/vimeo/vimeo.php/pull/186))

### Removed
- Removed support for <PHP 7.1. ([#186](https://github.com/vimeo/vimeo.php/pull/186))

## [2.0.5] - 2018-04-27
### Changed
- Updating the user agent version string to match the library version.

## [2.0.4] - 2018-04-24
### Added
- Support for passing your own `Authorization` header on API requests. ([#166](https://github.com/vimeo/vimeo.php/pull/166))

## [2.0.3] - 2018-04-02
### Changed
- Uploads no longer make a pre-emptive request to check the user's quota. This check is done automatically when making a POST to `/me/videos`. ([#163](https://github.com/vimeo/vimeo.php/pull/163))

## [2.0.2] - 2018-03-20
### Added
- Support for passing API requests through a custom proxy. ([#161](https://github.com/vimeo/vimeo.php/pull/161), [@MichalMMac](https://github.com/MichalMMac))

## [2.0.1] - 2018-03-07
### Added
- Initializing a unit test environment. ([#143](https://github.com/vimeo/vimeo.php/pull/143), [@peter279k](https://github.com/peter279k))
- Support for making `HEAD` requests. ([#160](https://github.com/vimeo/vimeo.php/pull/160))

## [2.0.0] - 2018-02-06
### Changed
- Moving API requests over to use API v3.4. ([#144](https://github.com/vimeo/vimeo.php/pull/144))
- Moving uploads over to using the new tus protocol.  ([#144](https://github.com/vimeo/vimeo.php/pull/144))

## [1.3.0] - 2017-10-25
### Changed
- Deprecating the `upgrade_to_1080` option on video uploads. ([#140](https://github.com/vimeo/vimeo.php/pull/140))

## [1.2.7] - 2017-10-04
### Added
- Support for supplying custom HTTP headers. ([#136](https://github.com/vimeo/vimeo.php/pull/136), [@davekiss](https://github.com/davekiss))

### Fixed
- Updated some bad PHPDoc comments on the upload methods. ([#129](https://github.com/vimeo/vimeo.php/pull/129), [@hluup](https://github.com/hluup))

### Changed
- PSR-2 code style. ([#117](https://github.com/vimeo/vimeo.php/pull/117), [@peter279k](https://github.com/peter279k))

## [1.2.6] - 2016-12-02
### Fixed
- Updating the user agent so it matches the current release.

## [1.2.5] - 2016-10-13
### Fixed
- Some namespace gremlins. ([#112](https://github.com/vimeo/vimeo.php/pull/112), [@Spudley](https://github.com/Spudley))

## [1.2.4] - 2016-09-13
### Added
- Changelog (@vinkla)
- Added a new search example. ([@greedo](https://github.com/greedo))
- Support for lowercase HTTP verbs. ([#108](https://github.com/vimeo/vimeo.php/issues/108))

### Changed
- Updated some examples to match the new example format.

### Fixed
- Fixed some bad documentation in the README.
- Fixed a bad VOD example. ([#76](https://github.com/vimeo/vimeo.php/issues/76))
- Correctly handing headers when parsing them. ([#110](https://github.com/vimeo/vimeo.php/pull/110), [@qzminski](https://github.com/qzminski))

## [1.2.3] - 2015-06-02
### Added
- Handling timeouts for large files.

## [1.2.2] - 2015-05-11
### Fixed
- Reinstating the upload request. ([#72](https://github.com/vimeo/vimeo.php/pull/72))
## [1.2.1] - 2015-05-07
### Changed
- Move from pem to cer

## [1.2.0] - 2015-05-01
### Changed
- Better error messages when uploading ([#66](https://github.com/vimeo/vimeo.php/pull/66))
- Better error messages when curl errors ([#68](https://github.com/vimeo/vimeo.php/pull/68))
- Root cert is included to help with curl errors ([#69](https://github.com/vimeo/vimeo.php/pull/69))

## [1.1.0] - 2014-10-23
### Added
- Added composer support ([#6](https://github.com/vimeo/vimeo.php/pull/6))

## 1.0.0 - 2014-09-26
### Added
- This is the Vimeo library for version 3 of the Vimeo API.

[3.0.8]: https://github.com/vimeo/vimeo.php/compare/3.0.7...3.0.8
[3.0.7]: https://github.com/vimeo/vimeo.php/compare/3.0.6...3.0.7
[3.0.6]: https://github.com/vimeo/vimeo.php/compare/3.0.5...3.0.6
[3.0.5]: https://github.com/vimeo/vimeo.php/compare/3.0.4...3.0.5
[3.0.4]: https://github.com/vimeo/vimeo.php/compare/3.0.3...3.0.4
[3.0.3]: https://github.com/vimeo/vimeo.php/compare/3.0.2...3.0.3
[3.0.2]: https://github.com/vimeo/vimeo.php/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/vimeo/vimeo.php/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/vimeo/vimeo.php/compare/2.0.5...3.0.0
[2.0.5]: https://github.com/vimeo/vimeo.php/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/vimeo/vimeo.php/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/vimeo/vimeo.php/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/vimeo/vimeo.php/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/vimeo/vimeo.php/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/vimeo/vimeo.php/compare/1.3.0...2.0.0
[1.3.0]: https://github.com/vimeo/vimeo.php/compare/1.2.7...1.3.0
[1.2.7]: https://github.com/vimeo/vimeo.php/compare/1.2.6...1.2.7
[1.2.6]: https://github.com/vimeo/vimeo.php/compare/1.2.5...1.2.6
[1.2.4]: https://github.com/vimeo/vimeo.php/compare/1.2.5...1.2.6
[1.2.5]: https://github.com/vimeo/vimeo.php/compare/1.2.4...1.2.5
[1.2.4]: https://github.com/vimeo/vimeo.php/compare/1.2.3...1.2.4
[1.2.3]: https://github.com/vimeo/vimeo.php/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/vimeo/vimeo.php/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/vimeo/vimeo.php/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/vimeo/vimeo.php/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/vimeo/vimeo.php/compare/1.0.0...1.1.0
