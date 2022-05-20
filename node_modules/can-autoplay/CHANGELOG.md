# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [3.0.0] - 2018-02-25

- Added pre-compilation step with Blob Generator
- Changed core implementation where it's now based on blobs
- Changed small media structure where media is stored as a separate local file
- Changed unit tests to work with blob based implementation
- Changed Rollup build to have all builds unified in a single step
- Changed AVA dependency to `0.25.0`

## [2.3.2] - 2018-02-15

- Fixed build by adding missed minified file

## [2.3.1] - 2018-02-15

- Fixed build by adding missed bundled files for CommonJS and ES6 variants

## [2.3.0] - 2018-02-15

- Added option `blob` to use blob as media source instead of base64

## [2.2.1] - 2018-02-13

- Fixed build by adding missed bundled files

## [2.2.0] - 2018-02-13

- Added option `inline` to check auto-play for inline playback

## [2.1.1] - 2018-02-02

- Added notes about media files used in the project
- Fixed imports to provide wrapper Object

## [2.1.0] - 2018-02-01

- Added ES5/ES6 versions of the library for bundlers

## [2.0.1] - 2017-12-11

- Changed documentation to include latest API changes

## [2.0.0] - 2017-12-04

- Added error for timeout
- Added changelog tracking
- Changed DOM test framework to JSDom
- Changed API to use `audio/video` methods with same payload
- Changed playback detection to rely on browser's `play()` Promise API
- Changed documentation to include examples with Promise API
- Removed `videoMuted` method in favor of generic API for `video` and `audio`
- Removed `DOM` invalidation

## [1.0.1] - 2017-11-17

- Added minified version of the library
- Added size badge
- Added more examples
- Changed `Ava` test output

## [1.0.0] - 2017-11-16

- Initial release
