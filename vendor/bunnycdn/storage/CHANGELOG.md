# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.4.0] - 2024-07-29

### Added

- Support for async uploads with `Client::uploadAsync()`;

## [3.3.1] - 2024-07-23

### Changed

- Improve error message for HTTP 400 errors during uploads ([#16](https://github.com/BunnyWay/BunnyCDN.PHP.Storage/pull/16));

## [3.3.0] - 2024-04-16

### Changed

- Support created/modified dates without microseconds on `info` and `list`;
- FileInfo `__construct` signature;

## [3.2.0] - 2024-04-05

### Changed

- Checksum uploads in `Client::upload()` and `Client::putContents()`. It can be disabled using the `bool $withChecksum` parameter;

## [3.1.0] - 2024-03-25

### Added

- Support for retrieving an object's metadata with `Client::info()`;

## [3.0.0] - 2024-03-20

### BC breaks

- `listFiles` now returns an array of `Bunny\Storage\FileInfo`;
- `delete`, `download`, `putContents`, `upload` now return `void`;

### Added

- PHP 7.4 support;
- Support for directories in `Client::delete()` and `Client::deleteMultiple()`;

## [2.1.0] - 2024-02-23

### Added

- Support for download/upload content in memory with `Client::getContents()` and `Client::putContents()`;
- Support for deleting multiple files in parallel with `Client::deleteMultiple()`;

### Changed

- Replaced `ext-curl` with `guzzlehttp/guzzle`, which might use either cURL or PHP streams;

## [2.0.0] - 2023-12-14

### Added

- Composer support;
- Strict types support;
- Minimum PHP version;
- Static analysis checks;
