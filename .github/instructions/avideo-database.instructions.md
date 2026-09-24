---
name: "AVideo Database"
description: "Use when writing SQL queries, database migrations, schema changes, or data access code in AVideo. Covers sqlDAL usage, prepared statements, migration file conventions, table naming, transaction handling, and performance considerations."
applyTo: "{objects/**/*.php,plugin/**/*.php,updatedb/*.sql,plugin/**/install/*.sql}"
---

# AVideo Database Guidelines

## Core Rule: Search Existing Schema First

Before adding tables, columns, or queries:
1. Search `updatedb/` for existing table definitions (`updatedb/updateDb.v*.sql`)
2. Search `plugin/*/install/` for plugin-specific tables
3. Search `objects/*.php` for existing data access patterns
4. Do not guess table names or column names — verify them in migration files or class definitions

---

## Database Access Layer: `sqlDAL`

All database access must go through `sqlDAL` in `objects/mysql_dal.php`.
**Never use raw `mysqli_query()` directly.**

### Read Queries (SELECT)

```php
$sql = "SELECT id, title, users_id FROM videos WHERE id = ?";
$res = sqlDAL::readSql($sql, 'i', [$videoId]);

if ($res) {
    while ($row = sqlDAL::fetchAssoc($res)) {
        // use $row['id'], $row['title'], etc.
    }
    sqlDAL::close($res);
} else {
    _error_log('Query failed: ' . $sql);
}
```

### Write Queries (INSERT / UPDATE / DELETE)

```php
// INSERT
$sql = "INSERT INTO my_table (users_id, value, created) VALUES (?, ?, NOW())";
sqlDAL::writeSql($sql, 'is', [$userId, $value]);

// UPDATE
$sql = "UPDATE my_table SET value = ?, modified = NOW() WHERE id = ?";
sqlDAL::writeSql($sql, 'si', [$value, $id]);

// DELETE
$sql = "DELETE FROM my_table WHERE id = ?";
sqlDAL::writeSql($sql, 'i', [$id]);
```

### Format Specifiers

| Specifier | Type |
|---|---|
| `i` | Integer |
| `d` | Double / Float |
| `s` | String |
| `b` | Blob |

Multiple parameters: `'iis'` = int, int, string.

### Fetching Results

```php
sqlDAL::fetchAssoc($result)  // associative array
sqlDAL::fetchArray($result)  // numeric array
sqlDAL::fetchRow($result)    // alias for fetchArray
```

Always call `sqlDAL::close($result)` after reading a result set.

---

## Transactions

Use transaction helpers from `objects/functionsMySQL.php`:

```php
mysqlBeginTransaction();
try {
    sqlDAL::writeSql($sql1, 'i', [$val1]);
    sqlDAL::writeSql($sql2, 'is', [$val2, $val3]);
    mysqlCommit();
} catch (\Throwable $th) {
    mysqlRollback();
    _error_log('Transaction failed: ' . $th->getMessage());
}
```

Use transactions whenever two or more writes must succeed or fail together.

---

## Migration Files

### Core Migrations

Core schema changes go in `updatedb/`:
```
updatedb/updateDb.v30.0.sql   # next sequential version
```

Always update the version in the `configurations` table at the end of each migration file:
```sql
ALTER TABLE `videos` ADD COLUMN `new_col` VARCHAR(255) DEFAULT NULL;
UPDATE configurations SET version = 'X.Y', modified = NOW() WHERE id = 1;
-- Replace X.Y with the next sequential version matching the filename
```

### Plugin Migrations

Plugin schema changes go in `plugin/PluginName/install/`:
```
plugin/PluginName/install/updateV1.0.sql
plugin/PluginName/install/updateV2.0.sql
```

Migrations run automatically on plugin enable/upgrade. Version comparison uses `AVideoPlugin::compareVersion()`.

### Migration Rules

- Never modify an existing migration file — add a new versioned file
- Use `IF NOT EXISTS` / `IF EXISTS` guards on all DDL
- Add `DEFAULT NULL` or `DEFAULT 'value'` on new columns to preserve existing rows
- Do not drop columns — mark as deprecated with a comment instead
- Do not rename columns — add a new column and deprecate the old one
- Do not change column types without verifying all code using that column

```sql
-- Safe column addition
ALTER TABLE `videos` ADD COLUMN IF NOT EXISTS `new_field` VARCHAR(255) DEFAULT NULL;

-- Safe table creation
CREATE TABLE IF NOT EXISTS `MyPlugin_data` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Table Naming Conventions

| Pattern | Example | Used For |
|---|---|---|
| Lowercase core names | `videos`, `users`, `categories`, `comments`, `playlists` | Core tables |
| `PluginName_entity` | `Subscription_plans`, `PayPerView_items` | Plugin-specific tables |
| `CachesInDB` | `CachesInDB` | Plugin cache (excluded from audit) |
| `configurations` | `configurations` | Site config singleton (id=1) |
| `plugins` | `plugins` | Plugin registry |

Do not invent table names. Search migration files to confirm a table exists before querying it.

---

## Common Core Tables

| Table | Key Columns | Notes |
|---|---|---|
| `users` | `id`, `username`, `email`, `password`, `isAdmin`, `canUpload`, `canStream` | User accounts |
| `videos` | `id`, `title`, `filename`, `users_id`, `categories_id`, `status`, `duration` | Video metadata |
| `categories` | `id`, `name`, `clean_name`, `parentId` | Video categories |
| `comments` | `id`, `videos_id`, `users_id`, `text`, `created` | User comments |
| `playlists` | `id`, `name`, `users_id` | User playlists |
| `plugins` | `id`, `name`, `uuid`, `status`, `dirName`, `object_data` | Plugin registry |
| `configurations` | `id=1`, `webSiteTitle`, `logo`, `theme`, `encoderURL`, `smtp*` | Site settings |
| `CachesInDB` | `id`, `name`, `value`, `expire` | DB cache |

### Cache invalidation is not immediate

`CacheHandler::deleteCache()` (`objects/Object.php`, used by `CategoryCacheHandler`,
`VideoCacheHandler`, etc.) defaults to `$schedule = true`. With the Cache plugin enabled, that
only queues the prefix in `cache_schedule_delete`, and the rows are removed later by the
`Cache::executeEveryMinute()` cron job. The file cache is removed asynchronously. A read right
after a write can therefore get stale data, which is worse on sites without a working cron. For
lists that must reflect a write immediately, put a cheap fingerprint of the source table in the
cache key (for example, `Category::getListCacheVersion()`) instead of relying on deletion.

---

## Indexes

- Add indexes only when justified by query patterns
- Document the reason for each index in a comment
- Check for existing indexes before adding a duplicate
- Composite indexes: put the highest-cardinality column first

```sql
-- Justified: used in JOIN and WHERE on users_id
ALTER TABLE `MyPlugin_data` ADD INDEX `idx_users_id` (`users_id`);
```

---

## Query Performance

- Avoid `SELECT *` — select only the columns you need
- Use `LIMIT` on queries that could return large result sets
- Avoid `N+1` query patterns — batch lookups where possible
- When adding a `WHERE` clause on a new column, add an index
- Avoid subqueries in loops — use JOINs instead

---

## Audit Logging

`sqlDAL::writeSql()` automatically sends write operations to the Audit plugin when enabled.
Do not bypass `sqlDAL` to avoid audit logging. Do not write to the audit table directly.

---

## Do

- Use `sqlDAL::readSql()` and `sqlDAL::writeSql()` for all queries
- Use `mysqlBeginTransaction()` / `mysqlCommit()` / `mysqlRollback()` for multi-step writes
- Add `IF NOT EXISTS` guards in migration files
- Search existing migration files to confirm table/column names before querying
- Use `utf8mb4` charset for new tables
- Add `PRIMARY KEY` and relevant indexes to new tables

## Do Not

- Use raw `mysqli_query()` or string-interpolated SQL
- Guess or invent table names or column names
- Modify existing migration files
- Drop columns in migrations
- Query tables from other plugins without checking their documented API
- Expose SQL error messages to end users (log with `_error_log()` instead)
