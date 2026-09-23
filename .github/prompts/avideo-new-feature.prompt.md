When the feature targets the Encoder, inspect and edit the Encoder source at
`D:\git\htdocs\AVideo\.compose\encoder\`. Keep Encoder analysis separate from the Streamer code
in the repository root.
---
name: "AVideo New Feature"
description: "Implement a new feature in AVideo safely. Ensures existing patterns are reused, plugin compatibility is maintained, and the implementation is minimal and well-integrated."
argument-hint: "Describe the feature to implement"
agent: agent
---

# AVideo New Feature Implementation

Implement the following feature for AVideo: **{{FEATURE_DESCRIPTION}}**

## Phase 1: Research (Complete Before Writing Any Code)

Before writing a single line of code, answer these questions by searching the codebase:

### 1.1 Does a similar feature already exist?
- Check `plugin/` directory — is there already a plugin covering this?
- Check `objects/*.php` — do existing classes already have relevant methods?
- Check `objects/functions*.php` — are there helper functions that already handle parts of this?

### 1.2 What existing patterns apply?
- How do similar features handle authentication? (`objects/user.php`)
- How do similar AJAX endpoints look? (`objects/*.json.php`)
- How do similar admin settings pages work? (`admin/functions.php`)
- How do similar DB migrations look? (`updatedb/updateDb.v*.sql`)

### 1.3 What are the affected parts?
List the files/areas that need to change:
- Backend PHP files (which endpoints or classes?)
- Database (new tables, new columns, or existing tables?)
- Plugin (new plugin or modification to existing?)
- Frontend (new UI, new AJAX calls, or new modals?)
- Admin settings (new settings form?)
- Tests (new test file or extend existing?)

---

## Phase 2: Implementation Plan

Before writing code, present a clear plan:

1. **New files to create** (with justification for each)
2. **Existing files to modify** (with the specific changes needed)
3. **Database changes** (tables/columns needed with migration file names)
4. **Plugin hooks to use or add**
5. **Backward compatibility impact** (what existing behavior might change)
6. **Plugin compatibility risk** (which existing plugins might be affected)

Get confirmation on the plan before proceeding.

---

## Phase 3: Implementation

Follow these rules during implementation:

### PHP
- Validate all inputs at endpoint entry — use `objects/functionsSecurity.php` filters
- Use `sqlDAL::readSql()` / `sqlDAL::writeSql()` for all DB access
- Return `['error' => false/true, 'msg' => '...']` from AJAX endpoints (`*.json.php`)
- Use `_error_log($th->getMessage())` in catch blocks
- Use `User::isLogged()`, `User::isAdmin()` for permission checks; enforce with `forbiddenPage($message)` from `objects/functionsSecurity.php` — not `die(json_encode(...))`
- Use `AVideoConf::getConf()` for configuration values

### Database
- New tables/columns → new migration file in `updatedb/` or `plugin/*/install/`
- Use `IF NOT EXISTS` / `IF EXISTS` guards in all DDL
- Do not drop or rename existing columns
- Use `utf8mb4` charset for new tables

### Plugin (if applicable)
- Create plugin in `plugin/NewPluginName/NewPluginName.php`
- Extend `PluginAbstract`, implement `getUUID()`, `getName()`, `getDescription()`
- Override only the hooks that are needed
- Store settings in `object_data` JSON column; use `createTable()` for admin UI

### Frontend
- Use `modal.showPleaseWait()` / `modal.hidePleaseWait()` for all async operations
- Use `avideoToastSuccess()`, `avideoToastError()`, `avideoAlertError()` for feedback
- Use Bootstrap 5 components — do not introduce new CSS frameworks
- Use `webSiteRootURL` for all endpoint URLs
- Use jQuery 4.0 patterns

---

## Phase 4: Validation Steps

After implementation, provide:

1. **Manual test steps**: Exact steps to verify the feature works end-to-end
2. **Edge cases to test**:
   - Logged-out user attempt (permission boundary)
   - Non-admin user attempt (if admin-only)
   - Empty/invalid input
   - Concurrent access (if relevant)
3. **Plugin compatibility check**: List which enabled plugins should still work correctly
4. **Regression check**: List existing features that could be affected
5. **Test command**:
   ```bash
   ./vendor/bin/phpunit --configuration phpunit.xml
   ```
6. **New tests**: If logic is complex, a new test should be created in `tests/`

---

## Constraints

- Keep changes minimal — do not refactor existing code unless directly required
- Do not change public API signatures without explicit approval
- Do not introduce new PHP namespaces
- Do not introduce new npm packages without explicit approval
- If uncertain about whether something exists, search first — do not hallucinate
- Leave `// TODO: verify this exists` comments when referencing unverified APIs
