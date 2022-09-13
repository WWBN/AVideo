# process

<p>
<a href="https://coveralls.io/github/amphp/process?branch=master"><img src="https://img.shields.io/coveralls/amphp/process/master.svg?style=flat-square" alt="Code Coverage"/></a>
<a href="https://github.com/amphp/process/releases"><img src="https://img.shields.io/github/release/amphp/process.svg?style=flat-square" alt="Release"/></a>
<a href="https://github.com/amphp/process/blob/master/LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License"/></a>
</p>

This package provides an asynchronous process dispatcher that works on all major platforms (including Windows).

As Windows pipes are file handles and do not allow non-blocking access, this package makes use of a [process wrapper](https://github.com/amphp/windows-process-wrapper), that provides access to these pipes via sockets.
On Unix-like systems it uses the standard pipes, as these can be accessed without blocking there.
Concurrency is managed by the [Amp](https://github.com/amphp/amp) event loop.

## Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```
composer require amphp/process
```

## Requirements

* PHP 7.0+

## Versioning

`amphp/process` follows the [semver](http://semver.org/) semantic versioning specification like all other `amphp` packages.

## Security

If you discover any security related issues, please email [`me@kelunik.com`](mailto:me@kelunik.com) instead of using the issue tracker.

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information.
