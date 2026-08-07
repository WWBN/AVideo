---
name: "AVideo Plugin Review"
description: "Review or audit an AVideo plugin for correctness, structure, hook usage, install/update/uninstall patterns, permissions, DB changes, UI integration, and backward compatibility."
argument-hint: "Plugin name or path to review (e.g. plugin/MyPlugin)"
agent: agent
---

# AVideo Plugin Review

Review the AVideo plugin at: **{{PLUGIN_PATH_OR_NAME}}**

## Step 1: Load Plugin Files

Read the following files from the plugin directory:
- `plugin/PluginName/PluginName.php` — main class
- `plugin/PluginName/version.json` — version metadata
- `plugin/PluginName/index.php` — admin settings page (if present)
- `plugin/PluginName/Objects/` — data classes (if present)
- `plugin/PluginName/install/` — migration SQL files
- `plugin/PluginName/pluginMenu.html` — admin menu entry (if present)
- Any `.json.php` endpoints in the plugin

Also read `plugin/Plugin.abstract.php` to verify hook method signatures.

---

## Review Checklist

### 1. Plugin Structure

- [ ] Main class file is named `PluginName.php` and the class is named `PluginName`
- [ ] Class extends `PluginAbstract`
- [ ] `getUUID()` returns a valid UUID v4 string (format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)
- [ ] `getName()` returns the plugin name matching the directory and class name
- [ ] `getDescription()` returns a meaningful description
- [ ] `version.json` exists and is valid JSON with a version field
- [ ] All plugin files are inside `plugin/PluginName/` — no files outside this directory

### 2. Hook Methods

- [ ] Only hooks that are actually used are overridden (no empty overrides)
- [ ] Hook signatures match `PluginAbstract` exactly (no modified signatures)
- [ ] `getHeadCode()` returns valid HTML (no broken tags, no PHP errors)
- [ ] `getFooterCode()` returns valid HTML
- [ ] Hook output does not hardcode site URLs — uses `$global['webSiteRootURL']`
- [ ] No hook calls another plugin's hooks directly (must go through `AVideoPlugin`)

### 3. Install / Update / Uninstall

- [ ] Migration SQL files are in `plugin/PluginName/install/updateVX.X.sql`
- [ ] Migration files use `IF NOT EXISTS` / `IF EXISTS` guards on all DDL
- [ ] Migration files do not drop or rename existing columns
- [ ] New tables use `utf8mb4` charset and define a proper `PRIMARY KEY`
- [ ] No existing migration file is modified — each change is in a new versioned file
- [ ] If uninstall logic exists, it safely removes only plugin-owned data

### 4. Permissions & Authentication

- [ ] Every AJAX endpoint (`*.json.php`) checks `User::isLogged()` or `User::isAdmin()` before the try/catch block
- [ ] Permission denials use `forbiddenPage($message)` from `objects/functionsSecurity.php` (not raw `die(json_encode(...))`)
- [ ] Ownership of resources is validated before modification (not just "is logged in")
- [ ] No endpoint performs writes accessible to non-admin users without authorization
- [ ] No frontend page exposes admin-only data to normal users
- [ ] Mutating endpoints reuse existing helpers from `objects/functionsSecurity.php` instead of duplicating inline checks:
  - `forbidIfNotPost($msg='')` — reject non-POST requests (405); required for any mutating endpoint not covered by `autoCSRFGuard()` (GET-reachable, or not named `*.json.php`)
  - `forbidIfInvalidToken($msg='')` — reject requests without a valid `globalToken` (403), wraps `isGlobalTokenValid()`
  - `forbidIfIsUntrustedRequest($logMsg='', $approveAVideoUserAgent=true)` — reject cross-origin requests
  - `forbidIfItIsNotMyUsersId($users_id, $logMsg='')` — reject requests targeting another user's resource

### 5. Database Access

- [ ] All queries use `sqlDAL::readSql()` / `sqlDAL::writeSql()` with prepared statements
- [ ] No string-concatenated SQL
- [ ] Table names referenced in code exist in migration files
- [ ] Column names referenced in code exist in migration files
- [ ] `sqlDAL::close($result)` is called after every `readSql()` result set

### 6. Input Validation & Output Escaping

- [ ] All `$_POST` / `$_GET` values are validated and sanitized before use
- [ ] Integer IDs are cast with `(int)` or `intval()`
- [ ] String values are sanitized with `strip_tags()` + `htmlspecialchars()` or `xss_esc()`
- [ ] All HTML output is properly escaped
- [ ] Error messages shown to users do not expose internal paths, stack traces, or queries

### 7. UI Integration

- [ ] Plugin-added UI uses Bootstrap 5 components
- [ ] Loading states use `modal.showPleaseWait()` / `modal.hidePleaseWait()`
- [ ] User feedback uses `avideoToastSuccess()`, `avideoToastError()`, `avideoAlertError()`
- [ ] Plugin settings page uses `createTable()` or `jsonToFormElements()` from `admin/functions.php`
- [ ] Plugin does not load external CDN resources (all assets are local)
- [ ] Plugin UI is visually consistent with the rest of AVideo

### 8. Backward Compatibility

- [ ] Plugin works with PHP 7.3+ (no PHP 8.1+ exclusive syntax unless explicitly targeted)
- [ ] Plugin does not modify core files outside `plugin/PluginName/`
- [ ] Plugin does not change or override another plugin's DB tables
- [ ] Disabling this plugin does not break other plugins or core functionality
- [ ] Plugin object_data structure is backward compatible with previously saved settings

### 9. Error Handling

- [ ] `try/catch(\Throwable $th)` used around DB and external calls
- [ ] Caught errors logged with `_error_log($th->getMessage())`
- [ ] No empty catch blocks
- [ ] AJAX endpoints always return `['error' => false/true, 'msg' => '...']`

---

## Output Format

Respond with:

1. **Plugin Overview**: What the plugin does and which hooks/features it uses
2. **Structural Issues**: Problems with class structure, naming, or missing required files
3. **Security Issues**: Auth, SQL, XSS, or input validation problems (blockers)
4. **Database Issues**: Migration problems, missing guards, unsafe column references
5. **UI Issues**: Inconsistencies with AVideo patterns
6. **Backward Compatibility Issues**: Risks to other plugins or existing data
7. **Minor Issues**: Style, comments, dead code, unnecessary complexity
8. **Overall Assessment**: Ready / Needs Minor Fixes / Needs Major Rework
