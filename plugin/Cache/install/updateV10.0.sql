-- CachesInDB performance/cleanup migration (see plugin/Cache/Objects/CachesInDB.php)
--
-- 1) Add an index on `expires` so scheduled expiration cleanup
--    (CachesInDB::deleteExpiredCache) can use an indexed range scan instead of a
--    full table scan.
ALTER TABLE `CachesInDB`
ADD INDEX `CachesInDB_expires` (`expires` ASC);

-- 2) Drop the FULLTEXT index on `name`. Repository-wide search confirmed
--    MATCH()/AGAINST() against CachesInDB.name was only ever used by the old
--    `_deleteCacheStartingWith()` prefix-deletion query, which has been
--    replaced with a literal, escaped `LIKE CONCAT(?, '%') ESCAPE '\\'`
--    condition backed by the existing `caches9` BTREE index on `name`.
--    No other query in the codebase uses MATCH(name) AGAINST(...) on this table.
--    This is an online DDL operation (ALGORITHM=INPLACE) on modern InnoDB/MySQL
--    versions, so it should not hold a long table lock, but it is still I/O
--    heavy on an ~18GB table: apply during a low-traffic window and monitor.
ALTER TABLE `CachesInDB`
DROP INDEX `name_fulltext`;
