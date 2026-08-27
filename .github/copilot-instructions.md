# AVideo Copilot Instructions

## Mandatory Canonical Security Policy

For every task related to security in any way—including security advisories, vulnerability reports, audits, reviews, hardening, authentication, authorization, access control, sensitive-data exposure, secrets, PII, SQL injection, XSS, CSRF, SSRF, command execution, uploads, path traversal, or a proposed security fix—read the [AVideo Security Advisory Triage prompt](prompts/avideo-security-advisory-triage.prompt.md) completely before analyzing the issue, reaching conclusions, suggesting changes, or modifying code.

Treat that prompt as the canonical and authoritative repository policy for security investigation, classification, regression analysis, fix decisions, testing, and reporting. Follow all applicable requirements from it. If another repository instruction conflicts with that prompt on a security matter, the canonical security prompt takes precedence; continue following all non-conflicting repository instructions.

Do not copy or restate the detailed security policy here. Update the canonical prompt only, so GitHub Copilot and Codex use the same source of truth.

## Project Overview

AVideo is an open-source, self-hosted video streaming platform (PHP/MySQL/JS) organized into three
deployable components described in the README: **Streamer** (main web app — video/user/plugin
management), **Encoder** (converts uploads to web-compatible/HLS formats), and **Live Server**
(RTMP/live streaming, typically Nginx-based). The codebase has a plugin-based architecture with
~130+ plugins under `plugin/`. Assume any given task touches a live, upgraded-in-place production
installation — reuse existing helpers, classes, and patterns before writing anything new.

More detailed, topic-specific rules already exist in `.github/instructions/*.md` and are
auto-applied by file pattern. This file is the top-level summary; when in doubt, the detailed
files win:
- [avideo-php-development.instructions.md](instructions/avideo-php-development.instructions.md) — PHP style, AJAX endpoints, error handling
- [avideo-security.instructions.md](instructions/avideo-security.instructions.md) — auth, SQLi/XSS/CSRF, uploads, path traversal
- [avideo-database.instructions.md](instructions/avideo-database.instructions.md) — sqlDAL, migrations, table conventions
- [avideo-plugin-development.instructions.md](instructions/avideo-plugin-development.instructions.md) — plugin structure and hooks
- [avideo-frontend.instructions.md](instructions/avideo-frontend.instructions.md) — JS/CSS/Bootstrap/Video.js conventions
- [avideo-prefer-existing-frontend-libraries.instructions.md](instructions/avideo-prefer-existing-frontend-libraries.instructions.md) — reuse bundled libraries
- [avideo-reuse-existing-code.instructions.md](instructions/avideo-reuse-existing-code.instructions.md) — search-first workflow and helper inventory

## Stack (verified from `composer.json` / `package.json`)

- **Backend**: PHP `^8.1` (composer platform pinned to `8.1`). CI (`.github/workflows/tests.yml`)
  runs the matrix `8.1`, `8.2`, `8.3`. No PHP namespaces in core files (only Composer vendor
  libraries use them); avoid PHP 8.1+-only syntax (enums, readonly props, fibers) unless the
  surrounding file already uses it — most core files still target a much older, non-strict style.
- **Database**: MySQL/MariaDB via `sqlDAL` prepared statements (`objects/mysql_dal.php`). Docker
  stack (`docker-compose.yml`, `Dockerfile.mariadb`) runs app + live server + MariaDB.
  Schema/version is tracked in the `configurations` table.
- **Key composer dependencies**: `google/apiclient`, `aws/aws-sdk-php`, `gliterd/backblaze-b2`,
  `bunnycdn/storage` (cloud storage/CDN providers), `paypal/*`, `stripe/stripe-php`,
  `authorizenet/authorizenet` (payments), `phpmailer/phpmailer`, `hybridauth/hybridauth` (social
  login), `james-heinrich/getid3` (media metadata), `zircote/swagger-php` (API docs),
  `monolog/monolog`, `ezyang/htmlpurifier`. Test deps: `phpunit/phpunit` `^9.6`, `mockery/mockery`.
- **Frontend** (all bundled locally via `package.json` / `view/`, never via CDN): Bootstrap
  `5.3.x` in `node_modules`, plus a legacy Bootstrap 3 build still bundled in `view/bootstrap/`
  and used by many older views — match whichever version the file/page you're editing already
  uses, do not silently upgrade a Bootstrap-3 page to Bootstrap-5 markup. jQuery `4.0`, jQuery UI,
  Video.js `8.x` (+ HLS quality selector, Chromecast/AirPlay, IMA ads, YouTube, VR plugins),
  HLS.js `1.6.x`, Socket.io-client `4.8.x`, Chart.js `4.5.x`, TinyMCE `8.x`, Croppie, Flatpickr,
  Intro.js, SweetAlert, FullCalendar, GLightbox, Flickity.
- **Architecture**: Plugin-based via `PluginAbstract` (`plugin/Plugin.abstract.php`), dispatched by
  `AVideoPlugin` (`plugin/AVideoPlugin.php`).

## Critical Rules

### Search Before Creating
Before writing any new code, search the repository for existing logic:
- Global helpers: `objects/functions*.php`
- Database layer: `objects/mysql_dal.php` — use `sqlDAL::readSql()` / `sqlDAL::writeSql()`
- Security filters: `objects/functionsSecurity.php` — use `$securityFilter`, `xss_esc()`, `htmlspecialchars()`
- Auth checks: `User::isLogged()`, `User::isAdmin()`, `User::getId()` (`objects/user.php`)
- Logging: `_error_log($msg, AVideoLog::$ERROR)` — never `echo` errors to normal users
- Config: `$global['systemRootPath']`, `$global['webSiteRootURL']`, `AVideoConf` singleton
- Plugin hooks: `AVideoPlugin::getHeadCode()`, `getFooterCode()`, `getBodyContent()`, etc.
- Admin forms: `createTable()`, `jsonToFormElements()` (`admin/functions.php`)
- FFmpeg/exec wrappers: `get_ffmpeg()`, `get_ffprobe()` (`objects/functionsFFMPEG.php`), never call
  the `ffmpeg`/`ffprobe` binaries directly.

### Do Not Invent
- Do not invent table names, column names, constants, class names, or function names.
- Do not guess whether a plugin, table, or function exists — search first using the repository tools.
- Never present fabricated function names, method signatures, or class names as if they exist in the codebase.
- If still unsure after searching, add a `// TODO: verify this exists` comment rather than inventing an API.
- When citing a method from `objects/user.php` or any other file, verify it in the source before referencing it.

### Plugin Architecture & Compatibility
- Keep plugin logic inside its own plugin directory (`plugin/PluginName/`).
- Extend `PluginAbstract`; implement `getUUID()`, `getName()`, `getDescription()`.
- Register hooks via the standard hook methods (`getHeadCode`, `getFooterCode`, `getHTMLBody`,
  `getHTMLMenuLeft`/`Right`, `getChartContent`, `getPluginMenu`, `getHelp`, `updateScript`, etc.).
- Database migrations go in `plugin/PluginName/install/updateVX.X.sql`; core migrations go in
  `updatedb/updateDb.vX.X.sql` (sequential, currently up to `v30.0` — pick the next version number).
- Never modify `plugin/Plugin.abstract.php` or `plugin/AVideoPlugin.php` without an explicit request
  — every one of the ~130+ plugins depends on their current contract.
- Interact with other plugins only via `AVideoPlugin::loadPluginIfEnabled()` / `loadPlugin()`, never
  by instantiating another plugin's classes or reading its tables directly.
- Assume third-party/community plugins outside this repo may also extend `PluginAbstract` and call
  core hooks — do not change hook method signatures or remove hooks that plugins may rely on.

### Coding Style
- Match the style of nearby files exactly.
- PHP: no strict modern syntax unless it already appears in nearby files.
- Error handling: `try { ... } catch (\Throwable $th) { _error_log($th->getMessage()); }`.
- AJAX endpoints: name them `*.json.php`; return `['error' => false, 'msg' => '...']`.
- Use `modal.showPleaseWait()` / `modal.hidePleaseWait()` around async operations.
- Use `avideoToastSuccess()`, `avideoToastError()`, `avideoAlertError()` for user feedback.
- Do not reformat or restyle surrounding code you are not asked to change — diffs should be small
  and focused on the requested behavior.

### Known Security Anti-Patterns — Never Repeat These

These bugs exist throughout the legacy codebase. Do not reproduce them:

```php
// WRONG — the most common mistake in AVideo plugins
// die(json_encode(...)) does NOT set HTTP 403, does NOT log, does NOT redirect to login
if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"])); // WRONG
}

// WRONG — http_response_code(403) alone is not enough
if (!User::isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => true, 'msg' => 'Not authorized'])); // WRONG
}

// WRONG — auth check inside try/catch; forbiddenPage() calls exit() which try/catch swallows
try {
    if (!User::isAdmin()) { forbiddenPage('...'); } // WRONG PLACEMENT
} catch (\Throwable $th) { ... }
```

```php
// CORRECT — forbiddenPage() handles HTTP status, audit logging, and login redirect atomically
// CORRECT — always BEFORE try/catch
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true); // CORRECT
}
$response = ['error' => false, 'msg' => ''];
try { ... } catch (\Throwable $th) { ... }
```

Other security rules that apply repo-wide (see `avideo-security.instructions.md` for full detail):
- SQL: always `sqlDAL::readSql()` / `writeSql()` with `?` placeholders — never string-interpolated SQL.
- Output: escape with `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` or `xss_esc($v)` for HTML,
  `json_encode()` for JSON; never echo raw user input.
- CSRF: state-changing endpoints must embed/validate `globalToken` via `getToken()` /
  `isGlobalTokenValid()` — do not invent a custom CSRF mechanism.
- Shell commands: always build commands through `get_ffmpeg()`/`get_ffprobe()` and
  `escapeshellarg()`/`escapeshellcmd()` for any user-influenced value passed to `exec()`,
  `shell_exec()`, or `proc_open()`. Never interpolate raw request data into a shell command.
- File uploads: validate MIME type with `finfo`, not just the extension; never store uploads in a
  PHP-executable directory.
- Secrets: never hardcode API keys/tokens/passwords; read them from `AVideoConf`/`$global`. Never
  print/log secrets, and never commit `videos/configuration.php` (already gitignored).

### Video, Encoding, Live Streaming, Storage & CDN (high-risk areas)

These areas are easy to break silently (a broken encode/playback path often only surfaces once a
real video/stream is processed) and are hard to unit test. Treat changes here as high-risk:

- **FFmpeg / encoding**: always go through `get_ffmpeg()` / `get_ffprobe()`
  (`objects/functionsFFMPEG.php`) instead of hardcoding `ffmpeg`/`ffprobe` — these wrappers apply
  the configured binary path, GPU flag, and user-agent. Command building elsewhere
  (`objects/functionsExec.php`) escapes arguments with `escapeshellarg()`; preserve that pattern
  and do not introduce a new raw `exec()`/`eval()` call with unescaped input. The `Encoder`
  component is a separate deployable service — do not assume encoder-side code runs in the same
  process/request as the Streamer.
- **Live streaming**: `plugin/Live/` implements RTMP webhook-style endpoints
  (`on_publish.php`, `on_play.php`, `on_publish_done.php`, `on_record_done.php`, etc.) called by
  the Nginx/RTMP live server component. These are integration points with an external process —
  do not change their expected request/response contract without checking the live-server side.
- **HLS / VideoHLS**: HLS packaging, encryption keys, and download protection
  (`plugin/VideoHLS/`) gate access to on-demand and live content; do not weaken key/token checks
  when touching this path.
- **Storage & CDN**: multiple storage backends are supported (`storage/data/` local disk,
  `AWS_S3`, `Blackblaze_B2`/`gliterd/backblaze-b2`, `FTP_Storage`, `bunnycdn/storage`, `YPTStorage`,
  `CDN` plugin). Path/URL-building helpers already exist per backend — reuse them rather than
  constructing storage paths/URLs manually, since a wrong path can silently break playback only on
  non-local storage.
- **Manual testing required**: changes to FFmpeg/encoding, HLS packaging, live streaming
  (RTMP/on_publish flows), CDN/storage backends, payments (PayPal/Stripe/AuthorizeNet/Blockonomics),
  or third-party auth/social-login providers cannot be fully verified by automated tests in this
  repo. Explicitly call out in your summary that these changes need manual verification against a
  real encoder/live server/storage provider/payment sandbox before merging.

### API Compatibility

- The public/third-party API lives in `plugin/API/` (`plugin/API/API.php` + `plugin/API/api/`),
  documented with `zircote/swagger-php` OpenAPI attributes and secured with a Bearer `APISecret`
  token scheme.
- Do not change existing endpoint URLs, request parameters, or response field names/shapes —
  external and mobile-app integrations depend on the current contract. Add new optional fields
  instead of renaming/removing existing ones.
- Public API responses must strip sensitive user fields (see `API::getSensitiveUserFields()` for
  the current list: `email`, `password`, `isAdmin`, permission flags, PII, etc.) — when adding a
  new endpoint that joins the `users` table, follow this same filtering pattern.
- Keep OpenAPI attributes (`#[OA\...]`) in sync when changing API method signatures.

### Backward Compatibility
- Do not change public APIs (core PHP classes/functions, plugin hooks, the `plugin/API/` HTTP API,
  AJAX endpoint contracts) unless explicitly requested.
- Do not break existing plugins, including third-party ones not in this repo.
- Do not drop or rename database columns/tables without a guarded migration; add new columns
  instead and deprecate old ones with a comment.
- Keep all schema changes backward compatible with sites that upgrade in place from an older
  version — migrations must be idempotent (`IF NOT EXISTS`/`IF EXISTS`) and never assume a clean
  install.
- Never remove a deprecated function, hook, config key, or endpoint without checking whether any
  in-repo plugin (or a documented external integration such as the API/mobile app) still calls it.

## Database Migrations — Never Modify History

- Core migrations: `updatedb/updateDb.vX.X.sql`, one file per released version, applied in order
  and tracked via the `configurations.version` column.
- Plugin migrations: `plugin/PluginName/install/updateVX.X.sql`, applied via each plugin's
  `updateScript()` and `AVideoPlugin::compareVersion()`.
- Never edit an existing, already-released migration file — add a new versioned file instead, even
  for a one-line fix. Existing installations have already run the old file.
- Guard all DDL with `IF NOT EXISTS` / `IF EXISTS`; give new columns a safe `DEFAULT` so existing
  rows remain valid.
- All data access goes through `sqlDAL` prepared statements (`objects/mysql_dal.php`) — never raw
  `mysqli_query()` with interpolated values. See `avideo-database.instructions.md` for the full
  read/write/transaction API and table-naming conventions.

## Dependency Management

- Backend dependencies are managed by Composer (`composer.json`, PHP `^8.1` platform). Note the
  `apply-patches` Composer script that `sed`-patches
  `vendor/paypal/rest-api-sdk-php/lib/PayPal/Common/PayPalModel.php` after install/update — this
  means the PayPal SDK is intentionally patched in place; running `composer update` must keep this
  script working, and vendor code should not be hand-edited outside of this mechanism.
- Frontend dependencies are managed by npm (`package.json`) and served locally from `node_modules`
  or `view/` — never add a CDN `<script>`/`<link>` tag.
- Do not add a new Composer or npm dependency (or replace an existing library) unless it is
  explicitly requested or no bundled library can reasonably cover the need — see
  `avideo-prefer-existing-frontend-libraries.instructions.md` for the frontend decision order.
- After adding/upgrading a dependency, confirm `composer.json`/`package.json` and any lock file are
  updated together, and re-check the PayPal patch script above still applies cleanly.

## Testing & Validation Expected Before Considering a Task Done

- Test suites live under `tests/Security/` and `tests/Unit/` (PHPUnit `^9.6`, config in
  `phpunit.xml`, bootstrap `tests/bootstrap.php`).
- Run relevant tests locally before calling a change complete:
  ```bash
  composer test                     # full suite (phpunit.xml)
  composer test:unit                # tests/Unit only
  vendor/bin/phpunit tests/Security  # security suite — CI treats this as critical/must-pass
  composer test:filter -- --filter=SomeTestName
  ```
- CI (`.github/workflows/tests.yml`) runs on PHP 8.1/8.2/8.3, validates `composer.json`, installs
  a clean `vendor/`, and runs `tests/Security` as a required (non-continue-on-error) step; treat
  new security-relevant code the same way — add or update a test under `tests/Security` when
  fixing a vulnerability or auth bug.
- For PHP files, at minimum verify with `php -l` (lint) when a test harness isn't practical, and
  use `get_errors`/static analysis tooling available in the editor.
- For anything under Video/Encoding/Live/HLS/Storage/CDN/Payments/social-login, automated tests in
  this repo cannot fully validate the change — state plainly that manual testing against a real
  encoder, live server, storage backend, or payment/auth sandbox is required.

## Logging & Error Handling Conventions

- Use `_error_log($message, $level)` from `objects/functions.php` for all error/warning/security
  logging — never `echo`/`var_dump`/`die()` raw error details to the client.
- Severity levels: `AVideoLog::$ERROR`, `AVideoLog::$WARNING`, `AVideoLog::$SECURITY` (use
  `$SECURITY` for auth/permission violations and invalid CSRF tokens).
- Wrap risky operations in `try { ... } catch (\Throwable $th) { _error_log($th->getMessage()); }`;
  never use an empty `catch` block.
- AJAX/API endpoints must return the generic `['error' => true, 'msg' => 'An error occurred']`
  shape to the client while logging the real exception message internally — do not leak stack
  traces, file paths, or SQL text in the response.

## Files & Directories Not to Modify Without Strong Justification

- `plugin/Plugin.abstract.php`, `plugin/AVideoPlugin.php` — the plugin contract every plugin relies on.
- `updatedb/updateDb.v*.sql` and any existing `plugin/*/install/*.sql` file — history is immutable;
  add a new versioned file instead.
- `objects/mysql_dal.php`, `objects/include_config.php` — core DB layer and session/config
  bootstrapping used by every request.
- `videos/configuration.php` — per-installation secrets/config; gitignored, never commit or print
  its contents.
- `vendor/`, `node_modules/` — dependency output; change via `composer.json`/`package.json` instead
  (except the documented PayPal SDK patch mechanism).
- `.github/workflows/*.yml`, `phpunit.xml`, `composer.json` platform/require versions — changing
  CI/build behavior affects every contributor; only touch these when the task is explicitly about
  CI/dependency changes.
- Any file under a specific plugin's `install/` directory other than adding a new migration file.

## Reference Files

- `objects/mysql_dal.php` — DB layer
- `objects/user.php` — Auth and user model
- `objects/configuration.php` — Site config (`AVideoConf`)
- `objects/functionsSecurity.php` — Input sanitization, CSRF tokens
- `objects/functionsFFMPEG.php`, `objects/functionsExec.php` — FFmpeg wrappers and shell execution
- `objects/functions.php` — Global utilities
- `plugin/Plugin.abstract.php` — Plugin base class
- `plugin/AVideoPlugin.php` — Plugin loader and hook dispatcher
- `plugin/API/API.php` — Public API plugin (Bearer auth, OpenAPI docs, PII filtering)
- `plugin/Live/Live.php` — Live streaming plugin (RTMP webhook endpoints)
- `plugin/VideoHLS/` — HLS packaging/encryption/download-protection
- `admin/functions.php` — Admin UI helpers
- `view/js/script.js` — Frontend globals and modal/toast utilities

## Build & Test

```bash
# Run PHPUnit tests
composer test
# or
./vendor/bin/phpunit --configuration phpunit.xml
```

See `.github/workflows/tests.yml` for CI test configuration.
