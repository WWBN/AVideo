---
name: "AVideo Security"
description: "Use when writing any PHP code in AVideo. Covers input validation, output escaping, authentication checks, SQL injection prevention, XSS prevention, file upload security, path traversal prevention, and secrets management."
applyTo: "**/*.php"
---

# AVideo Security Guidelines

## Critical: Never Skip These Checks

Every new endpoint or form handler must apply all relevant checks from this file. Security is not optional.

---

## Authentication & Authorization

**Every** endpoint that modifies data or returns private information must validate permissions using `forbiddenPage()` from `objects/functionsSecurity.php`.

`forbiddenPage()` automatically:
- Sets HTTP 403 status
- In JSON/AJAX requests: returns `{"error":true,"msg":"...","forbiddenPage":true}`
- In HTML requests: includes `view/forbiddenPage.php`
- When not logged in: redirects to login via `gotToLoginAndComeBackHere()`

```php
// Require any logged-in user
if (!User::isLogged()) {
    forbiddenPage('Login required');
}

// Admin-only
if (!User::isAdmin()) {
    forbiddenPage('Permission denied');
}

// Capability check
if (!User::getInstance()->getCanUpload()) {
    forbiddenPage('Upload not allowed');
}

// With internal logging (second parameter = true)
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
```

**The most common security mistake in AVideo plugins — do not repeat it:**

```php
// WRONG — the pattern found throughout the codebase
// Problems: no HTTP 403, no internal log, no login redirect
if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

// ALSO WRONG — setting http_response_code() manually is not enough
// forbiddenPage() also logs, redirects unauthenticated users, and includes view/forbiddenPage.php
if (!User::isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => true, 'msg' => 'Not authorized']));
}

// CORRECT — forbiddenPage() handles status code, audit logging, and login redirect atomically
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true); // true = write to security log
}
```

**The second most common mistake — auth check inside try/catch:**

```php
// WRONG — auth check inside try/catch; a misconfigured catch could swallow the exit
$response = ['error' => false, 'msg' => ''];
try {
    if (!User::isAdmin()) {
        forbiddenPage('Permission denied'); // WRONG: inside try/catch
    }
    // ...
} catch (\Throwable $th) { ... }

// CORRECT — auth check always before the try/catch block
if (!User::isAdmin()) {
    forbiddenPage('Permission denied'); // CORRECT: before try/catch
}
$response = ['error' => false, 'msg' => ''];
try {
    // ...
} catch (\Throwable $th) { ... }
```

Auth functions live in `objects/user.php`:
- `User::isLogged()` — authenticated?
- `User::isAdmin()` — admin role?
- `User::getId()` — current user's DB ID
- `User::getInstance()->getCanUpload()` — upload capability
- `User::getInstance()->getCanStream()` — stream capability

**Ownership check**: when acting on a resource, always verify the current user owns it:
```php
$videoOwnerId = Video::getUsersId($videoId);
if (!User::isAdmin() && $videoOwnerId !== User::getId()) {
    forbiddenPage('Permission denied');
}
```

**Video visibility/password check**: any endpoint that returns a video's data, metadata, or
lets a user attach/reference a `videos_id` (comments, likes, bookmarks, gallery images, media
session, playlists, "favorite", etc.) must verify the caller can actually see that video —
group restrictions AND password protection — not just that the video exists. Reuse the
existing helpers in `objects/functionsSecurity.php` instead of re-implementing the check:

```php
// Endpoint that should die/redirect on failure (most .json.php endpoints)
forbiddenPageIfCannotWatchVideo($videos_id);

// Function that must return a bool instead of dying (e.g. inside a reusable static helper)
if (!canWatchVideoIncludingPassword($videos_id)) {
    return false;
}
```

Both wrap `User::canWatchVideoWithAds($videos_id)` (group/plugin-based visibility) plus
`CustomizeUser::videoPasswordIsGood($videos_id)` (password-protected videos, only when that
plugin is enabled). Do not call `User::canWatchVideo()`/`canWatchVideoWithAds()` alone when a
password-protected video is in scope — it does not check `video_password`.

---

## Input Validation & Sanitization

Use filters from `objects/functionsSecurity.php`. Never trust `$_GET`, `$_POST`, `$_REQUEST`, or `$_COOKIE` directly.

```php
// Integer IDs — throw inside try/catch so the AJAX wrapper returns the error
$videoId = (int) filter_input(INPUT_POST, 'videos_id', FILTER_SANITIZE_NUMBER_INT);
if ($videoId <= 0) {
    throw new Exception('Invalid video ID');
}

// Strings - strip tags and HTML-encode
$title = filter_input(INPUT_POST, 'title', FILTER_DEFAULT);
$title = strip_tags($title);
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

// URL - validate format
$url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
if (!$url) {
    throw new Exception('Invalid URL');
}

// Video hash → ID (never use raw hash in SQL)
$videoId = videosHashToID($hash); // returns integer
```

For input errors in AJAX endpoints, throw inside the `try/catch` block so the standard `['error' => true, 'msg' => '...']` wrapper handles the response. Reserve `forbiddenPage()` for access-control violations.

Project security filters available (`objects/functionsSecurity.php`):
- `$securityFilter` — general string sanitizer
- `$securityFilterInt` — integer sanitizer
- `$securityRemoveSingleQuotes` — removes single quotes

---

## SQL Injection Prevention

**Always** use `sqlDAL` prepared statements. Never concatenate user input into SQL strings.

```php
// CORRECT - prepared statement
$sql = "SELECT id, title FROM videos WHERE users_id = ? AND status = ?";
$res = sqlDAL::readSql($sql, 'is', [$userId, $status]);

// WRONG - never do this
$sql = "SELECT id FROM videos WHERE users_id = " . $_POST['id']; // VULNERABLE
```

Never use:
- `$_GET`/`$_POST` values directly in SQL strings
- `sprintf()` with user values in SQL
- String interpolation (`"... $userValue ..."`) in SQL

---

## Output Escaping / XSS Prevention

Match the escaping method to the output context:

```php
// HTML attribute or tag content
echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// In AVideo utility (wraps htmlspecialchars)
echo xss_esc($value);

// JSON response (json_encode escapes automatically)
echo json_encode(['data' => $userValue]);

// JavaScript string (double-escaping required)
echo json_encode($value); // safe to embed in JS context
```

Do not echo user-supplied values without escaping into HTML, JavaScript, or CSS contexts.

---

## Secrets & Credentials

- **Never hardcode** API keys, tokens, passwords, or secrets in source files
- Read them from the database via `AVideoConf::getConf()->getPropertyName()` or `$global` config
- Never log secrets with `_error_log()`
- Never include secrets in AJAX responses or HTML output
- Do not commit `videos/configuration.php` (it is gitignored)

---

## File Upload Security

AVideo has existing upload handlers. If adding new upload functionality:

```php
// Verify MIME type — do NOT rely on file extension alone
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($_FILES['upload']['tmp_name']);
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $allowed, true)) {
    throw new Exception('Invalid file type');
}

// Store outside webroot or in a safe directory
// Never store uploads where they can be executed as PHP
$safePath = $global['systemRootPath'] . 'videos/';
```

Rules:
- Validate MIME type server-side, not just extension
- Never store uploads in directories where PHP execution is enabled
- Sanitize filenames before storing — use `make_path()` + sanitized basename
- Check `file_upload_max_size()` (from `objects/functionsFile.php`) before accepting uploads

---

## Path Traversal Prevention

When building file paths from user input:

```php
// Dangerous - user could pass ../../etc/passwd
$file = $global['systemRootPath'] . $_GET['file'];     // VULNERABLE

// Safe - use basename() and whitelist
$filename = basename($_GET['file']);
$allowed = ['thumbnail', 'poster', 'preview'];
if (!in_array($filename, $allowed, true)) {
    forbiddenPage('Path not allowed');
}
$file = $global['systemRootPath'] . 'videos/' . $filename . '.jpg';
```

Use `realpath()` to resolve the path and verify it starts with the expected directory:
```php
$resolved = realpath($path);
if ($resolved === false || strpos($resolved, $global['systemRootPath']) !== 0) {
    forbiddenPage('Path not allowed');
}
```

---

## Error Leakage

Do not expose internal details to users:

```php
// WRONG - leaks path and query details
die($th->getMessage());

// CORRECT - log internally; the AJAX wrapper returns the generic message
_error_log($th->getMessage(), AVideoLog::$ERROR);
throw new Exception('An error occurred'); // caught by the try/catch in the endpoint wrapper
```

Log levels (`AVideoLog`):
- `AVideoLog::$ERROR` — unexpected errors
- `AVideoLog::$WARNING` — handled but noteworthy issues
- `AVideoLog::$SECURITY` — security events (invalid tokens, permission violations, etc.)

Log security events with `AVideoLog::$SECURITY`:
```php
_error_log('Unauthorized access attempt by user ' . User::getId(), AVideoLog::$SECURITY);
```

---

## CSRF Protection

For forms and state-changing AJAX calls, use the existing token helpers from `objects/functions.php`:

```php
// PHP: embed token in the form (TTL in seconds)
<input type="hidden" name="globalToken" value="<?php echo getToken(300); ?>">

// PHP: validate token before processing
if (!isGlobalTokenValid()) {
    forbiddenPage('Invalid or missing CSRF token');
}
```

- `getToken($ttl)` - generates a time-limited encrypted token
- `isGlobalTokenValid()` - validates the submitted `globalToken` request value
- Do not implement a custom CSRF mechanism — these functions are the established pattern
- Only accept state-changing operations via POST — never via GET

---

## Session Security

Session handling is fully configured in `objects/include_config.php`. Do not override it.

- Do not store sensitive data (passwords, tokens, private keys) in `$_SESSION` beyond what the framework already stores
- Do not call `session_regenerate_id()` without first tracing the full session flow — it can break login state
- The session name is derived from `md5($global['systemRootPath'])` — do not change it
- Do not start a second session or call `session_start()` directly — the framework manages this

---

## Do

- Validate auth/permissions at the top of every endpoint
- Use `sqlDAL` prepared statements for all queries
- Escape all output with `htmlspecialchars()` or `xss_esc()`
- Validate MIME type for file uploads
- Use `realpath()` + prefix check for file path operations
- Log security events with `AVideoLog::$SECURITY`
- Read secrets from config/DB — never hardcode them

## Do Not

- Trust any `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` value without validation
- Concatenate user input into SQL strings
- Echo raw error messages or stack traces to users
- Store uploads in PHP-executable directories
- Hardcode credentials, tokens, or API keys
- Use empty `catch` blocks that silently swallow errors
- Log passwords, tokens, or private keys with `_error_log()`
