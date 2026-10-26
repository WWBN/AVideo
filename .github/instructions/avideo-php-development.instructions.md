---
name: "AVideo PHP Development"
description: "Use when writing, editing, or reviewing PHP files in AVideo. Covers coding style, error handling, AJAX endpoints, input validation, output escaping, and class/function reuse patterns specific to the AVideo codebase."
applyTo: "**/*.php"
---

# AVideo PHP Development Guidelines

## Core Principle: Search Before Writing

Before adding any PHP code, search the repository for existing logic:
- `objects/functions*.php` — global utility functions
- `objects/mysql_dal.php` — database access layer
- `objects/functionsSecurity.php` — input sanitization
- `objects/user.php` — authentication and user model
- `objects/configuration.php` — site configuration (`AVideoConf`)
- `admin/functions.php` — admin form helpers

Do not duplicate logic that already exists. Do not invent function names.

---

## PHP Version & Style

- Target PHP 8.2+. Preserve the style of the surrounding code; do not modernize unrelated code or introduce syntax requiring PHP newer than 8.2.
- Do not use PHP namespaces in core files. The core project does not use namespaces; only Composer-loaded vendor libraries do.
- Match the coding style of nearby files exactly: indentation, brace placement, spacing.
- Use `#[\AllowDynamicProperties]` on classes that set dynamic properties (follow existing pattern).

---

## Error Handling

Always use the project error logging function:

```php
try {
    // operation
} catch (\Throwable $th) {
    _error_log($th->getMessage());
}
```

Use severity levels when appropriate:
```php
_error_log($message, AVideoLog::$ERROR);
_error_log($message, AVideoLog::$WARNING);
_error_log($message, AVideoLog::$SECURITY);
```

**Do not:**
- `echo` or `var_dump` error messages to end users
- Expose stack traces, file paths, or SQL queries in user-facing responses
- Use `die()` or `exit()` with raw error messages
- Use empty `catch` blocks

---

## Authentication & Permission Checks

Always validate permissions at the top of sensitive files/endpoints using `forbiddenPage()` from `objects/functionsSecurity.php`. It auto-detects JSON vs HTML context, sets HTTP 403, optionally logs, and redirects unauthenticated users to login:

```php
// Require any logged-in user
if (!User::isLogged()) {
    forbiddenPage('Login required');
}

// Require admin
if (!User::isAdmin()) {
    forbiddenPage('Permission denied');
}

// Require specific capability
if (!User::getInstance()->getCanUpload()) {
    forbiddenPage('Upload not allowed');
}

// With logging (second parameter = true)
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
```

`forbiddenPage()` signature: `forbiddenPage($message = '', $logMessage = false, $unlockPassword = '', $namespace = '', $pageCode = '403 Forbidden')`
- In AJAX/JSON requests: sends `{"error":true,"msg":"...","forbiddenPage":true}` with HTTP 403
- In HTML requests: includes `view/forbiddenPage.php`
- When not logged in: redirects to login page via `gotToLoginAndComeBackHere()`

**Do not** use `die(json_encode(['error' => true, ...]))` for access control — it skips HTTP status codes, logging, and the login redirect.

Core auth methods (from `objects/user.php`):
- `User::isLogged()` — is the user authenticated?
- `User::isAdmin()` — is the current user an admin?
- `User::getId()` — get the current user's DB ID
- `User::getInstance()` — get the full `User` object (check `objects/user.php` for all instance methods)

---

## AJAX Endpoints

Name AJAX endpoints `*.json.php`. Always return a consistent JSON structure:

```php
<?php
// Standard AJAX endpoint pattern
require_once '../videos/configuration.php';
header('Content-Type: application/json');

// Auth checks ALWAYS before try/catch
// forbiddenPage() calls exit() — placing it inside try/catch is a security risk
if (!User::isLogged()) {
    forbiddenPage('Login required');
}
// Admin-only: if (!User::isAdmin()) { forbiddenPage('Permission denied', true); }

$response = ['error' => false, 'msg' => ''];

try {
    // validate inputs
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    if (!isGlobalTokenValid()) {
        forbiddenPage('Invalid or missing CSRF token', true);
    }
    // do work
    $response['msg'] = 'Success';
} catch (\Throwable $th) {
    $response['error'] = true;
    $response['msg'] = 'An error occurred';
    _error_log($th->getMessage(), AVideoLog::$ERROR);
}

echo json_encode($response);
```

- Use `*.edit.json.php` for edit/save operations that modify data.
- Never mix HTML output with JSON output in the same endpoint.
- Set `header('Content-Type: application/json')` immediately after `require_once` — before any conditional logic or output.
- For state-changing POST endpoints, include a `globalToken` value in the request and validate it with `isGlobalTokenValid()`.
- For read-only JSON endpoints, keep auth/authorization checks and input validation; CSRF is required when the endpoint changes state or returns private/sensitive data.
- Return specific user-facing validation messages only for expected validation failures. Log unexpected exceptions internally and return a generic message.

---

## Input Validation & Sanitization

Use `objects/functionsSecurity.php` filters — do not roll your own:

```php
// Standard input reading
$videoId = (int) filter_input(INPUT_POST, 'videos_id', FILTER_SANITIZE_NUMBER_INT);
$title   = filter_input(INPUT_POST, 'title', FILTER_DEFAULT);
$title   = strip_tags($title);
$title   = htmlspecialchars($title, ENT_QUOTES);

// Use xss_esc() for user-controlled strings going into HTML
echo xss_esc($userInput);
```

Security filter variables from `functionsSecurity.php`:
- `$securityFilter` — general string filter
- `$securityFilterInt` — integer filter
- `$securityRemoveSingleQuotes` — removes single quotes

For video hash/ID conversion, use `videosHashToID($hash)` — do not implement your own.

---

## Output Escaping

- **HTML context**: `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` or `xss_esc($value)`
- **JSON context**: `json_encode($value)` (escapes automatically)
- **SQL context**: use prepared statements via `sqlDAL` — never string-interpolated SQL
- **URL context**: `urlencode($value)`

---

## Configuration Access

Use the `AVideoConf` singleton — do not read the DB manually for site settings:

```php
$conf = AVideoConf::getConf(); // loads from configurations table (id=1)
$siteTitle = $conf->getWebSiteTitle();
$encoderURL = $conf->getEncoderURL();
```

Global path variables (set in `videos/configuration.php`):
```php
$global['systemRootPath']   // absolute filesystem path to AVideo root
$global['webSiteRootURL']   // site URL including trailing slash
$global['mysqli']           // active MySQLi connection
$global['logfile']          // log file path (use _error_log() instead of writing directly)
```

---

## Functions & Helpers

Key utility functions to reuse (check `objects/functions*.php` before writing new logic):

| Function | File | Purpose |
|---|---|---|
| `_error_log($msg, $level)` | functions.php | Structured logging |
| `_empty($val)` | functions.php | Custom empty check |
| `videosHashToID($hash)` | functionsSecurity.php | Video hash to DB ID |
| `humanFileSize($bytes)` | functionsFile.php | Human-readable file sizes |
| `make_path($path)` | functionsFile.php | Create directory if missing |
| `isAVideoEncoder()` | functionsAVideo.php | Detect encoder requests |
| `mysqlBeginTransaction()` | functionsMySQL.php | Start DB transaction |
| `mysqlCommit()` | functionsMySQL.php | Commit DB transaction |
| `mysqlRollback()` | functionsMySQL.php | Rollback DB transaction |

---

## Class Conventions

- Extend `ObjectYPT` for database-backed entities that follow the save/load pattern.
- Use static methods for "current user" lookups (following `User::isAdmin()` pattern).
- Do not add a new class if existing classes already cover the need.
- Do not create standalone helper classes when a global function already exists.

---

## Do

- Validate all inputs at the top of each endpoint
- Escape all outputs in the appropriate context
- Use `sqlDAL` for all database access
- Use `_error_log()` for all error/warning messages
- Return `['error' => false/true, 'msg' => '...']` from AJAX endpoints
- Reuse `AVideoConf`, `User`, `Video`, `Category` objects
- Follow existing file naming conventions (`*.json.php`, `*.edit.json.php`)
- Match the coding style of the file you are editing

## Do Not

- Invent function names — search `objects/functions*.php` first
- Use raw `mysqli_query()` directly — use `sqlDAL`
- Echo raw error messages to users
- Use PHP namespaces in core files
- Use syntax requiring PHP newer than 8.2
- Create new database tables without a migration file
- Mix HTML and JSON output in a single endpoint
