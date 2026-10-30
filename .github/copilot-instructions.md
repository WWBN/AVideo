## Encoder Source Location

When a task requires viewing, searching, reviewing, editing, or testing Encoder code, use the
Encoder source at `D:\git\htdocs\AVideo\.compose\encoder\`. Treat it as a separate component from
the Streamer code in the repository root; do not infer Encoder behavior from similarly named files
under the root or from generated/deployed copies.
# AVideo Copilot Instructions

## Mandatory Canonical Security Policy

For every task related to security in any way—including security advisories, vulnerability reports, audits, reviews, hardening, authentication, authorization, access control, sensitive-data exposure, secrets, PII, SQL injection, XSS, CSRF, SSRF, command execution, uploads, path traversal, or a proposed security fix—read the [AVideo Security Advisory Triage prompt](prompts/avideo-security-advisory-triage.prompt.md) completely before analyzing the issue, reaching conclusions, suggesting changes, or modifying code.

Treat that prompt as the canonical and authoritative repository policy for security investigation, classification, regression analysis, fix decisions, testing, and reporting. Follow all applicable requirements from it. If another repository instruction conflicts with that prompt on a security matter, the canonical security prompt takes precedence; continue following all non-conflicting repository instructions.

Local exception authorized by the repository owner: if `avideo-security-advisory-triage.prompt.md` cannot be found after checking the referenced path and searching the repository, skip that missing prompt and continue the task using this file and the available applicable instructions, especially `avideo-security.instructions.md`. Note the missing reference; do not repeatedly ask for it or block work because of its absence. This exception does not waive the security requirements in the available instructions.

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

## Documentation Maintenance

Before completing any relevant code change in AVideo, the Encoder or its plugins, always review the affected pages in the wiki checkout `D:/git/AVideo.wiki/`, which feeds the user's AI agent. Update outdated basic or technical documentation as part of the same task without requiring another reminder; if no update is needed, state that the wiki was reviewed. Keep basic guides understandable to nontechnical users. Detailed engineering references belong in that wiki's `technical/` directory with a separate index and unique `Technical-` page names, not in this application or plugin repository. Verify facts against the relevant component/plugin revision. Do not commit, push, publish or reindex without authorization from the current task.

## Stack (verified from `composer.json` / `package.json`)

- **Backend**: PHP `^8.2` (composer platform pinned to `8.2`). CI (`.github/workflows/tests.yml`)
  runs the matrix `8.2`, `8.3`. No PHP namespaces in core files (only Composer vendor
  libraries use them). Preserve the existing non-strict style in core files; do not modernize
  unrelated code or introduce syntax requiring PHP newer than 8.2.
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
- Many plugin folders (e.g. `plugin/Subscription/`, `plugin/VideoHLS/`, `plugin/PayPerView/`) are
  listed in `.gitignore` because they have their own repositories and are distributed separately.
  When an authorized task requires a fix in one of them, inspect and correct it in its own
  repository; do not skip a required fix merely because the main repository ignores the folder.
  Check `git check-ignore -v <path>` and verify the plugin's repository root before editing.
  Report changes and validation per repository, and preserve compatibility with independently
  deployed plugin versions, including older versions where applicable.
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
- Never add AI attribution (Co-Authored-By, Generated with, 🤖) to commits or PRs.

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
- **Admin-editable FFmpeg options**: `CustomizeAdvanced->ffmpegParameters` is never put into a
  command string. `parseFFmpegConversionParameters()` turns it into allowlisted arguments
  (`getFFmpegConversionParameterRules()`); a stored value that fails is logged as SECURITY and the
  default profile is used. `convertVideoFileWithFFMPEG()` runs the argument list with `proc_open()`
  (no shell); async/remote callers get `implodeFFMPEGArgsForShell()` (each argument escaped). Admin
  saves are validated in `applyAdminPluginValues()`/`getAdminPluginFieldValidators()`; recovery CLI:
  `install/checkFFmpegParameters.php`. FFmpeg 5+ aborts with `-user_agent` on a local input. The
  Encoder image endpoints (`getImage.php`, `getImageMP4.php`) use `decodeBase64UrlParameter()`,
  `execArgv()`/`execArgvAsync()` and `-protocol_whitelist` from `getFFmpegRemoteInputArgv()`.
  Covered by `tests/Security/FFmpegParametersValidationTest.php`.
- **`$global['disableAdvancedConfigurations']`** (admin account open on purpose, e.g. demos): besides
  blocking plugin import/CreatePlugin/Backup/logs, it freezes the plugin settings listed in
  `getDangerousPluginSettings()` (`objects/dangerousConfigurations.php` — shell options, server-side
  paths and URLs). Saves keep the stored value (`admin/save.json.php`,
  `objects/pluginAddDataObject.json.php`, before the validators run); the stored value is still used
  at runtime, except the FFmpeg-option settings (CustomizeAdvanced/Rebroadcaster `ffmpegParameters`),
  which fall back to their default. Live `live_servers` add/delete are refused. Add a new setting
  that reaches a shell/path/server-side fetch to that list. Covered by
  `tests/Security/DisableAdvancedConfigurationsTest.php`.
- **Encoder admin is not the Streamer admin**: `Login::isAdmin()` on the Encoder reads the Encoder's own
  `streamers.isAdmin` (set by its installer, `install/makeAdmin.php`, or another Encoder admin); a
  Streamer admin only gets `isStreamerAdmin`. Encoder-admin settings (`formats.code` = FFmpeg command
  templates, run through `eval`/shell by design) are trusted; `saveConfig`/`isAdmin` require the
  per-session `csrfToken` (`getEncoderAdminCSRFToken()`), covered by
  `.compose/encoder/tests/encoder-admin-csrf-regression.php`.
- **`videos/.htaccess` has two writers**: `adminSecurityCheck()` copies
  `objects/htaccess_for_videos.conf` when the file's version is lower, and the Scheduler watchdog
  rewrites it from `plugin/Scheduler/htaccess.sample.txt` unless it carries exactly its own version
  (that file also holds the HLS/`.key`/xsendfile rewrites). Keep the core template's version below
  the watchdog's and change both deny lists together, or they overwrite each other.
- **Live streaming**: `plugin/Live/` implements RTMP webhook-style endpoints
  (`on_publish.php`, `on_play.php`, `on_publish_done.php`, `on_record_done.php`, etc.) called by
  the Nginx/RTMP live server component. These are integration points with an external process —
  do not change their expected request/response contract without checking the live-server side.
- **Live start, restream and recording**: `Live::_on_publish()` starts the automatic restream with
  `restreamInBackground()` (CLI `plugin/Live/restreamInBackground.php`) because the restreamer
  answers only after launching every destination (sleeps, source check, YouTube RTMPS probe), and the
  recording starts in the `on_publish` plugin hook (SendRecordedToEncoder → `controlRecordingAsync()`,
  nginx `record manual`): run synchronously, the start of the live was missing from the recording.
  The restream input must not use `-reconnect_at_eof` on an HLS source (`getRestreamInputReconnectOptions()`):
  FFmpeg retries every playlist read and the first frame arrives ~33 s late. nginx-rtmp never ends the
  playlist, so a restream FFmpeg keeps reloading it after the source stops; the cron
  `kill_ffmpeg_restream.php` kills it, and must only act on a log that is still being written by that
  process (a stale log of an earlier restream has the same input URL as the new one). A reconnection
  within 10 min reuses the same `live_transmitions_history` row; each publish produces its own FLV and
  recorded video. `startRestream()` stops the destination's previous FFmpeg only after taking its start
  lock and, in separate mode, only for that destination id, so a reconnection restart replaces each
  running relay but never kills the VideoPlaylistScheduler relay (id 0). Covered by
  `tests/Unit/LiveOnPublishRestreamInBackgroundTest.php`, `LiveRestreamProfilesTest.php`,
  `tests/live-restream-kill-stale-log-regression.php` and `tests/live-restream-start-concurrency-regression.php`
  (Linux, isolated container).
- **WebRTC2RTMP server** (`plugin/WebRTC/`, closed-source binary on port 3000, started by the
  Scheduler cron through `WebRTC::executeEveryMinute()`): it reads
  `/etc/letsencrypt/live/<domain>/fullchain.pem` only at startup, so after certbot renews it keeps
  serving the old certificate and browsers fail with "Unable to connect" once that one expires.
  `restartIfCertificateIsOutdated()` compares the served certificate with the one on disk and restarts
  only when the server has no child processes (its ffmpeg = a WebRTC live), at most once per hour.
  `startServer()` checks and launches under `videos/WebRTC2RTMP.lock` and also refuses while a process
  of the same binary is still starting (port not open yet), so concurrent callers (cron, `quickGoLive`,
  `tools/`) never start a second instance. Its `WebRTC2RTMP.json` heartbeat is written by the binary.
  `htop` lists the binary's threads (and its `sh -c` wrapper) as separate lines with the same
  command — check `ss -ltnp` before assuming there are duplicate instances.
- **YPTSocket**: the default server (`socketIO = true`) is the Node binary from the public `WWBN/AVideo-Socket`
  repo (`server.node.php` downloads `dist/yptsocket`), not the PHP `Message*.php` servers. It relays client messages
  unfiltered, keeping the client's `type` and `webSocketToken`, so the PHP-side filters (e.g.
  `removeAutoEvalCodeOnHTMLRecursive()`) do not apply. In `script.js` a Socket.IO `MSG_BATCH` counts as the server
  batch only without a `webSocketToken`, and `autoEvalCodeOnHTML` is evaluated only on the PHP WebSocket path
  (GHSA-vw5g-hfpm-2m4g). If the Node server ever stops relaying `webSocketToken`, it must also stop relaying a
  client-chosen `MSG_BATCH`. Covered by `plugin/YPTSocket/tests/client.test.js` (`node --test`).
- **HLS / VideoHLS**: HLS packaging, encryption keys, and download protection
  (`plugin/VideoHLS/`) gate access to on-demand and live content; do not weaken key/token checks
  when touching this path.
- **Storage & CDN**: multiple storage backends are supported (`storage/data/` local disk,
  `AWS_S3`, `Blackblaze_B2`/`gliterd/backblaze-b2`, `FTP_Storage`, `bunnycdn/storage`, `YPTStorage`,
  `CDN` plugin). Path/URL-building helpers already exist per backend — reuse them rather than
  constructing storage paths/URLs manually, since a wrong path can silently break playback only on
  non-local storage.
- **Stripe single payments** (`plugin/StripeYPT/getIntent.json.php` with `singlePayment` metadata):
  the browser confirms the card (`confirmCardPayment`) and the wallet is credited only by the
  `charge.succeeded` webhook (`StripeYPT::processSinglePaymentIPN()`), so the server never sees the
  success inside `getIntent()`. The per-user duplicate/idempotency guard in
  `StripeYPT::getSinglePaymentIntentLocked()` (state files under `videos/stripe-payment-locks/`)
  must take its timestamps from Stripe (`latest_charge.created`), never from when the server first
  observed a status — otherwise a returning customer is blocked days later. Reading the charge needs
  the restricted key to have **Charges: Read** (the rest of the flow only needs PaymentIntents);
  without it the guard logs a warning and degrades to the 5-minute observation fallback. After those
  5 minutes, a paid intent whose wallet credit has not committed (`YPTWallet::isStripePaymentCredited()`)
  still blocks a new charge for up to 3 days (Stripe's webhook retry window) unless refunded; the same
  selection gets the paid intent back (`already_paid`, the page waits for the credit). Webhook events are
  snapshots: `processSinglePaymentIPN()` re-reads the Charge and credits only the part not refunded.
  PPV movie checkouts (PayPerView with `completeWalletCheckout()`) get `ppv_auto_rent` metadata and the
  webhook rents the movie in the same wallet transaction, inside `runPaymentStep()`: a failed rental
  undoes only the debit/split/rental and keeps the card credit (the customer finishes with Pay Now), so
  "credited" does not mean "rented". It never rents an unverified (no Charges: Read) or partially refunded
  charge. Covered by `tests/Unit/StripeSinglePaymentCheckoutTest.php`,
  `tests/Security/PaymentWebhookIdempotencyTest.php` and the MySQL integration test.
- **Payment webhooks (Stripe/Razorpay/PayPal recurring IPN `plugin/PayPalYPT/ipn.php`)**: verified deliveries go through
  `YPTWallet::processPaymentOnce()` (global `GET_LOCK` + transaction + zero-value `paymentProcessed`
  markers in `wallet_log`, hidden from the user list by `value != 0`). An exception rolls everything
  back and the IPN answers 500 so the provider retries; a `false` from `processSubscriptionIPN()` means
  "not an AVideo payment" and must stay HTTP 200 — `isSubscriptionPayment()` matches every
  `charge.succeeded` without `singlePayment`, including `setUpPayment()` charges that have no
  customer/metadata. Wrap the subscription renewal in `runPaymentStep()` (SQL savepoint): a failed
  renewal is undone alone and the credit is kept, otherwise a deterministic failure retries forever.
  Never use `mysqli::rollback($flags, $name)` for savepoints (it rolls back the whole transaction).
  Inside the payment a failed wallet-log insert aborts it, so `WalletLog` setters cut values to the column
  sizes (`description` VARCHAR(255), `json_data` TEXT) — there is no `sql_mode` override and strict mode is
  the MySQL/MariaDB default. Covered by `tests/Security/PaymentWebhookIdempotencyTest.php`,
  `RazorpayVerifiedWebhookTest.php` and the opt-in real-database
  `tests/Integration/PaymentWalletMySQLIntegrationTest.php` (`AVIDEO_PAYMENT_MYSQL_TESTS=1`).
- **PayPal (`plugin/PayPalYPT/`, REST SDK 1.6.4 = Payments v1 + Billing Agreements v1, both deprecated; "V2" files
  are still Billing Agreements)**: every PayPal purchase is a `PayPalYPT_checkout` row (buyer, purpose, catalog
  selection, server price, environment/app hash) created before calling PayPal. The return URL, webhook, IPN and
  `checkoutStatus.json.php` all call `PayPalYPT::reconcileCheckout()`, which re-reads the payment with this site's
  credentials and credits only a completed sale through `PayPalPaymentProcessor` (`processPaymentOnce()` keyed by
  the sale ID = IPN `txn_id` = webhook `resource.id`; rental/event/renewal in `runPaymentStep()`). An approved
  agreement alone grants nothing. Webhook signatures are verified by POSTing the raw body + saved `webhookId` to
  `/v1/notifications/verify-webhook-signature` (`VerifyWebhookSignature` does not exist in SDK 1.6.4). Never
  create, list-to-create or delete webhooks from a payment path: only the admin tool (`configuration.php`) does,
  and it never deletes. The default `ClientID` is a shared public sandbox app with other sites' webhooks.
  Covered by `tests/Unit/PayPalCheckoutTest.php`, `PayPalConfigurationTest.php`,
  `tests/Security/PayPalConfigurationEndpointTest.php` and `PaymentWebhookIdempotencyTest.php`.
- **BTCPayments (`plugin/BTCPayments/`)** never calls BTCPay directly: invoices are created (`invoice.json.php`) and
  re-verified by the webhook (`invoice.verify.json.php`) through the BTC marketplace `BTC_MARKETPLACE_URL`
  (streamphp.com, a separate repository). Those URLs carry the site's `BTCMarketPlaceKey`: log them only redacted and
  never put them in a message shown to the buyer. AVideo reads the marketplace answer with a plain `json_decode()`, so a
  PHP notice printed by the marketplace breaks invoice creation and payment crediting on every site at once (issue
  #10655, PHP 8.5 `curl_close()` deprecation): fix the marketplace output, do not loosen the parsing here.
  The buyer flow mirrors the PayPal one-time checkout: the `YPTWalletBTC` button POSTs to its `requestPayment.json.php`,
  `BTCPayments::startCheckout()` prices wallet/PPV/PPV Live on the server (balance first, reuses an unpaid invoice,
  refuses a second one while a payment is confirming; purpose/selection go in the invoice metadata), and
  `plugin/BTCPayments/checkout.php` shows the address/QR and polls `checkoutStatus.json.php`, which reconciles through
  the marketplace. The marketplace notifies the site only for `InvoiceReceivedPayment`, and for `InvoiceSettled` only after
  its payout to the site's wallet succeeds (never for expiry), so crediting must not depend on that notification.
  The rental/event runs in `runPaymentStep()` inside `processVerifiedInvoice()` (kept inline: tests extract that method),
  and its result is saved as `avideo_fulfillment` in `btc_payments.json`. Pass `$notifyUser = false` from polling,
  or the socket message re-triggers the page. Covered by `tests/Security/BTCPaymentsMarketplaceErrorTest.php`,
  `PaymentWebhookIdempotencyTest.php` and `tests/Unit/BTCPaymentsCheckoutTest.php`. A real testnet payment on the local
  vlu.me stack (Daniel's Electrum wallet) follows `.claude/btcpay-test/README.md` (local-only tools, not in git).
- **Translations (`locale/*.php`)**: never generate these files with ad-hoc code or add another `.php` file
  to `locale/` — `?lang=`, `/api/locale` and the language selector include/list every file there. All
  reads/writes go through `objects/LocaleTranslator.php` (tokenizer parse, no execution; rewrites sorted,
  one entry per text, single-quoted literals, validated and atomically renamed). `en_US` is the catalog of
  every text (translated to itself); `tests/Unit/PersianLocaleTest.php` requires `fa_IR` to have exactly
  its keys in the same order. Admin UI `locale/index.php` (Claude API key in
  `$global['localeTranslatorApiKey']`), CLI `install/translateLocale.php` (`--normalize`, `--translate`,
  `--prune [--dry-run]`: removes only texts not given to `__()`, not a letter-case variant, not any
  quoted string in PHP/JS/HTML/JSON/SQL (≤2 MB files) and not matching a runtime-built call such as
  `__("{$i} Month" . ...)`; the mobile app also reads these files through `/api/locale`;
  does not load `videos/configuration.php`, so no database; key from `ANTHROPIC_API_KEY`, otherwise
  `claude -p` with the runner's Claude Code login, never from the web server). `claude -p`: no `--bare` (it ignores the login), `--safe-mode` when
  available, empty temp cwd, `ANTHROPIC_API_KEY`/`ANTHROPIC_AUTH_TOKEN` removed from its environment
  (they override the login in print mode), only `.exe`/binary (a `claude.cmd` shim would let cmd.exe
  mangle `%s`), and stdout to a file (`stream_select()` fails on `proc_open` pipes on Windows).
  Covered by `tests/Unit/LocaleTranslatorTest.php`.
- **Changing the site address** (`install/updateStreamerURL.php`, helpers in `install/updateStreamerURL-functions.php`):
  never a plain find-and-replace. The old host is replaced only as a site address (after `//`, `\/`, `\\/` or
  `%2F%2F`, a whole/quoted value, Let's Encrypt paths once the new certificate exists); e-mails, folders, subdomains,
  serialized PHP, payment/log/cache tables and other Streamers in the Encoder are kept. Pitfalls found on real data:
  older Live `object_data` escapes slashes twice and is not valid JSON (it still loads); `latin1` columns with
  accents never match a byte comparison (compare as `utf8mb4_bin`); old installers wrote `webSiteRootURL` twice
  (an `if (false && ...)` expression plus the text): change only the text. Outside the database, `deleteALLCache()`
  does not remove `videos/permanentCache/getVideosPaths_*.tmp` (served with the old video URLs while the cache is
  empty), and WebRTC2RTMP keeps the old domain until it restarts. Details in the wiki
  `technical/Technical-Maintenance.md`; covered by `tests/Unit/UpdateStreamerURLTest.php`.
- **Test fixtures that eval real code**: an included fixture shares the includer's variable scope. Never
  use generic top-level names (`$root`, `$path`) in a fixture, and never let a test or simulation write
  under the repository (`videos/configuration.php` is real, gitignored, and holds the site secrets).
- **Outbound TLS verification**: connections to admin-configured services (Encoder queue, SMTP,
  WordPress login, CloneSite) verify certificates only when `shouldVerifySSLCertificates()` is true
  (CustomizeAdvanced `verifySSLCertificates`, or `$global['verifySSLCertificates']`, off by default):
  the Docker stack and many installs use self-signed certificates. The fixed public BTC marketplace
  uses `shouldVerifySSLCertificates(true)`. Do not hardcode `verify_peer => true` in those callers.
- **Publishing to YouTube** (`plugin/SocialMediaPublisher/`, not `plugin/YouTubeAPI/` — that one is only a
  search gallery; `objects/youtubeUpload.json.php` is an old, unused path): OAuth runs on the
  `restream.ypt.me` broker (`/d/git/htdocs/Restreamer`), which holds the Google client secret and returns
  the token by `postMessage`; accept it only from the broker origin (`isRestreamerBrokerOrigin()` in
  `view/js/script.js`). Tokens live in `publisher_user_preferences.json` and are renewed only through
  `SocialMediaPublisher::revalidateTokenAndSave()`: give `Google_Client` the access token alone (with a
  `refresh_token` it tries to refresh with a secret this site lacks), and never return/log that JSON
  (`getPublicConnectionRow()`). `YouTubePublisher` uploads in a background CLI worker
  (`youtubeUploadWorker.php`, resumable session, cancel/resume, cron recovery); the job is the `job` object in
  `publisher_video_publisher_logs.details`, error texts are stored as codes. Covered by
  `tests/Unit/YouTubePublisherTest.php`, `tests/Security/SocialMediaPublisherTokenExposureTest.php` and
  `tests/social-media-publisher-message-origin.test.cjs`. Real uploads need manual testing with a connected account.
- **Encoder monitor alerts**: the Encoder has no users table and no mailer. Its every-minute cron
  (`.compose/encoder/install/cron.php` → `EncoderMonitor`) detects slow/failed/stuck jobs and asks
  the job's own Streamer to e-mail the owner via `objects/aVideoEncoderAlert.json.php`
  (`EncoderAlert` builds the mail; the recipient always comes from the Streamer DB). Encoder
  callback responses must never contain `videos_id`/`video_id`/`video_id_hash` unless intended:
  `Encoder::sendToStreamer()` saves those into the job (and would insert a row for the unsaved job
  object used by system alerts). The cron belongs to the Encoder installation (one Encoder serves
  many Streamers, in any folder): install it with `install/installCron.php` from the Encoder itself,
  never from a Streamer's `deploy/` crontab, and run it as the web server user, not root. The
  `queue_stalled` admin alert also needs the queue idle (jobs waiting, nothing processing) for
  `queueIdleMinutes` across runs (state `queue_idle_since`): the run right after a long conversion
  ends always sees nothing processing, and jobs waiting behind a conversion are not a stall. Output
  files are named `avideoTmpFile_<job>_streamers_id_<streamer>_*`, and "Renew site access" logs in
  with the stored username, so a job whose site account was renamed cannot be renewed. Covered by
  `.compose/encoder/tests/encoder-monitor-regression.php` and `tests/Unit/EncoderAlertTest.php`.
- **Seek bar thumbnails (`plugin/VideoThumbnails/`, own GitLab repo, shipped encrypted)**: the sprite
  `<filename>_thumbsSprit.jpg` comes from the Encoder `getSpiritsFromVideo/<base64 url>/...`, which answers
  the "creating images" placeholder (147137 bytes, same 1500x840 as a real sprite, so only its size tells it
  apart) while `createSpiritsFromVideo.php` runs, deletes the finished sprite once served, and (since
  2026-10-26) answers empty for 1 hour after a failed run. The Streamer never saves the answer from the page
  request: `startSpriteWorker()` (at most every 10 min per video) starts `createSprite.php`, which polls with
  the same video URL (the Encoder keys the job on it) and saves only a valid image (`isValidSpriteContent()`).
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
- Any request carrying `user`+`pass` is already logged in before the endpoint code runs:
  `AVideoPlugin::getStart()` (every request) → `CustomizeUser::getStart()` → `User::loginFromRequest()`.
  So `User::isLogged()` cannot tell "this request's credentials" from "an ambient cookie session"; use
  `$global['loggedInFromRequestCredentials']` (set only after the request's password was actually
  checked: a fresh login, or a cookie session whose user/pass verify to that same session user), as the
  CSRF guard in `plugin/API/set.json.php` does. Only the API entry points (`plugin/API/get|set.json.php`
  set `$global['switchUserFromRequestCredentials']`) replace a cookie session with another account's
  verified user/pass (the FlixHouse app endpoints `plugin/FlixHouse/api/*.json.php` opt in the same way); normal pages never switch accounts.
  `Video::getImageFromFilename()` calls `header_remove('Set-Cookie')`, so a response that lists videos never carries
  a new session cookie: app clients must send user/pass on every call. `USER_LOGGED` is `0`, so code inside
  `if ($response) { switch ... case self::USER_LOGGED` in `loginFromRequest()` never runs. Covered by `tests/api-set-csrf-guard-regression.php`.
- Every password check in `User::login()` (form, API, user/pass in any URL, remember-me cookie) has a
  failed-attempt penalty: 10 failures per account+IP and 100 per IP in 15 min (overridable with
  `$global['failedLoginMaxPerAccount']` / `failedLoginMaxPerIP`), successes are refunded, and while
  blocked `login()` returns `User::TOO_MANY_FAILED_ATTEMPTS` without checking the password. It must not
  end the request: Encoder callbacks still authenticate through `video_id_hash` after a failed user/pass.
  Covered by `tests/login-failed-attempts-regression.php`.

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

- Backend dependencies are managed by Composer (`composer.json`, PHP `^8.2` platform). Note the
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
- Write PHP backend tests in PHP using the existing PHPUnit suite. Do not introduce Python test runners or Python dependencies to test PHP code.
- Run relevant tests locally before calling a change complete:
  ```bash
  composer test                     # full suite (phpunit.xml)
  composer test:unit                # tests/Unit only
  vendor/bin/phpunit tests/Security  # security suite — CI treats this as critical/must-pass
  composer test:filter -- --filter=SomeTestName
  ```
- CI (`.github/workflows/tests.yml`) runs on PHP 8.2/8.3, validates `composer.json`, installs
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
