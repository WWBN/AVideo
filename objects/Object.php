<?php

interface ObjectInterface {

    public static function getTableName();

    public static function getSearchFieldsNames();
}

$tableExists = array();

abstract class ObjectYPT implements ObjectInterface {

    protected $fieldsName = array();

    public function __construct($id = "") {
        if (!empty($id)) {
            // get data from id
            $this->load($id);
        }
    }

    protected function load($id) {
        $row = self::getFromDb($id);
        if (empty($row)) {
            return false;
        }
        foreach ($row as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    public static function getNowFromDB() {
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

    public static function setTimeZone() {
        global $advancedCustom;
        $row = self::getNowFromDB();
        $dt = new DateTime($row['my_date_field']);
        $timeZOnesOptions = object_to_array($advancedCustom->timeZone->type);
        if (empty($timeZOnesOptions[$advancedCustom->timeZone->value])) {
            return false;
        }
        try {
            $objDate = new DateTimeZone($timeZOnesOptions[$advancedCustom->timeZone->value]);
            if (is_object($objDate)) {
                $dt->setTimezone($objDate);
                date_default_timezone_set($timeZOnesOptions[$advancedCustom->timeZone->value]);
                return $dt;
            }
            return false;
        } catch (Exception $exc) {
            _error_log("setTimeZone: " . $exc->getMessage(), AVideoLog::$ERROR);
            return false;
        }
    }

    protected static function getFromDb($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM " . static::getTableName() . " WHERE  id = ? LIMIT 1";
        // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
        $res = sqlDAL::readSql($sql, "i", array($id), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res) {
            $row = $data;
        } else {
            $row = false;
        }
        return $row;
    }

    public static function getAll() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getAllActive() {
        global $global;
        if (!static::isTableInstalled()) {
            return false;
        }
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE status='a' ";

        $sql .= self::getSqlFromPost();
        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public static function getTotal() {
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

    public static function getSqlFromPost($keyPrefix = "") {
        global $global;
        $sql = self::getSqlSearchFromPost();

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

        if (!empty($_POST['sort'])) {
            $orderBy = array();
            foreach ($_POST['sort'] as $key => $value) {
                $key = $global['mysqli']->real_escape_string($key);
                //$value = $global['mysqli']->real_escape_string($value);
                $direction = "ASC";
                if (strtoupper($value) === "DESC") {
                    $direction = "DESC";
                }
                $key = preg_replace("/[^A-Za-z0-9._ ]/", '', $key);
                $orderBy[] = " {$keyPrefix}{$key} {$value} ";
            }
            $sql .= " ORDER BY " . implode(",", $orderBy);
        }

        $sql .= self::getSqlLimit();
        return $sql;
    }

    public static function getSqlLimit() {
        global $global;
        $sql = "";

        if (empty($_POST['rowCount']) && !empty($_GET['length'])) {
            $_POST['rowCount'] = intval($_GET['length']);
        }

        if (empty($_POST['current']) && !empty($_GET['start'])) {
            $_POST['current'] = ($_GET['start'] / $_GET['length']) + 1;
        } elseif (empty($_POST['current']) && isset($_GET['start'])) {
            $_POST['current'] = 1;
        }

        $_POST['current'] = getCurrentPage();
        $_POST['rowCount'] = getRowCount();

        if (!empty($_POST['rowCount']) && !empty($_POST['current']) && $_POST['rowCount'] > 0) {
            $_POST['rowCount'] = intval($_POST['rowCount']);
            $_POST['current'] = intval($_POST['current']);
            $current = ($_POST['current'] - 1) * $_POST['rowCount'];
            $current = $current < 0 ? 0 : $current;
            $sql .= " LIMIT $current, {$_POST['rowCount']} ";
        } else {
            $_POST['current'] = 0;
            $_POST['rowCount'] = 0;
            $sql .= " LIMIT 1000 ";
        }
        return $sql;
    }

    public static function getSqlSearchFromPost() {
        $sql = "";
        if (!empty($_POST['searchPhrase'])) {
            $_GET['q'] = $_POST['searchPhrase'];
        } elseif (!empty($_GET['search']['value'])) {
            $_GET['q'] = $_GET['search']['value'];
        }
        if (!empty($_GET['q'])) {
            global $global;
            $search = $global['mysqli']->real_escape_string(xss_esc($_GET['q']));

            $like = array();
            $searchFields = static::getSearchFieldsNames();
            foreach ($searchFields as $value) {
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

    public function save() {
        if (!$this->tableExists()) {
            _error_log("Save error, table " . static::getTableName() . " does not exists", AVideoLog::$ERROR);
            return false;
        }
        global $global;
        $fieldsName = $this->getAllFields();
        if (!empty($this->id)) {
            $sql = "UPDATE " . static::getTableName() . " SET ";
            $fields = array();
            foreach ($fieldsName as $value) {
                if (strtolower($value) == 'created') {
                    // do nothing
                } elseif (strtolower($value) == 'modified') {
                    $fields[] = " {$value} = now() ";
                } elseif (is_numeric($this->$value)) {
                    $fields[] = " `{$value}` = {$this->$value} ";
                } elseif (strtolower($this->$value) == 'null') {
                    $fields[] = " `{$value}` = NULL ";
                } else {
                    $fields[] = " `{$value}` = '{$this->$value}' ";
                }
            }
            $sql .= implode(", ", $fields);
            $sql .= " WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO " . static::getTableName() . " ( ";
            $sql .= "`" . implode("`,`", $fieldsName) . "` )";
            $fields = array();
            foreach ($fieldsName as $value) {
                if (strtolower($value) == 'created' || strtolower($value) == 'modified') {
                    $fields[] = " now() ";
                } elseif (!isset($this->$value) || strtolower($this->$value) == 'null') {
                    $fields[] = " NULL ";
                } else {
                    $fields[] = " '{$this->$value}' ";
                }
            }
            $sql .= " VALUES (" . implode(", ", $fields) . ")";
        }
        //if(static::getTableName() == 'subscriptions') echo $sql;
        $insert_row = sqlDAL::writeSql($sql);

        if ($insert_row) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            _error_log("ObjectYPT::save Error on save: " . $sql . ' Error : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error, AVideoLog::$ERROR);
            return false;
        }
    }

    private function getAllFields() {
        global $global, $mysqlDatabase;
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = '" . static::getTableName() . "'";
        $res = sqlDAL::readSql($sql, "s", array($mysqlDatabase));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $rows = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $rows[] = $row["COLUMN_NAME"];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    public function delete() {
        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM " . static::getTableName() . " ";
            $sql .= " WHERE id = ?";
            $global['lastQuery'] = $sql;
            //_error_log("Delete Query: ".$sql);
            return sqlDAL::writeSql($sql, "i", array($this->id));
        }
        _error_log("Id for table " . static::getTableName() . " not defined for deletion", AVideoLog::$ERROR);
        return false;
    }

    public static function setCache($name, $value) {
        $cachefile = self::getCacheFileName($name);
        make_path($cachefile);
        
        $content = _json_encode($value);
        if(empty($content)){
            $content = $value;
        }
        
        $bytes = @file_put_contents($cachefile, $content);
        self::setSessionCache($name, $value);
        return array('bytes' => $bytes, 'cachefile' => $cachefile);
    }

    public static function cleanCacheName($name) {
        $name = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $name);
        $name = preg_replace('/[!#$&\'()*+,:;=?@[\\]% -]+/', '_', trim(strtolower(cleanString($name))));
        $name = preg_replace('/\/{2,}/', '/', trim(strtolower(cleanString($name))));
        if (function_exists('mb_ereg_replace')) {
            $name = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).\\/\\\])", '', $name);
            // Remove any runs of periods (thanks falstro!)
            $name = mb_ereg_replace("([\.]{2,})", '', $name);
        }
        return preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
    }

    /**
     *
     * @param type $name
     * @param type $lifetime, if is = 0 it is unlimited
     * @return type
     */
    public static function getCache($name, $lifetime = 60, $ignoreSessionCache = false) {
        if (isCommandLineInterface()) {
            return false;
        }
        global $getCachesProcessed, $_getCache;

        if (empty($_getCache)) {
            $_getCache = array();
        }

        if (empty($getCachesProcessed)) {
            $getCachesProcessed = array();
        }
        $cachefile = self::getCacheFileName($name);

        if (!empty($_getCache[$name])) {
            return $_getCache[$name];
        }

        if (empty($getCachesProcessed[$name])) {
            $getCachesProcessed[$name] = 0;
        }
        $getCachesProcessed[$name]++;

        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }

        if (empty($ignoreSessionCache)) {
            $session = self::getSessionCache($name, $lifetime);
            if (!empty($session)) {
                $_getCache[$name] = $session;
                return $session;
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
            //if(preg_match('/getStats/', $cachefile)){echo $cachefile,'<br>';}
            $c = @url_get_contents($cachefile);
            $json = _json_decode($c);
            
            if(empty($json) && !is_object($json) && !is_array($json)){
                $json = $c;
            }
            
            self::setSessionCache($name, $json);
            $_getCache[$name] = $json;
            return $json;
        } elseif (file_exists($cachefile)) {
            self::deleteCache($name);
        }
        return null;
    }

    public static function deleteCache($name) {
        global $__getAVideoCache;
        unset($__getAVideoCache);
        $cachefile = self::getCacheFileName($name);
        @unlink($cachefile);
        self::deleteSessionCache($name);
        ObjectYPT::deleteCacheFromPattern($name);
    }

    static function deleteCachePattern($pattern) {
        global $__getAVideoCache;
        unset($__getAVideoCache);
        $tmpDir = self::getCacheDir();
        $array = _glob($tmpDir, $pattern);
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

    public static function deleteALLCache() {
        global $__getAVideoCache;
        unset($__getAVideoCache);
        $tmpDir = self::getCacheDir();
        rrmdir($tmpDir);
        self::deleteAllSessionCache();
        self::setLastDeleteALLCacheTime();
    }

    public static function getCacheDir($filename = '') {
        global $_getCacheDir, $global;

        if (!isset($_getCacheDir)) {
            $_getCacheDir = array();
        }

        if (!empty($_getCacheDir[$filename])) {
            return $_getCacheDir[$filename];
        }

        $tmpDir = getTmpDir();
        $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $tmpDir .= "YPTObjectCache" . DIRECTORY_SEPARATOR;

        $filename = self::cleanCacheName($filename);
        if (!empty($filename)) {
            $tmpDir .= $filename . DIRECTORY_SEPARATOR;

            $domain = getDomain();
            $tmpDir .= $domain . DIRECTORY_SEPARATOR;

            // make sure you separete http and https cache 
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $tmpDir .= $protocol . DIRECTORY_SEPARATOR;
            if (class_exists("User_Location")) {
                $loc = User_Location::getThisUserLocation();
                if (!empty($loc) && !empty($loc['country_code'])) {
                    $tmpDir .= $loc['country_code'] . DIRECTORY_SEPARATOR;
                }
            }
        
            if (User::isLogged()) {
                if(User::isAdmin()){
                    $tmpDir .= 'admin_'.md5("admin".$global['salt']).DIRECTORY_SEPARATOR;
                }else{
                    $tmpDir .= 'user_'.md5("user".$global['salt']).DIRECTORY_SEPARATOR;
                }
            }else{
                $tmpDir .= 'notlogged_'.md5("notlogged".$global['salt']).DIRECTORY_SEPARATOR;
            }
        }

        make_path($tmpDir);
        if (!file_exists($tmpDir . "index.html") && is_writable($tmpDir)) {// to avoid search into the directory
            file_put_contents($tmpDir . "index.html", time());
        }

        $_getCacheDir[$filename] = $tmpDir;
        return $tmpDir;
    }

    public static function getCacheFileName($name) {
        global $global;
        $tmpDir = self::getCacheDir($name);
        $uniqueHash = md5($name . $global['salt']); // add salt for security reasons
        return $tmpDir . $uniqueHash . '.cache';
    }

    public static function deleteCacheFromPattern($name) {
        $tmpDir = getTmpDir();
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
     * @param type $name
     * @param type $value
     */
    public static function setSessionCache($name, $value) {
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
     * @param type $name
     * @param type $lifetime, if is = 0 it is unlimited
     * @return type
     */
    public static function getSessionCache($name, $lifetime = 60) {
        $name = self::cleanCacheName($name);
        if (!empty($_GET['lifetime'])) {
            $lifetime = intval($_GET['lifetime']);
        }
        if (!empty($_SESSION['user']['sessionCache'][$name])) {
            if ((empty($lifetime) || time() - $lifetime <= $_SESSION['user']['sessionCache'][$name]['time'])) {
                $c = $_SESSION['user']['sessionCache'][$name]['value'];
                $json = _json_decode($c);
                if(is_string($json) && strtolower($json) === 'false'){
                    $json = false;
                }
                return $json;
            }
            _session_start();
            unset($_SESSION['user']['sessionCache'][$name]);
        }
        return null;
    }

    public static function clearSessionCache() {
        unset($_SESSION['user']['sessionCache']);
    }

    private static function getLastDeleteALLCacheTimeFile() {
        $tmpDir = getTmpDir();
        $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . "/";
        $tmpDir .= "lastDeleteALLCacheTime.cache";
        return $tmpDir;
    }

    public static function setLastDeleteALLCacheTime() {
        $file = self::getLastDeleteALLCacheTimeFile();
        //_error_log("ObjectYPT::setLastDeleteALLCacheTime {$file}");
        return file_put_contents($file, time());
    }

    public static function getLastDeleteALLCacheTime() {
        global $getLastDeleteALLCacheTime;
        if (empty($getLastDeleteALLCacheTime)) {
            $getLastDeleteALLCacheTime = (int) @file_get_contents(self::getLastDeleteALLCacheTimeFile(), time());
        }
        return $getLastDeleteALLCacheTime;
    }

    public static function checkSessionCacheBasedOnLastDeleteALLCacheTime() {
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

    public static function deleteSessionCache($name) {
        $name = self::cleanCacheName($name);
        _session_start();
        $_SESSION['user']['sessionCache'][$name] = null;
        unset($_SESSION['user']['sessionCache'][$name]);
    }

    public static function deleteAllSessionCache() {
        _session_start();
        unset($_SESSION['user']['sessionCache']);
    }

    public function tableExists() {
        return self::isTableInstalled();
    }

    public static function isTableInstalled($tableName = "") {
        global $global, $tableExists;
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

}

//abstract class Object extends ObjectYPT{};
