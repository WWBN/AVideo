When the issue concerns the Encoder, inspect and edit its source at
`D:\git\htdocs\AVideo\.compose\encoder\`. Treat it as a separate component from the Streamer
source in the repository root.
---
name: "AVideo Debug"
description: "Debug an issue in AVideo using logs, existing code analysis, and minimal safe fixes. Use when investigating a bug, unexpected behavior, PHP error, JavaScript error, or broken feature."
argument-hint: "Describe the issue, error message, or unexpected behavior"
agent: agent
---

# AVideo Debug Session

Debug the following issue in AVideo: **{{ISSUE_DESCRIPTION}}**

## Step 1: Gather Context (Do Not Assume)

Before suggesting a fix:

1. **Identify the entry point**: What URL, AJAX endpoint, or CLI script triggers the issue?
2. **Locate related files**:
   - For PHP errors: find the file and line number from the error log
   - For AJAX issues: identify the `*.json.php` endpoint being called
   - For plugin issues: identify the plugin directory under `plugin/`
   - For UI issues: identify the relevant JS file in `view/js/` or `plugin/*/view/`
3. **Read the error log**: AVideo logs to `$global['logfile']`. Look for `_error_log()` output.
4. **Read the relevant source files** before suggesting changes.

Useful log locations:
- PHP errors: `$global['logfile']` (set in `videos/configuration.php`)
- Docker environments: `/dev/stdout` (check `docker logs`)
- Browser JS errors: DevTools Console

---

## Step 2: Search for Similar Logic

Before writing a fix:
- Search `objects/functions*.php` for existing utilities relevant to the problem
- Search `plugin/` for existing plugins that handle similar behavior
- Check if the issue is in a shared helper used by multiple plugins

Do not assume a function, class, or table exists without verifying it in the source.

---

## Step 3: Identify the Root Cause

Analyze with these questions:
- Is this a missing permission check? (`User::isLogged()`, `User::isAdmin()`)
- Is this an unhandled `null` or missing DB row?
- Is this a missing or failed DB migration? (Check `updatedb/` version history)
- Is this a plugin conflict? (Which plugins are enabled? Do any override the same hook?)
- Is this an input validation failure? (Check `objects/functionsSecurity.php`)
- Is this a CORS or URL issue? (Check `$global['webSiteRootURL']` usage)
- Is this a session issue? (Check session name: `md5($global['systemRootPath'])`)

---

## Step 4: Suggest a Minimal Safe Fix

- Propose the **smallest possible change** that fixes the issue
- Do not refactor surrounding code unless the bug requires it
- Do not rename or move functions without checking all usages
- If a migration is needed, create a new versioned file — do not modify existing ones
- If a plugin is involved, keep the fix inside `plugin/PluginName/`

---

## Step 5: Provide Verification Steps

After describing the fix, provide:
- Exact steps to reproduce the bug before the fix
- Exact steps to verify the fix works
- Any commands or log lines to check:
  ```bash
  # Check PHP error log
  tail -f /path/to/logfile

  # Run PHPUnit tests
  ./vendor/bin/phpunit --configuration phpunit.xml

  # Test specific AJAX endpoint
  curl -X POST "https://yoursite/objects/endpoint.json.php" -d "param=value"
  ```
- Whether any cached data needs to be cleared after the fix

---

## Important Constraints

- Do not change public function signatures without checking all callers
- Do not change database column types without a migration
- Do not remove or change plugin hooks that other plugins may depend on
- Log unknown/unexpected errors with `_error_log($msg, AVideoLog::$ERROR)` — do not swallow exceptions
- Do not expose internal error details (paths, queries, stack traces) to end users
