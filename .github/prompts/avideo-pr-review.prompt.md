---
name: "AVideo PR Review"
description: "Review a pull request or code diff for AVideo. Checks code reuse, security, SQL safety, plugin compatibility, backward compatibility, UI consistency, regression risk, and SonarQube issues."
agent: agent
---

# AVideo Pull Request Review

Review the changes in this pull request (or the diff provided) against AVideo's project standards.

## Context to Gather First

Before reviewing, load the following context:
- Read the diff or changed files
- Identify which layers are affected: PHP backend, plugin, database, frontend, admin, tests
- Check whether affected files have related test coverage in `tests/`

---

## Review Checklist

### 1. Code Reuse & Duplication

- Does any new code duplicate logic already in `objects/functions*.php`, `objects/*.php`, or `plugin/*/Objects/`?
- Are there new utility functions that already exist in the codebase?
- Are there new AJAX endpoints that duplicate an existing `*.json.php` endpoint?
- Are there hardcoded values that should use existing constants or config?
- Flag any duplication with the existing file/function that should be reused instead.

### 2. Security

For every changed PHP file, verify:
- Are all `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` inputs validated and sanitized?
- Are SQL queries using `sqlDAL::readSql()` / `sqlDAL::writeSql()` prepared statements?
- Are HTML outputs escaped with `htmlspecialchars()` or `xss_esc()`?
- Are authentication checks present on all sensitive endpoints, enforced via `forbiddenPage()` (not raw `die(json_encode(...))`)?
- Are there any hardcoded credentials, tokens, or secrets?
- Are file uploads validated for MIME type (not just extension)?
- Are file paths protected against path traversal (`realpath()` + prefix check)?
- Do error responses avoid leaking stack traces or system paths?
- Does the diff duplicate an inline auth/CSRF check instead of reusing an existing helper from `objects/functionsSecurity.php`? Known reusable helpers to flag duplication against:
  - `forbiddenPage($msg, $log=false)` — 403 + optional security log + login redirect
  - `forbidIfNotPost($msg='')` — reject non-POST requests to a mutating endpoint (405)
  - `forbidIfInvalidToken($msg='')` — reject requests without a valid `globalToken` (403), wraps `isGlobalTokenValid()`
  - `forbidIfIsUntrustedRequest($logMsg='', $approveAVideoUserAgent=true)` — reject cross-origin requests (Referer/Origin check)
  - `forbidIfItIsNotMyUsersId($users_id, $logMsg='')` — reject requests targeting another user's resource
  - `getToken($timeout=0, $salt='', $videos_id=0)` / `isGlobalTokenValid()` — mint/verify the CSRF token pair
  - New mutating endpoints (GET-reachable or not named `*.json.php`) should call `forbidIfNotPost()` since `autoCSRFGuard()` in `objects/include_config.php` only arms for `POST` requests to `*.json.php` files.

### 3. SQL Safety

- Are all queries using prepared statements? (No string-concatenated SQL)
- Are new tables or columns defined with migration files in `updatedb/` or `plugin/*/install/`?
- Do migration files use `IF NOT EXISTS` / `IF EXISTS` guards?
- Are existing columns or tables being dropped or renamed? (Must be backward compatible)
- Are any tables or columns referenced that do not exist in migration history?

### 4. Plugin Compatibility

- Do changes to core files break any existing plugin hooks?
- Are any `PluginAbstract` or `AVideoPlugin` methods modified? If so, is it backward compatible?
- If a plugin is modified, is the plugin `version.json` bumped?
- If DB changes are included for a plugin, is there a new migration file in `plugin/PluginName/install/`?
- Does the change risk breaking any of the ~130+ existing plugins?

### 5. Backward Compatibility

- Are any public APIs (class methods, function signatures, endpoint response shapes) changed?
- Are removed parameters or properties guarded with defaults?
- Are any DB columns dropped or renamed without migration guards?
- Would this change affect existing stored data or configurations?

### 6. Frontend / UI Consistency

- Does the UI follow Bootstrap 5 patterns (not custom CSS for standard layout)?
- Are `modal.showPleaseWait()` / `modal.hidePleaseWait()` used for async operations?
- Are `avideoToastSuccess()`, `avideoToastError()`, `avideoAlertError()` used for feedback?
- Are any `alert()`, `confirm()`, or custom spinner implementations introduced?
- Is `webSiteRootURL` used for URLs (not hardcoded paths)?
- Is the UI consistent with the existing AVideo look and feel?

### 7. Regression Risk

- Are tests included or updated for the changed behavior?
- What is the blast radius? (How many pages, plugins, or features are affected?)
- Are there any edge cases that could break existing functionality?
- Are any database queries changed that could affect performance at scale?

### 8. SonarQube Readiness

- Are there empty `catch` blocks?
- Are there unreachable code paths or dead code?
- Are there overly complex functions (> 30 logical lines, deep nesting)?
- Are there unused variables or imports?
- Are there weak type comparisons (`==` where `===` should be used)?
- Are there security hotspots (open redirect, file inclusion, eval)?

---

## Output Format

Respond with:

1. **Summary**: 2-3 sentences on overall quality and risk level (Low / Medium / High)
2. **Blockers**: Issues that must be fixed before merging (security holes, data loss risk, breaking changes)
3. **Warnings**: Issues that should be fixed but are not merge-blocking
4. **Suggestions**: Minor improvements (style, performance, clarity)
5. **Verdict**: Approve / Request Changes / Needs Discussion
