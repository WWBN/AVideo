# HTTP error diagnostics

After `objects/functionsLogs.php` loads, web requests receive an `X-AVideo-Request-ID` response header. A request ending in HTTP 500–599, or a PHP fatal error, writes a `RequestDiagnostic` JSON record through the existing `_error_log()` destination. Successful requests do not add an error diagnostic. CLI jobs keep their existing behavior.

Shutdown records are rate-limited with `rateLimitedLog()`: one `http_error` per entry point and status, and one `php_fatal` per fatal type/file/line, every 5 minutes. Polling endpoints (chat, live audience), the VAST ad proxy (502 when an ad server fails) and a page broken on every request therefore add one line, not one per viewer. A suppressed request still gets its `X-AVideo-Request-ID` header, but its ID is not in the log; search by script and time instead. If a request fails before `functions.php` loads, the record is written without throttling.

Each record starts with a readable summary, followed by the JSON:

```text
RequestDiagnostic PHP Fatal error: Uncaught Error: Call to undefined method Video::getFoo() at plugin/Gallery/view/modeGallerySection.php:87 (GET view/modeYoutube.php, request a7c6...) {"event":"php_fatal",...,"details":{...,"trace":["plugin/Gallery/view/mainArea.php(41): include()","view/modeYoutube.php(12): createGallerySection()"],"recent_log":["..."]}}
```

The JSON has the UTC time, correlation ID, HTTP status observed at shutdown, PHP entry point, method and PID. Fatal errors add their type, message, file and line, and an uncaught exception adds up to 12 stack frames read from PHP's message. `recent_log` holds the last 3 non-performance lines (300 characters each) that the same request had already written with `_error_log()`: for an endpoint that logs its failure and then answers 500, that is the cause. Paths are relative to `systemRootPath`.

It does not collect request URLs/query strings, headers, cookies, POST bodies, session data or stack arguments. Error messages are shortened and have quoted values, URLs and recognized credentials (`Authorization`, `Cookie`, `Bearer`, `password=`, `token=`...) replaced by `[... redacted]`. `recent_log` is a copy of lines already in the same log, so it adds no data that was not logged before.

For an exception caught by application code, log it at the catch site for a real stack trace without arguments:

```php
catch (\Throwable $exception) {
    avideoLogException($exception);
    // Keep the endpoint's existing status and generic response contract.
}
```

This records the exception class, numeric code, redacted message, file, line and up to 12 frames with the same request ID. It does not change authentication, redirect, response-body, cache, session or plugin contracts. A generic HTTP 500 without a logged exception identifies the request but cannot reconstruct a stack that the application already discarded.

Check the configured PHP error-log destination, not only `videos/avideo.log`: Docker installations can route logs to a stream. Keep PHP `display_errors` off in production, make the logging destination writable, and retain bounded server/container logs. A correlation ID in a cached response can belong to the earlier request that produced that cached response.

Limitations: PHP parse/startup failures before this helper loads, web-server rewrite errors, proxy-generated 502/503, process kills and container restarts require PHP/Apache/Nginx/Docker logs. This helper does not make their logs available automatically. An ID alone does not identify the cause or demonstrate a security incident. An earlier shutdown handler that exits before these callbacks can also prevent a later callback from running.

Validation: `php vendor/phpunit/phpunit/phpunit tests/Unit/RequestDiagnosticsTest.php` exercises real local HTTP 200, explicit 500, `trigger_error` fatal and uncaught-exception requests, the summary, trace and `recent_log` fields, rate limiting, correlation, secret exclusion, caught exceptions and `printLogs` compatibility.
