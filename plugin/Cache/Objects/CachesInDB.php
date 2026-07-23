<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/CacheLikeEscaper.php';

class CachesInDB extends ObjectYPT
{
    // Bounded batch deletion defaults (overridable via the Cache plugin settings, see getCacheSetting()).
    const DEFAULT_DELETE_BATCH_SIZE = 1000;
    const DEFAULT_DELETE_MAX_BATCHES_HTTP = 5;   // caps synchronous work started from a normal HTTP request
    const DEFAULT_DELETE_MAX_BATCHES_CLI = 200;  // generous cap for CLI/cron context, still bounded to avoid infinite loops
    const DEFAULT_MAX_PAYLOAD_BYTES = 3145728;   // 3 MB conservative default for a single cache row content
    const DEFAULT_FALLBACK_RETENTION_DAYS = 7;   // used only when expires IS NULL
    const DEFAULT_LOCK_TIMEOUT_SECONDS = 2;      // GET_LOCK() wait timeout, kept short so requests are never blocked for long

    private static $missingTableLogLastTime = 0;
    private static $autoCreateLogLastTime = 0;
    private static $autoCreateAttempted = false;
    private static $cachedDatabaseName = null;

    protected $id;
    protected $content;
    protected $domain;
    protected $ishttps;
    protected $loggedType;
    protected $user_location;
    protected $expires;
    protected $timezone;
    protected $name;

    public static function getSearchFieldsNames()
    {
        return ['domain', 'ishttps', 'user_location', 'timezone', 'name'];
    }

    public static function getTableName()
    {
        return 'CachesInDB';
    }

    private static function shouldThrottleLog($type, $windowSeconds = 30)
    {
        $now = time();
        if ($type === 'missing') {
            if (($now - self::$missingTableLogLastTime) < $windowSeconds) {
                return true;
            }
            self::$missingTableLogLastTime = $now;
            return false;
        }

        if (($now - self::$autoCreateLogLastTime) < $windowSeconds) {
            return true;
        }
        self::$autoCreateLogLastTime = $now;
        return false;
    }

    private static function logSchemaEvent($event, $startTime = 0, $tableExists = false, $extra = [])
    {
        $isMissingEvent = stripos($event, 'missing') !== false;
        if (self::shouldThrottleLog($isMissingEvent ? 'missing' : 'autocreate')) {
            return;
        }

        $duration = 0;
        if (!empty($startTime)) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
        }

        $users_id = 0;
        if (class_exists('User') && method_exists('User', 'getId')) {
            $users_id = intval(User::getId());
        }

        $payload = [
            'event' => $event,
            'plugin' => 'Cache',
            'class' => __CLASS__,
            'table' => static::getTableName(),
            'tableExists' => !empty($tableExists),
            'request_uri' => @$_SERVER['REQUEST_URI'],
            'script_filename' => @$_SERVER['SCRIPT_FILENAME'],
            'script_name' => @$_SERVER['SCRIPT_NAME'],
            'users_id' => $users_id,
            'session_id' => @session_id(),
            'is_cli' => isCommandLineInterface(),
            'duration_ms' => $duration,
        ];

        if (!empty($extra) && is_array($extra)) {
            $payload = array_merge($payload, $extra);
        }

        _error_log('CachesInDB::schema ' . json_encode($payload), AVideoLog::$WARNING);
    }

    /**
     * Reads a Cache plugin configuration field, falling back to $default when
     * the plugin/field is not available (keeps this class usable even before
     * plugins are fully bootstrapped, e.g. from CLI tools).
     */
    private static function getCacheSetting($field, $default)
    {
        if (class_exists('AVideoPlugin')) {
            try {
                $obj = AVideoPlugin::getDataObject('Cache');
                if (!empty($obj) && isset($obj->$field) && $obj->$field !== '') {
                    return $obj->$field;
                }
            } catch (\Throwable $th) {
                // fall through to default
            }
        }
        return $default;
    }

    public static function getDeleteBatchSize()
    {
        return max(100, intval(self::getCacheSetting('cacheDeleteBatchSize', self::DEFAULT_DELETE_BATCH_SIZE)));
    }

    public static function getDeleteMaxBatchesHttp()
    {
        return max(1, intval(self::getCacheSetting('cacheDeleteMaxBatchesHttp', self::DEFAULT_DELETE_MAX_BATCHES_HTTP)));
    }

    public static function getDeleteMaxBatchesCli()
    {
        return max(1, intval(self::getCacheSetting('cacheDeleteMaxBatchesCli', self::DEFAULT_DELETE_MAX_BATCHES_CLI)));
    }

    public static function getMaxPayloadBytes()
    {
        return intval(self::getCacheSetting('maxCachePayloadSizeBytes', self::DEFAULT_MAX_PAYLOAD_BYTES));
    }

    public static function getFallbackRetentionDays()
    {
        return intval(self::getCacheSetting('cacheFallbackRetentionDays', self::DEFAULT_FALLBACK_RETENTION_DAYS));
    }

    /**
     * Literal (non-wildcard) LIKE prefix escaping. Delegates to CacheLikeEscaper
     * so the logic lives in exactly one place and can be unit tested without
     * pulling in configuration.php / the DB layer.
     */
    public static function escapeLikePrefix($value)
    {
        return CacheLikeEscaper::escapeLikePrefix($value);
    }

    private static function getCurrentDatabaseName()
    {
        global $global;
        if (self::$cachedDatabaseName !== null) {
            return self::$cachedDatabaseName;
        }
        self::$cachedDatabaseName = '';
        if (!empty($global['mysqli']) && _mysql_is_open()) {
            try {
                $res = $global['mysqli']->query('SELECT DATABASE() as db');
                if ($res) {
                    $row = $res->fetch_assoc();
                    self::$cachedDatabaseName = !empty($row['db']) ? $row['db'] : '';
                    $res->free();
                }
            } catch (\Throwable $th) {
                self::$cachedDatabaseName = '';
            }
        }
        return self::$cachedDatabaseName;
    }

    private static function cleanupLockKey($prefix)
    {
        return 'CachesInDB_cleanup_' . md5(self::getCurrentDatabaseName() . '|' . $prefix);
    }

    /**
     * Cross-process/cross-server lock (MySQL GET_LOCK) so the same cache
     * prefix is never deleted concurrently by two Apache workers, async CLI
     * processes, or the scheduled cron cleanup at the same time.
     * Uses a short timeout on purpose: failing to acquire the lock must never
     * block the HTTP request for long, the caller should just skip this run.
     */
    private static function acquireCleanupLock($prefix, $timeoutSeconds = null)
    {
        if (!_mysql_is_open()) {
            return true; // no DB connection available, do not block cleanup entirely
        }
        if ($timeoutSeconds === null) {
            $timeoutSeconds = self::DEFAULT_LOCK_TIMEOUT_SECONDS;
        }
        $key = self::cleanupLockKey($prefix);
        try {
            $sql = "SELECT GET_LOCK(?, ?) as locked";
            $res = sqlDAL::readSql($sql, 'si', [$key, $timeoutSeconds], true);
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            $locked = !empty($row) && intval($row['locked']) === 1;
        } catch (\Throwable $th) {
            // If GET_LOCK is unavailable for any reason, do not block the cleanup.
            return true;
        }
        if (!$locked) {
            _error_log("CachesInDB::acquireCleanupLock({$prefix}) skipped, lock held by another process", AVideoLog::$DEBUG);
        }
        return $locked;
    }

    private static function releaseCleanupLock($prefix)
    {
        if (!_mysql_is_open()) {
            return;
        }
        try {
            $key = self::cleanupLockKey($prefix);
            $res = sqlDAL::readSql("SELECT RELEASE_LOCK(?) as released", 's', [$key], true);
            sqlDAL::close($res);
        } catch (\Throwable $th) {
            // nothing to do, the lock will expire when the connection closes
        }
    }

    private static function tryAutoCreateMissingTable()
    {
        global $global;
        $start = microtime(true);

        // Runtime web traffic must not execute schema creation. Keep this as a
        // CLI-only recovery path and rely on install/update scripts for normal operation.
        if (!isCommandLineInterface()) {
            self::logSchemaEvent('missing_table_skip_autocreate_non_cli', $start, false);
            return false;
        }

        if (self::$autoCreateAttempted) {
            self::logSchemaEvent('missing_table_skip_autocreate_already_attempted', $start, false);
            return false;
        }
        self::$autoCreateAttempted = true;

        $lockFile = sys_get_temp_dir() . '/CachesInDB_autocreate.lock';
        $lockHandle = @fopen($lockFile, 'c');
        if (empty($lockHandle)) {
            self::logSchemaEvent('missing_table_skip_autocreate_lock_open_failed', $start, false);
            return false;
        }

        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($lockHandle);
            self::logSchemaEvent('missing_table_skip_autocreate_lock_busy', $start, false);
            return false;
        }

        $created = false;
        try {
            if (static::isTableInstalled()) {
                self::logSchemaEvent('missing_table_skip_autocreate_already_exists', $start, true);
                return true;
            }
            $file = $global['systemRootPath'] . 'plugin/Cache/install/install.sql';
            sqlDal::executeFile($file);
            $created = static::isTableInstalled();
            self::logSchemaEvent('missing_table_autocreate_cli_attempt', $start, $created, ['install_file' => $file]);
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }

        return $created;
    }

    public function setId($id)
    {
        $this->id = intval($id);
    }

    public function setContent($content)
    {
        $content = self::encodeContent($content);
        $this->content = $content;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setIshttps($ishttps)
    {
        $this->ishttps = $ishttps;
    }

    public function setLoggedType($loggedType)
    {
        $this->loggedType = $loggedType;
    }

    public function setUser_location($user_location)
    {
        $this->user_location = $user_location;
    }

    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getId()
    {
        return intval($this->id);
    }

    public function getContent()
    {
        $this->content = self::decodeContent($this->content);
        return $this->content;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getIshttps()
    {
        return $this->ishttps;
    }

    public function getLoggedType()
    {
        return $this->loggedType;
    }

    public function getUser_location()
    {
        return $this->user_location;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getName()
    {
        return $this->name;
    }

    static function hashName($name){
        if(preg_match('/^hashName_/', $name)){
            return $name;
        }
        return 'hashName_'.preg_replace('/[^0-9a-z]/i', '_', $name);
    }

    public static function _getCache($name, $domain, $ishttps, $user_location, $loggedType, $ignoreMetadata=false)
    {
        global $global;
        $name = self::hashName($name);
        // Normalize nullable unique-key fields so lookups match what _setCache() persists
        // (MySQL UNIQUE indexes allow multiple NULLs, so NULL vs '' must not be mixed).
        $domain = (string) $domain;
        $user_location = (string) $user_location;
        $loggedType = (string) $loggedType;
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE name = ? ";
        $formats = 's';
        $values = [$name];
        if (empty($ignoreMetadata)) {
            $sql .= "  AND ishttps = ? AND domain = ? AND user_location = ? ";
            $formats = 'siss';
            $values = [$name, $ishttps, $domain, $user_location];
        } else {
            $sql .= "  AND ishttps = ? AND domain = ? ";
            $formats = 'sis';
            $values = [$name, $ishttps, $domain];
        }
        if(empty($ignoreMetadata)){
            $sql .= " AND loggedType = ? ";
            $formats .= 's';
            $values[] = $loggedType;
        }
        $sql .= " ORDER BY id DESC LIMIT 1";

        //var_dump($sql, $formats, $values );
        //_error_log(json_encode(array($sql, $values )));
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, $formats, $values);
        $data = sqlDAL::fetchAssoc($res);
        //var_dump( $data);
        sqlDAL::close($res);
        if ($res) {
            if (!empty($data) && !empty($data['content'])) {
                $originalContent = $data['content'];
                $data['content'] = self::decodeContent($data['content']);
                //var_dump($originalContent );
                //var_dump($data['content']);
                if($data['content'] === null){
                    _error_log("Fail decode content [{$name}]".$originalContent);
                    //_error_log(json_encode(debug_backtrace()));exit;
                    //var_dump(debug_backtrace());exit;
                    //var_dump("Fail decode content [{$name}]", $originalContent);exit;
                }
            }
            $row = $data;
        } else {
            if (empty($global['mysqli'])) {
                $global['mysqli'] = new stdClass();
            }
            if($global['mysqli']->errno == 1146){
                self::tryAutoCreateMissingTable();
            }
            $row = false;
        }
        //var_dump($row);
        return $row;
    }

    public static function _setCache($name, $value, $domain, $ishttps, $user_location, $loggedType)
    {
        if (!is_string($value)) {
            $value = _json_encode($value);
        }
        if (empty($value)) {
            return false;
        }

        // Avoid race conditions from read-then-insert by using a single atomic UPSERT.
        $name = self::hashName($name);
        $value = self::encodeContent($value);

        $maxBytes = self::getMaxPayloadBytes();
        $size = strlen($value);
        if (!empty($maxBytes) && $size > $maxBytes) {
            _error_log("CachesInDB::_setCache skipped oversized payload name={$name} size={$size} max={$maxBytes}", AVideoLog::$WARNING);
            return false;
        }

        $expires = date('Y-m-d H:i:s', strtotime('+ 1 month'));
        $timezone = date_default_timezone_get();
        $createdPhpTime = time();
        // Normalize nullable unique-key fields to avoid duplicate rows: MySQL UNIQUE
        // indexes treat every NULL as distinct, so NULL and '' must not be mixed.
        $domain = (string) $domain;
        $user_location = (string) $user_location;
        $loggedType = (string) $loggedType;

        $sql = "INSERT INTO " . static::getTableName() . "
                (name, content, domain, ishttps, user_location, loggedType, expires, timezone, created_php_time, created, modified)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    content = VALUES(content),
                    expires = VALUES(expires),
                    timezone = VALUES(timezone),
                    created_php_time = VALUES(created_php_time),
                    modified = NOW()";

        return sqlDAL::writeSql($sql, 'sssissssi', [$name, $value, $domain, intval($ishttps), $user_location, $loggedType, $expires, $timezone, $createdPhpTime]);
    }
    private static function prepareCacheItem($name, $cache, $metadata, $tz, $time) {
        $formattedCacheItem = [];

        $name = self::hashName($name);
        $content = !is_string($cache) ? json_encode($cache) : $cache;
        if (empty($content)) {
            return null;
        }

        $maxBytes = self::getMaxPayloadBytes();
        $size = strlen($content);
        if (!empty($maxBytes) && $size > $maxBytes) {
            _error_log("CachesInDB::prepareCacheItem skipped oversized payload name={$name} size={$size} max={$maxBytes}", AVideoLog::$WARNING);
            return null;
        }

        $expires = date('Y-m-d H:i:s', strtotime('+1 month'));

        // Normalize nullable unique-key fields (see _setCache() for rationale).
        $domain = (string) $metadata['domain'];
        $user_location = (string) $metadata['user_location'];
        $loggedType = (string) $metadata['loggedType'];

        // Format for the prepared statement
        $formattedCacheItem['format'] = "ssssssssi";
        $formattedCacheItem['values'] = [
            $name,
            $content,
            $domain,
            $metadata['ishttps'],
            $user_location,
            $loggedType,
            $expires,
            $tz,
            $time
        ];

        return $formattedCacheItem;
    }

    public static function setBulkCache($cacheArray, $metadata, $batchSize = 50) {
        if(isBot()){
            return false;
        }
        if (empty($cacheArray)) {
            return false;
        }

        if(isCommandLineInterface()){
            echo "setBulkCache ".json_encode(array($metadata, $batchSize )).PHP_EOL;
        }
        global $global;
        $cacheBatches = array_chunk($cacheArray, $batchSize, true);
        $tz = date_default_timezone_get();
        $time = time();
        $result = true;

        foreach ($cacheBatches as $batch) {$placeholders = [];
            $formats = [];
            $values = [];

            foreach ($batch as $name => $cache) {
                $cacheItem = self::prepareCacheItem($name, $cache, $metadata, $tz, $time);
                if ($cacheItem === null) continue;

                $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $formats[] = $cacheItem['format'];
                $values = array_merge($values, $cacheItem['values']);
            }

            if (empty($placeholders)) {
                // every item in this batch was skipped (empty content or oversized payload)
                continue;
            }

            $sql = "INSERT INTO " . static::getTableName() . " (name, content, domain, ishttps, user_location, loggedType, expires, timezone, created_php_time, created, modified)
             VALUES " . implode(", ", $placeholders) . "
             ON DUPLICATE KEY UPDATE
             content = VALUES(content),
             expires = VALUES(expires),
             created_php_time = VALUES(created_php_time),
             modified = NOW()";

            // Start transaction
            mysqlBeginTransaction();

            try {
                $res = sqlDAL::writeSql($sql, implode('', $formats), $values);
                $result &= $res;
                $resCommit = mysqlCommit();
                if(isCommandLineInterface()){
                    echo "setBulkCache name={$name} ".json_encode($res).PHP_EOL;
                }
            } catch (\Throwable $th) {
                mysqlRollback();
                if(isCommandLineInterface()){
                    echo $th->getMessage() . " name={$name} ".$sql.PHP_EOL;
                }
                _error_log($th->getMessage() . " name={$name} ".$sql, AVideoLog::$ERROR);
                //return false;
            }
        }

        return $result;
    }

    public static function readUncomited($uncomited=true)
    {
        if($uncomited){
            $sql = "SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;";
        }else{
            $sql = "SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;";
        }
        return sqlDAL::writeSql($sql);
    }

    public static function _deleteCache($name)
    {
        if(isBot()){
            return false;
        }
        global $global;
        if (empty($name)) {
            return false;
        }

        if (!static::isTableInstalled()) {
            return false;
        }

        // Check if mysqli connection is still valid
        if (!_mysql_is_open()) {
            return false;
        }

        $name = self::hashName($name);
        self::set_innodb_lock_wait_timeout();
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE name = ?";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        self::readUncomited();
        $return = sqlDAL::writeSql($sql, "s", [$name]);
        self::readUncomited(false);
        return $return;
    }
    public static function set_innodb_lock_wait_timeout($timeout = 10)
    {
        global $global;

        // Check if connection was closed
        if (!_mysql_is_open()) {
            return false;
        }

        $sql = "SET SESSION innodb_lock_wait_timeout = {$timeout};";
        /**
        *
        * @var array $global
        * @var object $global['mysqli']
        */
       return $global['mysqli']->query($sql);
    }

    public static function _deleteCacheStartingWith($name)
    {
        global $_deleteCacheStartingWithList;
        if(empty($_deleteCacheStartingWithList)){
            $_deleteCacheStartingWithList = array();
        }
        if(!empty($_deleteCacheStartingWithList[$name])){
            //_error_log("CachesInDB::_deleteCacheStartingWith($name)  error line=".__LINE__);
            return false;
        }
        $_deleteCacheStartingWithList[$name] = time();
        $isCli = isCommandLineInterface();
        if((isBot() && !$isCli) && !preg_match('/plugin\/Live\/on_/', $_SERVER['SCRIPT_NAME'])){
            _error_log("CachesInDB::_deleteCacheStartingWith($name)  error line=".__LINE__);
            return false;
        }
        global $global;
        if (empty($name)) {
            _error_log("CachesInDB::_deleteCacheStartingWith($name)  error line=".__LINE__);
            return false;
        }
        if (!static::isTableInstalled()) {
            _error_log("CachesInDB::_deleteCacheStartingWith($name)  error line=".__LINE__);
            return false;
        }
        // Same-second/short-window de-dup guard, kept as a lightweight first line of defense
        // before we attempt the (more expensive) cross-process MySQL lock below.
        $tmpFile = getTmpDir().'_deleteCacheStartingWith'.md5($name);
        if(file_exists($tmpFile) && (time() - file_get_contents($tmpFile)) < 10){
            _error_log("CachesInDB::_deleteCacheStartingWith($name) already in progress, skipping. Last run: " . (time() - file_get_contents($tmpFile)) . " seconds ago", AVideoLog::$DEBUG);
            return false;
        }
        file_put_contents($tmpFile, time());
        $name = self::hashName($name);

        // Cross-process/cross-server lock: the same prefix must never be deleted
        // concurrently by two Apache workers, an async CLI invalidation process,
        // and the scheduled cron cleanup at the same time.
        if (!self::acquireCleanupLock($name)) {
            _error_log("CachesInDB::_deleteCacheStartingWith($name) skipped, duplicate concurrent invalidation", AVideoLog::$DEBUG);
            return false;
        }

        $start = microtime(true);
        $totalDeleted = 0;
        $batches = 0;
        $count = 0;
        try {
            self::set_innodb_lock_wait_timeout();
            self::readUncomited();

            $escapedPrefix = self::escapeLikePrefix($name);
            $batchSize = self::getDeleteBatchSize();
            $maxBatches = $isCli ? self::getDeleteMaxBatchesCli() : self::getDeleteMaxBatchesHttp();

            // Prefer a single indexed prefix condition over the previous
            // `MATCH(name) AGAINST(...) OR name LIKE '...%'` pattern, which forced a
            // full table scan (see caches9 / unique_cache_index BTREE on `name`).
            // Delete in small bounded batches (by primary key) instead of one huge
            // DELETE so we never hold a long-running transaction/lock on this table.
            $selectSql = "SELECT id FROM " . static::getTableName() . " WHERE name LIKE CONCAT(?, '%') ESCAPE '\\\\' ORDER BY id LIMIT ?";
            $deleteSqlTemplate = "DELETE FROM " . static::getTableName() . " WHERE id IN (%s)";
            $global['lastQuery'] = $selectSql;

            do {
                // $refreshCache=true bypasses sqlDAL's per-request result cache, which
                // otherwise would keep returning the very first batch of ids forever.
                $res = sqlDAL::readSql($selectSql, 'si', [$escapedPrefix, $batchSize], true);
                $ids = [];
                while ($row = sqlDAL::fetchAssoc($res)) {
                    $ids[] = intval($row['id']);
                }
                sqlDAL::close($res);
                $count = count($ids);
                if ($count > 0) {
                    $placeholders = implode(',', array_fill(0, $count, '?'));
                    $deleteSql = sprintf($deleteSqlTemplate, $placeholders);
                    sqlDAL::writeSql($deleteSql, str_repeat('i', $count), $ids);
                    $totalDeleted += $count;
                    if ($isCli) {
                        usleep(50000); // 50ms: be gentle on a busy production InnoDB buffer pool
                    }
                }
                $batches++;
            } while ($count === $batchSize && $batches < $maxBatches);

            if ($count === $batchSize && $batches >= $maxBatches) {
                // More rows may remain. Do not keep looping inside this request/process;
                // hand off the remainder to the existing scheduled cleanup queue instead.
                if (class_exists('Cache_schedule_delete')) {
                    Cache_schedule_delete::insert($name);
                }
                _error_log("CachesInDB::_deleteCacheStartingWith($name) batch cap reached ({$maxBatches}), remainder queued for scheduled cleanup", AVideoLog::$WARNING);
            }

            self::readUncomited(false);
        } finally {
            self::releaseCleanupLock($name);
        }

        $elapsed = round(microtime(true) - $start, 3);
        _error_log("CachesInDB::_deleteCacheStartingWith($name) deleted={$totalDeleted} batches={$batches} elapsed={$elapsed}s", AVideoLog::$PERFORMANCE);
        return $totalDeleted > 0;
    }

    /**
     * Deletes expired cache rows (expires < NOW()) in small bounded batches.
     * Intended to be called from a cron/scheduled context (Cache::executeEveryMinute).
     * Requires the CachesInDB_expires index (see install/updateV10.0.sql).
     */
    public static function deleteExpiredCache($batchSize = null, $maxBatches = null)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $batchSize = $batchSize ?: self::getDeleteBatchSize();
        $maxBatches = $maxBatches ?: self::getDeleteMaxBatchesCli();

        if (!self::acquireCleanupLock('expired_cache')) {
            _error_log("CachesInDB::deleteExpiredCache skipped, duplicate concurrent invalidation", AVideoLog::$DEBUG);
            return 0;
        }

        $start = microtime(true);
        $totalDeleted = 0;
        $batches = 0;
        $count = 0;
        try {
            self::set_innodb_lock_wait_timeout();
            $selectSql = "SELECT id FROM " . static::getTableName() . " WHERE expires IS NOT NULL AND expires < NOW() ORDER BY id LIMIT ?";
            $global['lastQuery'] = $selectSql;
            do {
                $res = sqlDAL::readSql($selectSql, 'i', [$batchSize], true);
                $ids = [];
                while ($row = sqlDAL::fetchAssoc($res)) {
                    $ids[] = intval($row['id']);
                }
                sqlDAL::close($res);
                $count = count($ids);
                if ($count > 0) {
                    $placeholders = implode(',', array_fill(0, $count, '?'));
                    sqlDAL::writeSql("DELETE FROM " . static::getTableName() . " WHERE id IN ({$placeholders})", str_repeat('i', $count), $ids);
                    $totalDeleted += $count;
                    usleep(20000); // 20ms between batches
                }
                $batches++;
            } while ($count === $batchSize && $batches < $maxBatches);
        } finally {
            self::releaseCleanupLock('expired_cache');
        }

        $elapsed = round(microtime(true) - $start, 3);
        _error_log("CachesInDB::deleteExpiredCache deleted={$totalDeleted} batches={$batches} elapsed={$elapsed}s", AVideoLog::$PERFORMANCE);
        return $totalDeleted;
    }

    /**
     * Fallback retention for rows that somehow never got an `expires` value.
     * Disabled when $retentionDays is empty/0 (admin-configurable via the Cache
     * plugin setting "cacheFallbackRetentionDays"). Uses created_php_time, which
     * already has an index (CachesInDB_created_php_time), so this stays a cheap
     * indexed range scan instead of a full table scan.
     */
    public static function deleteStaleCacheWithoutExpiration($retentionDays = null, $batchSize = null, $maxBatches = null)
    {
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        if ($retentionDays === null) {
            $retentionDays = self::getFallbackRetentionDays();
        }
        if (empty($retentionDays) || $retentionDays <= 0) {
            return 0; // administrators can disable fallback retention entirely
        }
        $batchSize = $batchSize ?: self::getDeleteBatchSize();
        $maxBatches = $maxBatches ?: self::getDeleteMaxBatchesCli();

        if (!self::acquireCleanupLock('stale_cache_no_expiration')) {
            _error_log("CachesInDB::deleteStaleCacheWithoutExpiration skipped, duplicate concurrent invalidation", AVideoLog::$DEBUG);
            return 0;
        }

        $start = microtime(true);
        $totalDeleted = 0;
        $batches = 0;
        $count = 0;
        try {
            self::set_innodb_lock_wait_timeout();
            $cutoff = strtotime("-{$retentionDays} days");
            $selectSql = "SELECT id FROM " . static::getTableName() . " WHERE expires IS NULL AND created_php_time IS NOT NULL AND created_php_time < ? ORDER BY id LIMIT ?";
            $global['lastQuery'] = $selectSql;
            do {
                $res = sqlDAL::readSql($selectSql, 'ii', [$cutoff, $batchSize], true);
                $ids = [];
                while ($row = sqlDAL::fetchAssoc($res)) {
                    $ids[] = intval($row['id']);
                }
                sqlDAL::close($res);
                $count = count($ids);
                if ($count > 0) {
                    $placeholders = implode(',', array_fill(0, $count, '?'));
                    sqlDAL::writeSql("DELETE FROM " . static::getTableName() . " WHERE id IN ({$placeholders})", str_repeat('i', $count), $ids);
                    $totalDeleted += $count;
                    usleep(20000);
                }
                $batches++;
            } while ($count === $batchSize && $batches < $maxBatches);
        } finally {
            self::releaseCleanupLock('stale_cache_no_expiration');
        }

        $elapsed = round(microtime(true) - $start, 3);
        _error_log("CachesInDB::deleteStaleCacheWithoutExpiration deleted={$totalDeleted} batches={$batches} elapsed={$elapsed}s retentionDays={$retentionDays}", AVideoLog::$PERFORMANCE);
        return $totalDeleted;
    }


    public static function _deleteCacheWith($name)
    {
        if(isBot()){
            return false;
        }
        global $global;
        if (empty($name)) {
            return false;
        }
        if (!static::isTableInstalled()) {
            return false;
        }
        $name = self::hashName($name);
        $name = str_replace('hashName_', '', $name);
        self::set_innodb_lock_wait_timeout();
        // Substring search can never use a BTREE index (leading wildcard), so this
        // remains a full scan by nature. Still escape LIKE metacharacters so the
        // substring is matched literally and bind it as a parameter (no string
        // concatenation of user-controlled values into the SQL text).
        $escaped = self::escapeLikePrefix($name);
        $sql = "DELETE FROM " . static::getTableName() . " ";
        $sql .= " WHERE name LIKE CONCAT('%', ?, '%') ESCAPE '\\\\'";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        self::readUncomited();
        $return = sqlDAL::writeSql($sql, 's', [$escaped]);
        self::readUncomited(false);
        return $return;
    }

    public static function _deleteAllCache()
    {
        if(isBot()){
            return false;
        }
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        /*
        $sql = "TRUNCATE TABLE " . static::getTableName() . " ";
        $global['lastQuery'] = $sql;
        //_error_log("Delete Query: ".$sql);
        return sqlDAL::writeSql($sql);
        */
        $sql = 'DROP TABLE IF EXISTS `CachesInDB`';
        sqlDal::writeSql($sql);
        $file = $global['systemRootPath'] . 'plugin/Cache/install/install.sql';
        return sqlDal::executeFile($file);
    }

    public static function encodeContent($content)
    {
        $original = $content;
        if (!is_string($content)) {
            $content = _json_encode($content);
        }
        $prefix = substr($content, 0, 10);
        if ($prefix!== CacheDB::$prefix) {
            //$content = base64_encode($content);
            $content = CacheDB::$prefix.$content;
        }
        return $content;
    }

    public static function decodeContent($content)
    {
        $prefix = substr($content, 0, strlen(CacheDB::$prefix));
        if ($prefix === CacheDB::$prefix) {
            $content = str_replace(CacheDB::$prefix, '', $content);
            //$content = base64_decode($content);
        }
        return $content;
    }
}
