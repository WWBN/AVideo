<?php

interface ObjectInterface
{

    public static function getTableName();
}

$tableExists = [];

abstract class ObjectYPT implements ObjectInterface
{

    protected $properties = [];
    protected $fieldsName = [];
    protected $id;
    protected $created;

    public function __construct($id = "", $refreshCache = false)
    {
        if (!empty($id)) {
            // get data from id
            $this->load($id, $refreshCache);
        }
    }

    public static function getSearchFieldsNames()
    {
        return [];
    }

    public function load($id, $refreshCache = false)
    {
        $row = self::getFromDb($id, $refreshCache);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            @$this->$key = $value;
            //$this->properties[$key] = $value;
        }
        return true;
    }

    public static function getNowFromDB()
    {
        global $global;
        $sql = "SELECT NOW() as my_date_field";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function setGlobalTimeZone()
    {
        global $advancedCustom, $timezoneOriginal;
        if (!isset($timezoneOriginal)) {
            $timezoneOriginal = date_default_timezone_get();
        }
        if (!empty($_COOKIE['timezone']) && $_COOKIE['timezone'] !== 'undefined') {
            $timezone = $_COOKIE['timezone'];
        } else {
            $timeZOnesOptions = object_to_array($advancedCustom->timeZone->type);
            $timezone = $timeZOnesOptions[$advancedCustom->timeZone->value];
        }
        if (empty($timezone) || $timezone == 'undefined') {
            return false;
        }
        date_default_timezone_set($timezone);
    }

    static function getFromDb($id, $refreshCache = false)
    {
        global $global;
        if (!class_exists('sqlDAL')) {
            return false;
        }
        $id = intval($id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  id = ? LIMIT 1";
        //var_dump($sql, $id);
        $res = sqlDAL::readSql($sql, "i", [$id], $refreshCache);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getAll()
    {
        global $global;
        if (!static::isTableInstalled()) {
            return array();
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    public static function getAllActive()
    {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        return $fullData;
    }

    public static function getTotal()
    {
        //will receive
        //current=1&rowCount=10&sort[sender]=asc&searchPhrase=
        global $global;
        if (!static::isTableInstalled()) {
            return 0;
        }
        $sql = "SELECT id FROM  " . static::getTableName() . " WHERE 1=1  ";
        $sql .= self::getSqlSearchFromPost();
        $res = sqlDAL::readSql($sql);
        $countRow = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $countRow;
    }

    public static function getSqlFromPost($keyPrefix = "", $searchTableAlias = '')
    {
        global $global;
        $sql = self::getSqlSearchFromPost($searchTableAlias);
        $sql .= self::getSqlOrderByFromPost($keyPrefix);
        $sql .= self::getSqlLimit();
        return $sql;
    }

    public static function getSqlOrderByFromPost($keyPrefix = "")
    {
        global $global;
        $sql = '';
        if (empty($_POST['sort']) && !empty($_GET['order'][0]['dir'])) {
            $index = intval($_GET['order'][0]['column']);
            $_GET['columns'][$index]['data'];
            $_POST['sort'][$_GET['columns'][$index]['data']] = $_GET['order'][0]['dir'];
        }

        // add a security here
        if (!empty($_POST['sort'])) {
            foreach ($_POST['sort'] as $key => $value) {
                $_POST['sort'][xss_esc($key)] = xss_esc($value);
            }
        }

        // add a security here
        if (!empty($_GET['sort']) && is_array($_GET['sort'])) {
            foreach ($_GET['sort'] as $key => $value) {
                $_POST['sort'][xss_esc($key)] = xss_esc($value);
            }
        }

        if (!empty($_POST['sort'])) {
            $orderBy = [];
            foreach ($_POST['sort'] as $key => $value) {
                $key = ($key);
                //$value = ($value);
                $direction = "ASC";
                if (strtoupper($value) === "DESC") {
                    $direction = "DESC";
                }
                $key = preg_replace("/[^A-Za-z0-9._ ]/", '', $key);
                $key = trim($key);
                if (strpos($key, '.') === false) {
                    $key = "`{$key}`";
                }
                $orderBy[] = " {$keyPrefix}{$key} {$direction} ";
            }
            $sql .= " ORDER BY " . implode(",", $orderBy);
        }

        return $sql;
    }

    public static function getSqlLimit()
    {
        global $global;
        $sql = '';

        if (empty($_REQUEST['rowCount']) && !empty($_REQUEST['length'])) {
            $_REQUEST['rowCount'] = intval($_REQUEST['length']);
        }
        if (empty($_REQUEST['current']) && !empty($_GET['start'])) {
            if(empty($_GET['length'])){
                $_REQUEST['current'] = 1;
            }else{
                $_REQUEST['current'] = ($_GET['start'] / $_GET['length']) + 1;
            }
        } elseif (empty($_REQUEST['current']) && isset($_GET['start'])) {
            $_REQUEST['current'] = 1;
        }

        $_REQUEST['current'] = getCurrentPage();
        $_REQUEST['rowCount'] = getRowCount();

        if (!empty($_REQUEST['rowCount']) && !empty($_REQUEST['current']) && $_REQUEST['rowCount'] > 0) {
            $_REQUEST['rowCount'] = intval($_REQUEST['rowCount']);
            $_REQUEST['current'] = intval($_REQUEST['current']);
            $current = ($_REQUEST['current'] - 1) * $_REQUEST['rowCount'];
            $current = $current < 0 ? 0 : $current;
            $sql .= " LIMIT $current, {$_REQUEST['rowCount']} ";
        } else {
            $_REQUEST['current'] = 0;
            $_REQUEST['rowCount'] = 0;
            $sql .= " LIMIT 1000 ";
        }
        return $sql;
    }

    public static function getSqlDateFilter($searchTableAlias = '')
    {
        $sql = '';
        $created_year = intval(@$_REQUEST['created_year']);
        $created_month = intval(@$_REQUEST['created_month']);
        $modified_year = intval(@$_REQUEST['modified_year']);
        $modified_month = intval(@$_REQUEST['modified_month']);
        if (!empty($searchTableAlias)) {
            $searchTableAlias = "`$searchTableAlias`.";
        }
        if (!empty($created_year)) {
            $sql .= " AND YEAR({$searchTableAlias}created) = $created_year ";
        }
        if (!empty($created_month)) {
            $sql .= " AND MONTH({$searchTableAlias}created) = $created_month ";
        }
        if (!empty($modified_year)) {
            $sql .= " AND YEAR({$searchTableAlias}modified) = $modified_year ";
        }
        if (!empty($modified_month)) {
            $sql .= " AND MONTH({$searchTableAlias}modified) = $modified_month ";
        }

        return $sql;
    }

    public static function getSqlSearchFromPost($searchTableAlias = '')
    {
        $sql = self::getSqlDateFilter($searchTableAlias);
        if (!empty($_POST['searchPhrase'])) {
            $_GET['q'] = $_POST['searchPhrase'];
        } elseif (!empty($_GET['search']['value'])) {
            $_GET['q'] = $_GET['search']['value'];
        }
        if (!empty($_GET['q'])) {
            global $global;
            $search = strtolower(xss_esc($_GET['q']));

            $like = [];
            $searchFields = static::getSearchFieldsNames();
            foreach ($searchFields as $value) {
                if (!str_contains($value, '.') && !str_contains($value, '`')) {
                    $value = "`{$value}`";
                }
                $like[] = " {$value} LIKE '%{$search}%' ";
                // for accent insensitive
                $like[] = " CONVERT(CAST({$value} as BINARY) USING utf8) LIKE '%{$search}%' ";
            }
            if (!empty($like)) {
                $sql .= " AND (" . implode(" OR ", $like) . ")";
            } else {
                $sql .= " AND 1=1 ";
            }
        }

        return $sql;
    }

    public function save()
    {
        if (!$this->tableExists()) {
            _error_log("Save error, table " . static::getTableName() . " does not exists", AVideoLog::$ERROR);
            return false;
        }
        if (!isCommandLineInterface() && !self::ignoreTableSecurityCheck() && isUntrustedRequest("SAVE " . static::getTableName())) {
            _error_log("Save error, table " . static::getTableName() . " something ", AVideoLog::$ERROR);
            return false;
        }
        global $global;
        $fieldsName = $this->getAllFields();
        if (empty($fieldsName)) {
            _error_log("Save error, table " . static::getTableName() . " MySQL Error", AVideoLog::$ERROR);
            return false;
        }
        //_error_log("Save ".static::getTableName().' '.json_encode($fieldsName));
        $formats = '';
        $values = [];
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET ";
            $fields = [];
            foreach ($fieldsName as $value) {
                //$escapedValue = $global['mysqli']->real_escape_string($this->$value);
                if (strtolower($value) == 'created') {
                    //var_dump($this->created);exit;
                    if (
                        !empty($this->created) && (User::isAdmin() ||
                            isCommandLineInterface() ||
                            (class_exists('API') && API::isAPISecretValid()) ||
                            !empty($global['allowModifyCreated'])
                        )
                    ) {
                        $this->created = preg_replace('/[^0-9: \/-]/', '', $this->created);
                        //_error_log("created changed in table=".static::getTableName()." id={$this->id} created={$this->created}");
                        $formats .= 's';
                        $values[] = $this->created;
                        $fields[] = " `{$value}` = ? ";
                    }
                } elseif (strtolower($value) == 'modified') {
                    $fields[] = " {$value} = now() ";
                } elseif (strtolower($value) == 'modified_php_time') {
                    $fields[] = " {$value} = " . time();
                } elseif (strtolower($value) == 'created_php_time') {
                    if(empty($this->created_php_time)){
                        if(!empty($this->created)){
                            $formats .= 'i';
                            $values[] = strtotime($this->created);
                            $fields[] = " `{$value}` = ? ";
                        }else{
                            $formats .= 'i';
                            $values[] = time();
                            $fields[] = " `{$value}` = ? ";
                        }
                    }
                } elseif (strtolower($value) == 'timezone') {
                    if (empty($this->$value)) {
                        eval('$this->' . $value . ' = date_default_timezone_get();');
                    }
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " `{$value}` = ? ";
                } elseif (!isset($this->$value) || strtolower($this->$value) == 'null') {
                    $fields[] = " `{$value}` = NULL ";
                } else {
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " `{$value}` = ? ";
                }
                //if(strtolower($value) == 'description'){ var_dump($formats, $this->$value);}
            }
            $sql .= implode(", ", $fields);
            $formats .= 'i';
            $values[] = $this->id;
            $sql .= " WHERE id = ?";
        } else {
            $sql = "INSERT INTO " . static::getTableName() . " ( ";
            $sql .= "`" . implode("`,`", $fieldsName) . "` )";
            $fields = [];
            foreach ($fieldsName as $value) {
                if (is_string($value) && (strtolower($value) == 'created' || strtolower($value) == 'modified')) {
                    if (strtolower($value) == 'created') {
                        if (empty($this->created) || (!User::isAdmin() && !isCommandLineInterface() && empty($global['allowModifyCreated']))) {
                            $fields[] = " now() ";
                        } else {
                            $this->created = preg_replace('/[^0-9: \/-]/', '', $this->created);
                            $formats .= 's';
                            $values[] = $this->created;
                            $fields[] = " ? ";
                        }
                    } else {
                        $fields[] = " now() ";
                    }
                } elseif (is_string($value) && strtolower($value) == 'timezone') {
                    if (empty($this->$value)) {
                        eval('$this->' . $value . ' = date_default_timezone_get();');

                    }
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " ? ";
                } elseif (strtolower($value) == 'created_php_time') {
                    if (empty($this->$value)) {
                        eval('$this->' . $value . ' = time();');

                    }
                    $formats .= 'i';
                    $values[] = $this->$value;
                    $fields[] = " ? ";
                } elseif (strtolower($value) == 'modified_php_time') {
                    eval('$this->' . $value . ' = time();');
                    $formats .= 'i';
                    $values[] = $this->$value;
                    $fields[] = " ? ";
                } elseif (!isset($this->$value) || (is_string($this->$value) && strtolower($this->$value) == 'null')) {
                    $fields[] = " NULL ";
                } elseif (is_string($this->$value) || is_numeric($this->$value)) {
                    $formats .= 's';
                    $values[] = $this->$value;
                    $fields[] = " ? ";
                } else {
                    $fields[] = " NULL ";
                }
            }
            $sql .= " VALUES (" . implode(", ", $fields) . ")";
        }
        //error_log("save: $sql [$formats]".json_encode($values));
        //var_dump(static::getTableName(), $sql, $values);exit;
        //if(static::getTableName() == 'videos'){ echo $sql;var_dump($values); var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));}//return false;
        //echo $sql;var_dump($this, $values, $global['mysqli']->error);exit;
        $global['lastQuery'] = array('sql'=>$sql, 'formats'=>$formats, 'values'=>$values );
        $insert_row = sqlDAL::writeSql($sql, $formats, $values);

        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            _error_log("ObjectYPT::Error on save 1: " . $sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error . ' ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
            _error_log("ObjectYPT::Error on save 2: " . json_encode($values), AVideoLog::$ERROR);
            return false;
        }
    }

    private function getAllFields()
    {
        global $global, $mysqlDatabase;
        if (!class_exists('sqlDAL')) {
            return array();
        }
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = '" . static::getTableName() . "'";
        $res = sqlDAL::readSql($sql, "s", [$mysqlDatabase]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = [];
        if ($res !== false) {
            foreach ($fullData as $row) {
                $rows[] = $row["COLUMN_NAME"];
            }
        }
        return $rows;
    }

    public function delete()
    {
        global $global;
        if (!empty($this->id)) {

            if (!self::ignoreTableSecurityCheck() && isUntrustedRequest("DELETE " . static::getTableName())) {
                return false;
            }
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", [$this->id]);
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), AVideoLog::$ERROR);
        return false;
    }


    public static function truncateTable()
    {
        global $global;

        if (!self::ignoreTableSecurityCheck() && isUntrustedRequest("TRUNCATE " . static::getTableName())) {
            return false;
        }

        // Attempt to truncate the table
        $sql = "TRUNCATE TABLE " . static::getTableName();
        $global['lastQuery'] = $sql;

        $result = sqlDAL::writeSql($sql);
        if ($result === false) {
            // If truncation fails, delete all records
            $sql = "DELETE FROM " . static::getTableName() . " WHERE id > 0";
            $global['lastQuery'] = $sql;
            return sqlDAL::writeSql($sql);
        }

        return $result;
    }


    static function ignoreTableSecurityCheck()
    {

        $ignoreArray = [
            'vast_campaigns_logs',
            'videos',
            'CachesInDB',
            'cache_schedule_delete',
            'plugins',
            'users_login_history',
            'live_transmitions_history',
            'logincontrol_history',
            'wallet',
            'audit',
            'wallet_log',
            'live_restreams_logs',
            'live_transmitions',
            'clone_SitesAllowed',
            'user_notifications',
            'email_to_user',
            'emails_messages',
            'ai_responses',
            'ai_metatags_responses',
            'ai_transcribe_responses',
            'ai_responses_json',
            'playlists_schedules',
            'videops_logs',
            'videos_statistics'
        ];
        return in_array(static::getTableName(), $ignoreArray);
    }

    public static function shouldUseDatabase($content)
    {
        global $advancedCustom, $global, $lastShouldUseDatabaseMsg;
        $lastShouldUseDatabaseMsg = array();
        if (!empty($global['doNotUseCacheDatabase'])) {
            $lastShouldUseDatabaseMsg[] = 'Seted on $global[\'doNotUseCacheDatabase\']';
            return false;
        }
        //$maxLen = 60000; For blob
        // for medium blob
        $maxLen = 16000000;
        //$maxLen = 500000;

        if (empty($advancedCustom)) {
            $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
        }

        if (empty($advancedCustom->doNotSaveCacheOnFilesystem) && AVideoPlugin::isEnabledByName('Cache') && self::isTableInstalled('CachesInDB')) {
            $json = _json_encode($content);
            if ($json === false) {
                $lastShouldUseDatabaseMsg[] = 'Could not json encode ' . json_last_error_msg();
                //$lastShouldUseDatabaseMsg[] = $content;
                //$lastShouldUseDatabaseMsg[] = $json;
                return false;
            }
            $len = strlen($json);
            if ($len > $maxLen / 2) {
                $lastShouldUseDatabaseMsg[] = 'String is too big len=' . $len . ' size=' . humanFileSize($len);
                return false;
            }
            if (class_exists('CachesInDB')) {
                $content = CacheDB::encodeContent($json);
            } else {
                $content = base64_encode($json);
            }

            $len = strlen($content);
            if (!empty($len) && $len < $maxLen) {
                return $content;
            } elseif (!empty($len)) {
                $lastShouldUseDatabaseMsg[] = 'Final content is too big len=' . $len . ' size=' . humanFileSize($len);
                //_error_log('Object::setCache '.$len);
            }
        }

        $lastShouldUseDatabaseMsg[] = 'Finish ';
        return false;
    }

    public static function setCacheGlobal($name, $value, $addSubDirs = true)
    {
        return self::setCache($name, $value, $addSubDirs, true);
    }

    private static function logTime($start, $line, $name, $tolerance = 0.1)
    {
        global $lastShouldUseDatabaseMsg;
        $timeNow = microtime(true);
        $difference = $timeNow - $start;
        if ($difference >= $tolerance) {
            _error_log("cache logTime: {$line} $name " . number_format($difference, 3) . ' ' . json_encode($lastShouldUseDatabaseMsg), AVideoLog::$PERFORMANCE);
        }
    }

    public static function setCache($name, $value, $addSubDirs = true, $ignoreMetadata = false)
    {
        if (!isset($value) || $value == '') {
            //_error_log('Error on set cache, empty content '.$name);
            return false;
        }
        $start = microtime(true);
        if (!self::isToSaveInASubDir($name) && $content = self::shouldUseDatabase($value)) {
            $saved = Cache::_setCache($name, $content);
            self::logTime($start, __LINE__, $name);
            if (!empty($saved)) {
                //_error_log('set cache saved '.$saved);
                return "Saved on Cache::_setCache($name) at the end";
            } else {
                _error_log('Error on set cache not saved ');
            }
        }

        $content = _json_encode($value);
        if (empty($content)) {
            $content = $value;
        }

        if (empty($content) && $content !== 0) {
            _error_log('Error on set cache ' . json_encode(array($name, $value)));
            return false;
        }

        $cachefile = self::getCacheFileName($name, true, $addSubDirs, $ignoreMetadata);
        if(isCommandLineInterface()){
            echo 'setCache '.json_encode(array($name, $addSubDirs, $ignoreMetadata)).PHP_EOL;
        }
        self::logTime($start, __LINE__, $name);
        make_path($cachefile);
        //_error_log("YPTObject::setCache log error [{$name}] $cachefile filemtime = ".filemtime($cachefile));
        $bytes = @file_put_contents($cachefile, $content);
        self::logTime($start, __LINE__, $name);
        self::setSessionCache($name, $value);
        self::logTime($start, __LINE__, $name);
        return ['bytes' => $bytes, 'cachefile' => $cachefile, 'type' => 'file', 'parameters' => array($name, $addSubDirs, $ignoreMetadata)];
    }

    public static function cleanCacheName($name)
    {
        //return sha1($name);
        $parts = explode(DIRECTORY_SEPARATOR, $name);

        $lastPart = sha1(array_pop($parts));
        $parts[] = $lastPart;
        $name = implode(DIRECTORY_SEPARATOR, $parts);
        return $name;
        /*
          $name = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $name);
          $name = preg_replace('/[!#$&\'()*+,:;=?@[\\]% -]+/', '_', trim(strtolower(cleanString($name))));
          $name = preg_replace('/\/{2,}/', '/', trim(strtolower(cleanString($name))));
          if (function_exists('mb_ereg_replace')) {
          $name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).\\/\\\])", '', $name);
          // Remove any runs of periods (thanks falstro!)
          $name = mb_ereg_replace("([\.]{2,})", '', $name);
          }
          return preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
         * */
    }

    public static function getCacheGlobal($name, $lifetime = 60, $ignoreSessionCache = false, $addSubDirs = true)
    {
        return self::getCache($name, $lifetime, $ignoreSessionCache, $addSubDirs, true);
    }

    /**
     *
     * @param string $name
     * @param int $lifetime, if is = 0 it is unlimited
     * @return object|string
     */
    public static function getCache($name, $lifetime = 60, $ignoreSessionCache = false, $addSubDirs = true, $ignoreMetadata = false)
    {
        global $global;
        if (!empty($global['ignoreAllCache'])) {
            return null;
        }
        if (isCommandLineInterface() && empty($global['forceGetCache'])) {
            self::setLastUsedCacheMode("No cache detected isCommandLineInterface $name, $lifetime, " . intval($ignoreSessionCache));
            return null;
        }
        self::setLastUsedCacheMode("No cache detected $name, $lifetime, " . intval($ignoreSessionCache));
        if (isBot()) {
            $lifetime = 0;
        }
        global $getCachesProcessed, $_getCache;

        if (empty($_getCache)) {
            $_getCache = [];
        }

        if (empty($getCachesProcessed)) {
            $getCachesProcessed = [];
        }
        //if($name=='getVideosURL_V2video_220721204450_v21b7'){var_dump($name);exit;}
        $cachefile = self::getCacheFileName($name, false, $addSubDirs, $ignoreMetadata);
        if(isCommandLineInterface()){
            echo 'getCache '.json_encode(array($name, $addSubDirs, $ignoreMetadata)).PHP_EOL;
        }
        //if($name=='getVideosURL_V2video_220721204450_v21b7'){var_dump($cachefile);exit;}//exit;
        self::setLastUsedCacheFile($cachefile);
        //_error_log("getCache: cachefile [$name] ".$cachefile);
        if (!empty($_getCache[$name])) {
            //_error_log('getCache: '.__LINE__);
            self::setLastUsedCacheMode("Global Variable \$_getCache[$name]");
            return $_getCache[$name];
        }

        if (empty($getCachesProcessed[$name])) {
            $getCachesProcessed[$name] = 0;
        }
        $getCachesProcessed[$name]++;

        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }

        if (empty($ignoreSessionCache) && empty($global['ignoreSessionCache'])) {
            $session = self::getSessionCache($name, $lifetime);
            if (!empty($session)) {
                self::setLastUsedCacheMode("Session cache \$_SESSION['user']['sessionCache'][$name]");
                $_getCache[$name] = $session;
                //_error_log('getCache: '.__LINE__);
                return $session;
            }
        }

        if (class_exists('Cache')) {
            $cache = Cache::getCache($name, $lifetime, $ignoreMetadata);
            //var_dump($name, $lifetime, $ignoreMetadata);
            if (!empty($cache)) {
                //if(preg_match('/live/', $name)){_error_log("getCache 1: stats [$name] lifetime=$lifetime filemtime=".filemtime($cachefile)." ".$cachefile);}
                self::setLastUsedCacheMode("Cache::getCache($name, $lifetime, $ignoreMetadata)");
                return $cache;
            }
        }

        /*
          if (preg_match('/firstpage/i', $cachefile)) {
          echo var_dump($cachefile) . PHP_EOL;
          $trace = debug_backtrace();
          $backtrace_lite = array();
          foreach ($trace as $call) {
          echo $call['function'] . "    " . $call['file'] . "    line " . $call['line'] . PHP_EOL;
          }exit;
          }
          /**
         */
        if (file_exists($cachefile) && (empty($lifetime) || time() - $lifetime <= filemtime($cachefile))) {
            //if(preg_match('/live/', $name)){_error_log("getCache 2: stats [$name] lifetime=$lifetime filemtime=".filemtime($cachefile)." ".$cachefile);}
            self::setLastUsedCacheMode("Local File $cachefile");
            $c = @url_get_contents($cachefile);
            $json = _json_decode($c);

            if (empty($json) && !is_object($json) && !is_array($json)) {
                $json = $c;
            }

            self::setSessionCache($name, $json);
            $_getCache[$name] = $json;
            //_error_log('getCache: '.__LINE__);
            return $json;
        } elseif (file_exists($cachefile) && !empty($lifetime)) {
            self::deleteCache($name);
            @unlink($cachefile);
        }
        //var_dump(file_exists($cachefile), $cachefile);
        //if(preg_match('/getChannelsWithMoreViews30/i', $name)){var_dump($name, $cachefile, file_exists($cachefile) , $lifetime, time() - $lifetime, filemtime($cachefile));exit;}
        //_error_log("YPTObject::getCache log error [{$name}] $cachefile filemtime = ".filemtime($cachefile));
        return null;
    }

    private static function setLastUsedCacheMode($mode)
    {
        global $_lastCacheMode;
        $_lastCacheMode = $mode;
    }

    private static function setLastUsedCacheFile($cachefile)
    {
        global $_lastCacheFile;
        $_lastCacheFile = $cachefile;
    }

    public static function getLastUsedCacheInfo()
    {
        global $_lastCacheFile, $_lastCacheMode;
        return ['file' => $_lastCacheFile, 'mode' => $_lastCacheMode];
    }

    public static function deleteCache($name, $addSubDirs = true)
    {
        if (empty($name)) {
            return false;
        }
        if (!class_exists('Cache')) {
            AVideoPlugin::loadPlugin('Cache');
        }

        if (class_exists('Cache')) {
            Cache::deleteCache($name);
        }
        global $__getAVideoCache;
        unset($__getAVideoCache);
        //_error_log('deleteCache: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $cachefile = self::getCacheFileName($name, false, $addSubDirs);
        @unlink($cachefile);
        self::deleteSessionCache($name);
        ObjectYPT::deleteCacheFromPattern($name);
    }

    public static function deleteCachePattern($pattern)
    {
        global $__getAVideoCache;
        unset($__getAVideoCache);
        $tmpDir = self::getCacheDir();
        $array = _glob($tmpDir, $pattern);
        _error_log('deleteCachePattern: ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        foreach ($array as $value) {
            _error_log("Object::deleteCachePattern file [{$value}]");
            @unlink($value);
        }
        _session_start();
        foreach ($_SESSION['user']['sessionCache'] as $key => $value) {
            if (preg_match($pattern, $key)) {
                _error_log("Object::deleteCachePattern session [{$key}]");
                $_SESSION['user']['sessionCache'][$key] = null;
                unset($_SESSION['user']['sessionCache'][$key]);
            }
        }
    }

    public static function deleteALLCache()
    {
        if (!class_exists('Cache')) {
            AVideoPlugin::loadPluginIfEnabled('Cache');
        }
        if (class_exists('Cache')) {
            Cache::deleteAllCache();
        }
        self::deleteAllSessionCache();
        $lockFile = getVideosDir() . '.deleteALLCache.lock';
        if (file_exists($lockFile) && filectime($lockFile) > strtotime('-5 minutes')) {
            _error_log('clearCache is in progress ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            return false;
        }
        $start = microtime(true);
        _error_log('deleteALLCache starts ');
        global $__getAVideoCache;
        unset($__getAVideoCache);
        //_error_log('deleteALLCache: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $tmpDir = self::getCacheDir('', false);

        $newtmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . uniqid();
        _error_log("deleteALLCache rename($tmpDir, $newtmpDir) ");
        @rename($tmpDir, $newtmpDir);
        if (is_dir($tmpDir)) {
            _error_log('deleteALLCache 1 rmdir ' . $tmpDir);
            @rrmdir($tmpDir);
        } elseif (preg_match('/videos.cache/', $newtmpDir)) {
            // only delete if it is on the videos dir. otherwise it is on the /tmp dit and the system will delete it
            _error_log('deleteALLCache 2 rmdir ' . $newtmpDir);
            rrmdirCommandLine($newtmpDir, true);
        }
        self::setLastDeleteALLCacheTime();
        @unlink($lockFile);
        $end = microtime(true) - $start;
        _error_log("deleteALLCache end in {$end} seconds");
        return true;
    }

    private static function isToSaveInASubDir($filename)
    {
        return str_starts_with($filename, '/') || str_ends_with($filename, '/');
    }

    public static function getTmpCacheDir()
    {
        $tmpDir = getTmpDir();
        $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $tmpDir .= "YPTObjectCache" . DIRECTORY_SEPARATOR;
        return $tmpDir;
    }

    public static function getCacheDir($filename = '', $createDir = true, $addSubDirs = true, $ignoreMetadata = false)
    {
        global $_getCacheDir, $global;

        if (!isset($_getCacheDir)) {
            $_getCacheDir = [];
        }

        if (!empty($_getCacheDir[$filename])) {
            return $_getCacheDir[$filename];
        }

        $tmpDir = self::getTmpCacheDir();
        if (self::isToSaveInASubDir($filename)) {
            $addSubDirs = false;
            $filename = trim($filename, '/');
        }
        $filename = self::cleanCacheName($filename);
        if (!empty($filename)) {
            $tmpDir .= $filename . DIRECTORY_SEPARATOR;
            if ($addSubDirs) {
                $domain = getDomain();
                // make sure you separete http and https cache
                $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
                $tmpDir .= "{$protocol}_{$domain}" . DIRECTORY_SEPARATOR;
                if (class_exists("User_Location")) {
                    $loc = User_Location::getThisUserLocation();
                    if (!empty($loc) && !empty($loc['country_code']) && $loc['country_code'] !== '-') {
                        $tmpDir .= $loc['country_code'] . DIRECTORY_SEPARATOR;
                    }
                }
                if (empty($ignoreMetadata)) {
                    if (User::isLogged()) {
                        if (User::isAdmin()) {
                            $tmpDir .= 'admin_' . md5("admin" . $global['salt']) . DIRECTORY_SEPARATOR;
                        } else {
                            $tmpDir .= 'user_' . md5("user" . $global['salt']) . DIRECTORY_SEPARATOR;
                        }
                    } else {
                        $tmpDir .= 'notlogged_' . md5("notlogged" . $global['salt']) . DIRECTORY_SEPARATOR;
                    }
                }
            }
        }
        $tmpDir = fixPath($tmpDir);
        if ($createDir) {
            make_path($tmpDir);
        }
        if (!file_exists($tmpDir . "index.html") && is_writable($tmpDir)) { // to avoid search into the directory
            _file_put_contents($tmpDir . "index.html", time());
        }

        $_getCacheDir[$filename] = $tmpDir;
        return $tmpDir;
    }

    public static function getCacheFileName($name, $createDir = true, $addSubDirs = true, $ignoreMetadata = false)
    {
        global $global;
        $tmpDir = self::getCacheDir($name, $createDir, $addSubDirs, $ignoreMetadata);
        $uniqueHash = sha1($name . $global['salt']); // add salt for security reasons
        return $tmpDir . $uniqueHash . '_' . getDeviceName('web') . '.cache';
    }

    public static function deleteCacheFromPattern($name)
    {
        if (empty($name)) {
            return false;
        }
        $tmpDir = getTmpDir();
        //_error_log('deleteCacheFromPattern: '.json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $name = self::cleanCacheName($name);
        $ignoreLocationDirectoryName = (strpos($name, DIRECTORY_SEPARATOR) !== false);
        $filePattern = $tmpDir . DIRECTORY_SEPARATOR . $name;
        foreach (glob("{$filePattern}*") as $filename) {
            unlink($filename);
        }
        self::deleteSessionCache($name);
    }

    /**
     * Make sure you start the session before any output
     * @param string $name
     * @param string $value
     */
    public static function setSessionCache($name, $value)
    {
        $name = self::cleanCacheName($name);
        _session_start();
        $_SESSION['user']['sessionCache'][$name]['value'] = json_encode($value);
        $_SESSION['user']['sessionCache'][$name]['time'] = time();
        if (empty($_SESSION['user']['sessionCache']['time'])) {
            $_SESSION['user']['sessionCache']['time'] = time();
        }
    }

    /**
     *
     * @param string $name
     * @param string $lifetime, if is = 0 it is unlimited
     * @return string
     */
    public static function getSessionCache($name, $lifetime = 60)
    {
        $name = self::cleanCacheName($name);
        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }
        if (!empty($_SESSION['user']['sessionCache'][$name])) {
            if ((empty($lifetime) || time() - $lifetime <= $_SESSION['user']['sessionCache'][$name]['time'])) {
                $c = $_SESSION['user']['sessionCache'][$name]['value'];
                self::setLastUsedCacheMode("Session cache \$_SESSION['user']['sessionCache'][$name]");
                $json = _json_decode($c);
                if (is_string($json) && strtolower($json) === 'false') {
                    $json = false;
                }
                return $json;
            }
            _session_start();
            unset($_SESSION['user']['sessionCache'][$name]);
        }
        return null;
    }

    public static function clearSessionCache()
    {
        unset($_SESSION['user']['sessionCache']);
    }

    private static function getLastDeleteALLCacheTimeFile()
    {
        $tmpDir = getTmpDir();
        $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . "/";
        $tmpDir .= "lastDeleteALLCacheTime.cache";
        return $tmpDir;
    }

    public static function setLastDeleteALLCacheTime()
    {
        $file = self::getLastDeleteALLCacheTimeFile();
        //_error_log("ObjectYPT::setLastDeleteALLCacheTime {$file}");
        return @file_put_contents($file, time());
    }

    public static function getLastDeleteALLCacheTime()
    {
        global $getLastDeleteALLCacheTime;
        if (empty($getLastDeleteALLCacheTime)) {
            $getLastDeleteALLCacheTime = (int) @file_get_contents(self::getLastDeleteALLCacheTimeFile(), time());
        }
        return $getLastDeleteALLCacheTime;
    }

    public static function checkSessionCacheBasedOnLastDeleteALLCacheTime()
    {
        /*
          var_dump(
          $session_var['time'],
          self::getLastDeleteALLCacheTime(),
          humanTiming($session_var['time']),
          humanTiming(self::getLastDeleteALLCacheTime()),
          $session_var['time'] <= self::getLastDeleteALLCacheTime());
         *
         */
        if (empty($_SESSION['user']['sessionCache']['time']) || $_SESSION['user']['sessionCache']['time'] <= self::getLastDeleteALLCacheTime()) {
            self::deleteAllSessionCache();
            return false;
        }
        return true;
    }

    public static function deleteSessionCache($name)
    {
        $name = self::cleanCacheName($name);
        _session_start();
        $_SESSION['user']['sessionCache'][$name] = null;
        unset($_SESSION['user']['sessionCache'][$name]);
    }

    public static function deleteAllSessionCache()
    {
        _session_start();
        unset($_SESSION['user']['sessionCache']);
        return empty($_SESSION['user']['sessionCache']);
    }

    public function tableExists()
    {
        return self::isTableInstalled();
    }

    public static function isTableInstalled($tableName = "")
    {
        global $global, $tableExists;
        if (!class_exists('sqlDAL')) {
            return false;
        }
        if (empty($tableName)) {
            $tableName = static::getTableName();
        }
        if (empty($tableName)) {
            return false;
        }
        if (!isset($tableExists[$tableName])) {
            $sql = "SHOW TABLES LIKE '" . $tableName . "'";
            //_error_log("isTableInstalled: ({$sql})");
            $res = sqlDAL::readSql($sql);
            $result = sqlDal::num_rows($res);
            sqlDAL::close($res);
            $tableExists[$tableName] = !empty($result);
        }
        return $tableExists[$tableName];
    }

    public static function clientTimezoneToDatabaseTimezone($clientDate)
    {
        if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $clientDate)) {
            return $clientDate;
        }

        global $timezoneOriginal;
        $currentTimezone = date_default_timezone_get();
        $time = strtotime($clientDate);
        date_default_timezone_set($timezoneOriginal);

        $dbDate = date('Y-m-d H:i:s', $time);

        date_default_timezone_set($currentTimezone);
        return $dbDate;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
    }

    public static function __set_state($state)
    {
        // not sure where comes from this error
        $obj = new self();
        $obj->properties = $state['properties'];
        return $obj;
    }


    static function isTableEmpty()
    {
        $sql = "SELECT 1 FROM `" . static::getTableName() . "` LIMIT 1";
        $res = sqlDAL::readSql($sql);
        if ($res) {
            $row = sqlDAL::fetchAssoc($res);
            sqlDAL::close($res);
            return $row ? false : true; // Return false if a row exists (table is not empty), true if no row exists (table is empty)
        } else {
            return true; // Assume the table is empty if the query fails
        }
    }
}
abstract class CacheHandler
{

    protected $suffix;
    protected $maxCacheRefresh = 10;
    static $cachedResults = 0;

    protected function getCacheName($suffix)
    {
        if (!is_string($suffix)) {
            $suffix = json_encode($suffix);
        }
        $suffix = md5($suffix) . '_' . getRequestUniqueString();
        return $this->getCacheSubdir() . "{$suffix}.cache";
    }

    public function setCache($value)
    {
        $name = $this->getCacheName($this->suffix);

        if(isCommandLineInterface()){
            //echo "public function setCache({$this->suffix}) name=".$name.PHP_EOL;
            //echo json_encode(debug_backtrace());
        }
        $return = ObjectYPT::setCacheGlobal($name, $value);
        /*
        if (empty($return) || ($return['type'] == 'file' && empty($return['bytes']))) {
            _error_log("setCache {$this->suffix} " . json_encode($return));
        }
        */
        return $return;
    }

    public function getCache($suffix, $lifetime = 60, $logInfo = false)
    {
        global $_getCache;
        if (!isset($_getCache)) {
            $_getCache = array();
        }
        $this->setSuffix($suffix);
        $name = $this->getCacheName($this->suffix);
        if(isCommandLineInterface()){
            //echo "public function getCache($suffix) name=".$name.PHP_EOL;
            //echo json_encode(debug_backtrace());
        }
        if (isset($_getCache[$name])) {
            if ($logInfo) {
                _error_log("getCache($suffix, $lifetime) line=" . __LINE__);
            }
            return $_getCache[$name];
        }

        if (!empty($lifetime) && !$this->canRefreshCache()) {
            if ($logInfo) {
                _error_log("{$suffix} lifetime={$lifetime} cache will not be refreshed now line=" . __LINE__);
            }
            $lifetime = 0;
        }
        $name = $this->getCacheName($suffix);
        $cache = ObjectYPT::getCacheGlobal($name, $lifetime);
        if (!empty($cache)) {
            self::$cachedResults++;
        }
        $_getCache[$name] = $cache;
        if ($logInfo) {
            _error_log("getCache($suffix, $lifetime) name={$name} line=" . __LINE__);
        }
        return $cache;
    }

    public function deleteCache($clearFirstPageCache = false, $schedule = true)
    {
        $timeLog = __FILE__ . "::deleteCache ";
        TimeLogStart($timeLog);
        $prefix = $this->getCacheSubdir();
        if (!class_exists('CachesInDB')) {
            AVideoPlugin::loadPlugin('Cache');
        }
        if (class_exists('CachesInDB')) {
            //_error_log("deleteCache CachesInDB prefix=$prefix line=".__LINE__);
            CacheDB::deleteCacheStartingWith($prefix, $schedule);
        }
        TimeLogEnd($timeLog, __LINE__);
        unset($_SESSION['user']['sessionCache']);
        TimeLogEnd($timeLog, __LINE__);
        $dir = ObjectYPT::getTmpCacheDir() . $prefix;
        if (!$schedule) {
            TimeLogEnd($timeLog, __LINE__);
            _session_start();
            if ($clearFirstPageCache) {
                //_error_log("deleteCache clearFirstPageCache");
                clearCache(true);
            } else {
                //_error_log("deleteCache not schedule");
            }
            TimeLogEnd($timeLog, __LINE__);

            $resp = exec("rm -R {$dir}");

            TimeLogEnd($timeLog, __LINE__);

            //_error_log("deleteCache prefix=$prefix line=".__LINE__);
            return true;
        } else {
            $resp = execAsync("rm -R {$dir}");
            _error_log("deleteCache not schedule {$dir} ".json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            return false;
        }
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    abstract protected function getCacheSubdir();

    abstract protected function canRefreshCache();

    public function hasCache($suffix, $lifetime = 60)
    {
        $cache = $this->getCache($suffix, $lifetime);
        return $cache !== null;
    }
}

class VideosListCacheHandler extends CacheHandler
{
    private static $cacheRefreshCount = 0;

    private function getCacheSufix()
    {
        $cacheParameters = array(
            'noRelated',
            'APIName',
            'catName',
            'rowCount',
            'APISecret',
            'sort',
            'search',
            'searchPhrase',
            'current',
            'tags_id',
            'channelName',
            'videoType',
            'is_serie',
            'user',
            'videos_id',
            'playlist',
            'created',
            'minViews',
            'id',
            'doNotShowCatChilds',
            'doNotShowCats'
        );
        $cacheVars = array(
            'users_id' => User::getId(),
            'requestUniqueString' => getRequestUniqueString()
        );
        foreach ($cacheParameters as $value) {
            $cacheVars[$value] = @$_REQUEST[$value];
        }
        $cacheName = md5(json_encode($cacheVars));
        return $cacheName;
    }

    public function setAutoSuffix()
    {
        $this->suffix = $this->getCacheSufix();
    }

    public function getCacheWithAutoSuffix($lifetime = 60)
    {
        $suffix = $this->getCacheSufix();
        return parent::getCache($suffix, $lifetime, true);
    }

    protected function getCacheSubdir()
    {
        return "videosQueries/";
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}

class VideoCacheHandler extends CacheHandler
{

    private $filename;
    private static $cacheRefreshCount = 0;
    private $forceSaveOnDir = false;

    private function getCacheVideoFilename($filename = '', $id = 0)
    {
        global $_getCacheVideoFilename;
        if (!isset($_getCacheVideoFilename)) {
            $_getCacheVideoFilename = array();
        }
        if (empty($filename) && !empty($id)) {
            if (empty($_getCacheVideoFilename[$id])) {
                $video = new Video('', '', $id);
                $filename = $video->getFilename();
                if (!empty($filename)) {
                    $_getCacheVideoFilename[$id] = $filename;
                }
            } else {
                $filename = $_getCacheVideoFilename[$id];
            }
        }
        if (empty($filename)) {
            //var_dump($filename , $id, debug_backtrace());
            die('Filename not found');
        }
        return $filename;
    }

    public function __construct($filename = '', $id = 0, $forceSaveOnDir = false)
    {
        $this->filename = $this->getCacheVideoFilename($filename, $id);
        $this->forceSaveOnDir = $forceSaveOnDir;
    }

    protected function getCacheSubdir()
    {

        $subdir = "video/{$this->filename}/";
        if ($this->forceSaveOnDir) {
            $subdir = '/' . $subdir;
        }
        return $subdir;
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}

class CategoryCacheHandler extends CacheHandler
{

    private $id;
    private static $cacheRefreshCount = 0;

    public function __construct($id)
    {
        $this->id = intval($id);
    }

    protected function getCacheSubdir()
    {
        return "category/{$this->id}/";
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}

class UserCacheHandler extends CacheHandler
{

    private $id;
    private static $cacheRefreshCount = 0;

    public function __construct($id)
    {
        $this->id = intval($id);
    }

    protected function getCacheSubdir()
    {
        return "user/{$this->id}/";
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}

class PlayListCacheHandler extends CacheHandler
{

    private $id;
    private static $cacheRefreshCount = 0;

    public function __construct($id)
    {
        $this->id = intval($id);
    }

    protected function getCacheSubdir()
    {
        return "playlists/{$this->id}/";
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}

class PlayListUserCacheHandler extends CacheHandler
{

    private $id;
    private static $cacheRefreshCount = 0;

    public function __construct($id)
    {
        $this->id = intval($id);
    }

    protected function getCacheSubdir()
    {
        return "playlistsUser/{$this->id}/";
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}

class LiveCacheHandler extends CacheHandler
{
    private static $cacheRefreshCount = 0;
    static $cacheTypeNotificationSuffix = 'getStatsNotifications';

    protected function getCacheSubdir()
    {
        return "live/";
    }

    protected function canRefreshCache()
    {
        if (self::$cacheRefreshCount < $this->maxCacheRefresh) {  // assuming 10 is the limit
            self::$cacheRefreshCount++;
            return true;
        }
        return false;
    }
}
