# Requests runtime bundle

This directory contains the Requests 1.8.0 runtime distributed with the Razorpay
SDK. `../../Razorpay.php` loads `library/Requests.php` directly. The upstream
`tests/` directory and development dependencies are not shipped here.

The bundled Composer manifest preserves runtime requirements, autoloading,
licensing and attribution. Upstream-only `require-dev` and development scripts
were removed because this is not an independently installed development project.
They declared PHPUnit 4–7 without a lockfile, making Dependabot attempt an update
for a test runner that is not installed or used by AVideo.

Run AVideo tests using the root `composer.json`, `composer.lock` and PHPUnit
configuration. To develop Requests itself, use the complete upstream repository:
https://github.com/WordPress/Requests/tree/v1.8.0

When replacing this bundle, retain this packaging convention. This packaging
change does not upgrade Requests or change the Razorpay runtime.
