---
name: "AVideo Reuse Existing Code"
description: "Use when starting any new feature, endpoint, plugin, or frontend component in AVideo. Covers the required search-first workflow, existing helpers inventory, plugin hook reuse, JS utility reuse, and patterns to avoid hallucinating APIs that do not exist."
applyTo: "**"
---

# AVideo: Reuse Existing Code Before Creating New Code

## The Rule

**Do not write new code before searching for existing code.**

AVideo has ~130+ plugins and a rich library of global helpers. Creating duplicate logic fragments the codebase, increases maintenance cost, and risks inconsistencies. Always search first.

---

## Required Search Checklist

Before writing any new PHP function, class, endpoint, SQL query, or JS utility, verify:

1. **Is there an existing PHP function?** → Check `objects/functions*.php`
2. **Is there an existing class?** → Check `objects/*.php`
3. **Is there an existing plugin?** → Check `plugin/` directory listing
4. **Is there an existing AJAX endpoint?** → Check `objects/*.json.php`, `plugin/*/objects/*.json.php`
5. **Is there an existing database table/column?** → Check `updatedb/updateDb.v*.sql` and `plugin/*/install/*.sql`
6. **Is there an existing JS utility?** → Check `view/js/*.js`
7. **Is there an existing admin form helper?** → Check `admin/functions.php`

If you are not sure, **ask or leave a `// TODO: verify this exists` comment** rather than inventing an API.

---

## PHP Helpers Inventory

### `objects/functions.php`
- `_error_log($msg, $level)` — structured logging
- `_empty($val)` — custom empty check
- `cmpPlugin()` — plugin sort comparator
- `isPHP()`, `modEnabled()`, `modRewriteEnabled()`, `isFFMPEG()` — environment checks

### `objects/functionsSecurity.php`
- `$securityFilter`, `$securityFilterInt`, `$securityRemoveSingleQuotes` — input sanitizers
- `videosHashToID($hash)` — video hash to integer ID
- `xss_esc($value)` — XSS output escaping
- `forbiddenPageIfCannotWatchVideo($videos_id)` — dies/redirects (via `forbiddenPage()`) unless the caller can see the video (group/plugin visibility + password protection)
- `canWatchVideoIncludingPassword($videos_id)` — same check as above, returns bool instead of dying (use inside a function that itself must return a value)
- URL validation patterns

### `objects/functionsFile.php`
- `humanFileSize($bytes)` — human-readable sizes
- `file_upload_max_size()` — PHP upload limit
- `make_path($path)` — create directory if missing
- `rrmdir($path)` — recursive directory removal
- `local_get_contents($url)` — local HTTP GET
- `checkFileModified($path)` — modification check
- `setPathPermissions($path)` — set directory permissions

### `objects/functionsMySQL.php`
- `mysqlBeginTransaction()` — start transaction
- `mysqlCommit()` — commit transaction
- `mysqlRollback()` — rollback transaction
- `getDatabaseTimezoneName()` — DB timezone

### `objects/functionsAVideo.php`
- `isAVideoMobileApp()` — detect mobile app requests
- `isAVideoEncoder()` — detect encoder requests
- `isAVideo()` — detect AVideo requests
- `isCDN()`, `isFromCDN()` — CDN detection

### `objects/functionsMail.php`
- Email sending via PHPMailer (already configured)

### `objects/functionsImages.php`
- Image processing and manipulation utilities

### `objects/functionsFFMPEG.php`
- FFMPEG-related utilities including `handleCallbackTriggerPluginHook()`

---

## Core Classes Inventory

| Class | File | Key Methods |
|---|---|---|
| `sqlDAL` | `objects/mysql_dal.php` | `readSql()`, `writeSql()`, `fetchAssoc()`, `close()` |
| `User` | `objects/user.php` | `isLogged()`, `isAdmin()`, `getId()`, `getInstance()` |
| `AVideoConf` | `objects/configuration.php` | `getConf()`, `getWebSiteTitle()`, `getEncoderURL()` |
| `Video` | `objects/video.php` | `getById()`, `getUsersId()`, `getFilename()` |
| `Category` | `objects/category.php` | Category CRUD |
| `Comment` | `objects/comment.php` | Comment CRUD |
| `Playlist` | `objects/playlist.php` | Playlist CRUD |
| `Channel` | `objects/Channel.php` | Channel data |
| `Page` | `objects/Page.php` | Static pages |
| `Plugin` | `objects/plugin.php` | Plugin entity |
| `Main` | `objects/mainObject.php` | `dateMySQLToBrString()`, `dateBrStringToMySQL()` |
| `AVideoPlugin` | `plugin/AVideoPlugin.php` | `loadPlugin()`, `loadPluginIfEnabled()`, `getHeadCode()` |

---

## Plugin Hooks Inventory

Before creating a new hook method, verify it does not already exist in `plugin/Plugin.abstract.php`:

| Hook | Purpose |
|---|---|
| `getHeadCode()` | HTML inside `<head>` |
| `getFooterCode()` | HTML before `</body>` |
| `getHTMLBody()` | Page body injection |
| `getHTMLMenuLeft()` | Left sidebar navigation |
| `getHTMLMenuRight()` | Right sidebar navigation |
| `getChartContent()` | Analytics chart content |
| `getPluginMenu()` | Admin plugin settings link |
| `getHelp()` | Plugin help text |
| `updateScript()` | Migration execution |

---

## Admin UI Helpers

From `admin/functions.php` — reuse before writing custom form HTML:

```php
// Render a plugin settings table from object_data
createTable('PluginName');

// Convert JSON config to form elements
echo jsonToFormElements($config);

// Plugin on/off toggle switch
echo getPluginSwitch($pluginName);
```

---

## JavaScript Utilities Inventory

From `view/js/script.js` (available globally on all pages):

| Symbol | Purpose |
|---|---|
| `modal.showPleaseWait()` | Show full-screen loading spinner |
| `modal.hidePleaseWait()` | Hide loading spinner |
| `avideoToastSuccess(msg)` | Green auto-dismiss toast |
| `avideoToastError(msg)` | Red auto-dismiss toast |
| `avideoAlertError(msg)` | Error modal requiring dismissal |
| `webSiteRootURL` | Site URL with trailing slash |
| `_serverTime` | Server Unix timestamp |
| `timezone` | User timezone |
| `player` | Video.js player instance |
| `mediaId` | Current media ID |

From `view/js/ajaxLoad.js`:
- AJAX loading patterns for partial page updates

From `view/js/form2JSON.js`:
- `form2JSON()` — serialize form to JSON object

---

## Checking Whether a Plugin Exists

Before building a feature, check if a plugin already covers it:

```bash
# Linux/macOS
ls plugin/
# Windows
Get-ChildItem plugin/
```

Notable existing plugins (do not duplicate their functionality):
- `Subscription` — subscription plans and access control
- `PayPerView` — per-video purchasing
- `FansSubscriptions` — creator subscription tiers
- `Live` — live streaming
- `VideoHLS` — HLS video delivery
- `Permissions` — role-based access
- `Audit` — action audit logging
- `Cache` — caching layer
- `API` — REST API
- `S3Import`, `B2`, `FTP` — external storage
- `GoogleAnalytics` — analytics
- `SocialLogin*` — social auth providers

---

## Patterns to Avoid

**Inventing APIs:**
```php
// WRONG - VideoHelper does not exist
VideoHelper::processUpload($file);

// Check what exists first, then use it
$video = new Video();
$video->processUpload($file); // only if this method actually exists
```

**Duplicate DB queries:**
```php
// WRONG - User already loaded in session
$sql = "SELECT * FROM users WHERE id = ?";
$res = sqlDAL::readSql($sql, 'i', [User::getId()]);

// RIGHT - use the existing User object
$user = User::getInstance();
```

**Custom loading spinners:**
```javascript
// WRONG
$('#mySpinner').show();
$.ajax({...}).always(() => $('#mySpinner').hide());

// RIGHT
modal.showPleaseWait();
$.ajax({...}).always(() => modal.hidePleaseWait());
```

**Wrong access-control pattern (most common security bug in this codebase):**
```php
// WRONG — this pattern is widespread in legacy code; do not copy it
// Missing: HTTP 403 status code, audit log entry, login redirect for guests
if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => "You can't do this"]));
}

// ALSO WRONG — http_response_code(403) alone is not sufficient
if (!User::isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => true, 'msg' => 'Not authorized']));
}

// RIGHT — reuse forbiddenPage() from objects/functionsSecurity.php
// It sets HTTP 403, logs the violation, and redirects unauthenticated users to login
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
```

---

## When You Cannot Find an Existing Implementation

If after searching you cannot find an existing implementation:

1. Confirm the feature does not exist in a different form (different name, different file)
2. Check the most similar existing feature to understand the expected pattern
3. Implement following the pattern of the most similar existing feature
4. Add a comment: `// New implementation - no existing equivalent found in [files searched]`

Do **not** invent class names, function names, table names, or hook names and present them as if they exist in the codebase. If uncertain, use a `// TODO: verify this exists` comment.

---

## Do

- Search `objects/functions*.php` before writing new utility functions
- Search `plugin/` before creating a new plugin
- Search `updatedb/` and `plugin/*/install/` before creating new tables
- Search `view/js/` before writing new frontend utilities
- Reuse `createTable()` and `jsonToFormElements()` for admin settings forms
- Use `AVideoPlugin::loadPluginIfEnabled()` to interact with other plugins

## Do Not

- Invent class names, function names, or table names without verifying they exist
- Duplicate logic already in `objects/functions*.php`
- Reimplement loading spinners, toasts, or alerts — use the existing globals
- Create a new plugin for functionality already covered by an existing one
- Roll your own auth, session, or CSRF logic
