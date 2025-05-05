<?php
/*
  tester-execution-code
  $sql = "SELECT * FROM users WHERE id=?;";
  $result = sqlDAL::readSql($sql,"i",array(1));
  while($row = sqlDAL::fetchArray($result)){
  echo $row[2]."<br />";
  }

  OR

  while($row = sqlDAL::fetchAssoc($result)){
  echo $row['user']."<br />";
  }
 */

/*
 * Internal used class
 */

/**
 *
 * @var array $global
 * @var object $global['mysqli']
 */

class iimysqli_result
{

    public $stmt;
    public $nCols;
    public $fields;
}

global $disableMysqlNdMethods;
// this is only to test both methods more easy.
$disableMysqlNdMethods = false;

if(function_exists('isDocker') && isDocker()){
    ini_set('mysql.connect_timeout', 300);
    ini_set('default_socket_timeout', 300);
}

/*
 * This class exists for making servers avaible, which have no mysqlnd, withouth cause a performance-issue for those who have the driver.
 * It wouldn't be possible without Daan on https://stackoverflow.com/questions/31562359/workaround-for-mysqlnd-missing-driver
 */

class sqlDAL
{

    public static function executeFile($filename)
    {
        global $global, $isStandAlone;

        if($isStandAlone){
            return false;
        }
        $templine = '';
        // Read in entire file
        $lines = file($filename);
        // Loop through each line
        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '') {
                continue;
            }

            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                // Perform the query

                /**
                 *
                 * @var array $global
                 * @var object $global['mysqli']
                 */
                try {
                    if (!$global['mysqli']->query($templine)) {
                        _error_log('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />', AVideoLog::$ERROR);
                    }
                } catch (\Throwable $th) {
                    _error_log('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br /> '.$th->getMessage(), AVideoLog::$ERROR);
                }
                // Reset temp variable to empty
                $templine = '';
            }
        }
    }

    /**
     * This method is used to write to the database. It is used for INSERT, UPDATE and DELETE.
     *
     * @param [String] $preparedStatement The Sql-command
     * @param string $formats  i=int,d=doube,s=string,b=blob (http://www.php.net/manual/en/mysqli-stmt.bind-param.php)
     * @param array  $values   A array, containing the values for the prepared statement.
     * @param integer $try     A integer, used to retry the command if the MySQL server has gone away.
     * @return void
     */
    public static function writeSql($preparedStatement, $formats = "", $values = [], $try=0)
    {
        global $global, $disableMysqlNdMethods, $isStandAlone;

        if($isStandAlone){
            return false;
        }

        if (empty($preparedStatement)) {
            _error_log("writeSql empty(preparedStatement)");
            return false;
        }

        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        // make sure it does not store autid transactions
        if(strpos($preparedStatement, 'CachesInDB')===false){
            $debug = debug_backtrace();
            if (empty($debug[2]['class']) || $debug[2]['class'] !== "AuditTable" && class_exists('AVideoPlugin')) {
                $audit = AVideoPlugin::loadPluginIfEnabled('Audit');
                if (!empty($audit)) {
                    try {
                        $audit->exec(@$debug[1]['function'], @$debug[1]['class'], $preparedStatement, $formats, json_encode($values), User::getId());
                    } catch (Exception $exc) {
                        _error_log('Error in writeSql: ' . $global['mysqli']->errno . " " . $global['mysqli']->error . ' ' . $preparedStatement);
                        log_error($exc->getTraceAsString());
                    }
                }
            }
        }

        if (preg_match('/^update plugins/i', $preparedStatement) || preg_match('/^insert into plugins/i', $preparedStatement) || preg_match('/^delete from plugins/i', $preparedStatement)) {
            _error_log("Plugin updated {$preparedStatement}:" . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            if (!empty($global['lockPlugins'])) {
                _error_log("writeSql lockPlugins");
                return false;
            }
        }

        if (!_mysql_is_open()) {
            _mysql_connect();
        }

        try {
            $stmt = $global['mysqli']->prepare($preparedStatement);
        } catch (mysqli_sql_exception $e) {
            if (preg_match('/Table .*CachesInDB.* doesn\'t exist/i', $e->getMessage())) {
                _error_log("writeSql: Skipping missing table 'CachesInDB'");
                return true;
            }
            _error_log("writeSql: Exception in prepare: " . $e->getMessage());
            return false;
        }

        if (!$stmt) {
            log_error("[sqlDAL::writeSql] Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error .
                " preparedStatement = " . json_encode($preparedStatement) .
                " formats = " . json_encode($formats));
            return false;
        }
        //var_dump($preparedStatement, $formats, $values);exit;
        if (!sqlDAL::eval_mysql_bind($stmt, $formats, $values)) {
            log_error("[sqlDAL::writeSql]  eval_mysql_bind failed: values and params in stmt don't match ({$preparedStatement}) with formats ({$formats})");
            return false;
        }
        try {
            $stmt->execute();
        } catch (Exception $exc) {
            if (empty($try) && $stmt->errno == 2006) { //MySQL server has gone away
                _mysql_close();
                _mysql_connect();
                return self::writeSql($preparedStatement, $formats, $values, $try+1);
            }else if (preg_match('/playlists_has_videos/', $preparedStatement)) {
                log_error('Error in writeSql values: ' . json_encode($values));
            }else if (preg_match('/Illegal mix of collations.*and \(utf8mb4/i', $global['mysqli']->error)) {
                try {
                    // Set the MySQL connection character set to UTF-8
                    $global['mysqli']->query("SET NAMES 'utf8mb4'");
                    $global['mysqli']->query("SET CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $stmt = $global['mysqli']->prepare($preparedStatement);
                    sqlDAL::eval_mysql_bind($stmt, $formats, $values);
                    $stmt->execute();
                } catch (Exception $exc) {
                    log_error($exc->getTraceAsString());
                }
            }else if(preg_match('/Conversion from collation/i', $global['mysqli']->error)){
                $values2 = $values;
                foreach ($values2 as $key => $value) {
                    if(!is_numeric($value) && strlen($value)>20){
                        $values2[$key] = "CONVERT('{$value}' USING latin1)";
                    }
                }
                sqlDAL::eval_mysql_bind($stmt, $formats, $values2);
                try {
                    log_error('try again 1');
                    $stmt->execute();
                    log_error('try again 1 SUCCESS');
                } catch (Exception $exc) {
                    foreach ($values as $key => $value) {
                        if(strlen($value)){
                            $values[$key] = preg_replace("/[^A-Za-z0-9 ._:-]+/", ' ', $value);
                        }
                    }
                    sqlDAL::eval_mysql_bind($stmt, $formats, $values);
                    try {
                        log_error('try again 2');
                        $stmt->execute();
                        log_error('try again 2 SUCCESS');
                    } catch (Exception $exc) {
                        log_error($exc->getTraceAsString());
                        log_error('Error in writeSql stmt->execute: ' . $global['mysqli']->errno . " " . $global['mysqli']->error . ' ' . $preparedStatement);
                    }
                }
            }
        }

        if ($stmt->errno !== 0) {
            //log_error('Error in writeSql : (' . $stmt->errno . ') ' . $stmt->error . ", SQL-CMD:" . $preparedStatement);
            /*
              if(empty($global['mysqli_charset']) && preg_match('/collation utf8/', $stmt->error)){
              $global['mysqli_charset'] = 'latin1';
              _mysql_close();
              _mysql_connect();
              return self::writeSql($preparedStatement, $formats, $values);
              }
             *
             */

             _error_log(sprintf('writeSql [%s] {%s}  %s userAgent=[%s]', $stmt->errno, $stmt->error, json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)), $_SERVER['HTTP_USER_AGENT'] ?? '-'));
             if($stmt->errno == 1205 && preg_match('/CachesInDB/', $preparedStatement)){//Lock wait timeout exceeded; try restarting transaction
                _error_log("writeSql Recreate CachesInDB ");
                $sql = 'DROP TABLE IF EXISTS `CachesInDB`';
                $global['mysqli']->query($sql);
                $file = $global['systemRootPath'] . 'plugin/Cache/install/install.sql';
                sqlDal::executeFile($file);
             }
             if(preg_match('/Data truncated for column/i', $stmt->error)){
                _error_log("writeSql values = ".' '.json_encode($values));
             }
            $stmt->close();
            return false;
        }
        $iid = @$global['mysqli']->insert_id;
        //$global['mysqli']->affected_rows = $stmt->affected_rows;
        //$stmt->commit();
        $stmt->close();
        if (!empty($iid)) {
            return $iid;
        } else {
            return true;
        }
    }

    public static function writeSqlTry($preparedStatement, $formats = "", $values = [])
    {
        global $global, $isStandAlone;

        if($isStandAlone){
            return false;
        }

        /**
         * @var array $global
         * @var object $global['mysqli']
         */
        try {
            $return = self::writeSql($preparedStatement, $formats, $values);
            if(!$return){
                _error_log('Error in writeSqlTry return: ' . $global['mysqli']->errno . " " . $global['mysqli']->error . ' ' . $preparedStatement);
            }
            return $return;
        } catch (\Throwable $th) {
            _error_log('Error in writeSqlTry: ' . $global['mysqli']->errno . " " . $global['mysqli']->error . ' ' . $preparedStatement);
            _error_log('writeSqlTry: '.$th->getMessage(), AVideoLog::$ERROR);
            $search = array('COLUMN IF NOT EXISTS', 'IF NOT EXISTS');
            $replace = array('COLUMN', 'COLUMN');
            $preparedStatement = str_ireplace($search, $replace, $preparedStatement);
            try {
                return self::writeSql($preparedStatement, $formats, $values);
            } catch (\Throwable $th) {
                _error_log('Error in writeSqlTry retry: ' . $global['mysqli']->errno . " " . $global['mysqli']->error . ' ' . $preparedStatement);
                _error_log('writeSqlTry retry: '.$th->getMessage(), AVideoLog::$ERROR);
                return false;
            }
        }
    }


    static function wasSTMTError()
    {
        global $wasSTMTError;
        return $wasSTMTError;
    }

    /*
     * For Sql like SELECT. This method needs to be closed anyway. If you start another readSql, while the old is open, it will fail.
     * @param string $preparedStatement  The Sql-command
     * @param string $formats            i=int,d=doube,s=string,b=blob (http://www.php.net/manual/en/mysqli-stmt.bind-param.php)
     * @param array  $values             A array, containing the values for the prepared statement.
     * @return Object                    Depend if mysqlnd is active or not, a object, but always false on fail
     */

    public static function readSql($preparedStatement, $formats = '', $values = [], $refreshCache = false)
    {
        // $refreshCache = true;
        global $global, $disableMysqlNdMethods, $readSqlCached, $crc, $wasSTMTError, $isStandAlone;

        if($isStandAlone){
            return false;
        }

        if($refreshCache){
            $random = uniqid();
            $preparedStatement .= " /* {$random} */ ";
        }

        $wasSTMTError = false;
        // need to add dechex because some times it return an negative value and make it fails on javascript playlists
        $crc = (md5($preparedStatement . implode($values)));

        if (!isset($readSqlCached)) {
            $readSqlCached = [];
        }
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {

            // Mysqlnd enabled

            if ((!isset($readSqlCached[$crc])) || ($refreshCache)) {

                // When not cached

                /**
                 *
                 * @var array $global
                 * @var object $global['mysqli']
                 */
                $readSqlCached[$crc] = "false";
                if(!_mysql_is_open()){
                    _mysql_connect();
                }
                try {
                    $stmt = $global['mysqli']->prepare($preparedStatement);
                } catch (Exception $exc) {
                    log_error("[sqlDAL::readSql] (mysqlnd) Prepare failed: ({$global['mysqli']->errno}) ({$global['mysqli']->error}) " .
                        " preparedStatement = " . json_encode($preparedStatement) .
                        " formats = " . json_encode($formats) .
                        " values = " . json_encode($values) .
                        " refreshCache = " . json_encode($refreshCache));
                    //log_error("[sqlDAL::readSql] trying close and reconnect");
                    _mysql_close();
                    _mysql_connect();
                    try {
                        $stmt = $global['mysqli']->prepare($preparedStatement);
                        log_error("[sqlDAL::readSql] SUCCESS close and reconnect works!");
                    } catch (Exception $exc) {
                        log_error("[sqlDAL::readSql] (mysqlnd) Prepare failed again return false");
                        return false;
                    }
                }

                if (empty($stmt)) {
                    log_error("[sqlDAL::readSql] (stmt) is empty {$preparedStatement} with formats {$formats}");
                    $wasSTMTError = true;
                    return false;
                }

                if (!sqlDAL::eval_mysql_bind($stmt, $formats, $values)) {
                    log_error("[sqlDAL::readSql] (mysqlnd) eval_mysql_bind failed: values and params in stmt don't match {$preparedStatement} with formats {$formats}");
                    return false;
                }
                $TimeLog = "[$preparedStatement], $formats, " . json_encode($values) . ", $refreshCache";
                TimeLogStart($TimeLog);
                $stmt->execute();
                $readSqlCached[$crc] = $stmt->get_result();
                if ($stmt->errno !== 0) {
                    log_error('Error in readSql (mysqlnd): (' . $stmt->errno . ') ' . $stmt->error . ", SQL-CMD:" . $preparedStatement);
                    $stmt->close();
                    $disableMysqlNdMethods = true;
                    // try again with noMysqlND
                    $read = self::readSql($preparedStatement, $formats, $values, $refreshCache);
                    TimeLogEnd($TimeLog, "mysql_dal", 0.5);
                    return $read;
                }
                TimeLogEnd($TimeLog, "mysql_dal", 0.5);
                $stmt->close();
            } elseif (is_object($readSqlCached[$crc])) {

                // When cached
                // reset the stmt for fetch. this solves objects/video.php line 550
                $readSqlCached[$crc]->data_seek(0);
                //log_error("set dataseek to 0");
                // increase a counter for the saved queries.
                if (isset($_SESSION['savedQuerys'])) {
                    $_SESSION['savedQuerys']++;
                }
            } else {
                $readSqlCached[$crc] = "false";
            }

            //
            // if ($readSqlCached[$crc] == "false") {
            // add this in case the cache fail
            // ->lengths seems to be always NULL.. fix: $readSqlCached[$crc]->data_seek(0); above
            //if("SELECT * FROM configurations WHERE id = 1 LIMIT 1"==$preparedStatement){
            //  var_dump($readSqlCached[$crc]);
            //}
            if ($readSqlCached[$crc] != "false") {
                if (is_null($readSqlCached[$crc]->lengths) && !$refreshCache && $readSqlCached[$crc]->num_rows == 0 && $readSqlCached[$crc]->field_count == 0) {
                    log_error("[sqlDAL::readSql] (mysqlnd) Something was going wrong, re-get the query. {$preparedStatement} {$readSqlCached[$crc]->num_rows}");
                    return self::readSql($preparedStatement, $formats, $values, true);
                }
            } else {
                $readSqlCached[$crc] = false;
            }
            // }
        } else {

            // Mysqlnd-fallback

            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            if (!($stmt = $global['mysqli']->prepare($preparedStatement))) {
                log_error("[sqlDAL::readSql] (no mysqlnd) Prepare failed: (" . $global['mysqli']->errno . ") " . $global['mysqli']->error . " ({$preparedStatement})");
                return false;
            }

            if (!sqlDAL::eval_mysql_bind($stmt, $formats, $values)) {
                log_error("[sqlDAL::readSql] (no mysqlnd) eval_mysql_bind failed: values and params in stmt don't match {$preparedStatement} with formats {$formats}");
                return false;
            }

            $stmt->execute();
            $result = self::iimysqli_stmt_get_result($stmt);
            if ($stmt->errno !== 0) {
                log_error('Error in readSql (no mysqlnd): (' . $stmt->errno . ') ' . $stmt->error . ", SQL-CMD:" . $preparedStatement);
                $stmt->close();
                $readSqlCached[$crc] = false;
            } else {
                $readSqlCached[$crc] = $result;
            }
        }
        return $readSqlCached[$crc];
    }

    /*
     * This closes the readSql
     * @param Object $result A object from sqlDAL::readSql
     */

    public static function close($result)
    {
        global $disableMysqlNdMethods, $global, $isStandAlone;

        if($isStandAlone){
            return false;
        }
        if ((!function_exists('mysqli_fetch_all')) || ($disableMysqlNdMethods !== false)) {
            if (!empty($result->stmt)) {
                $result->stmt->close();
            }
        }
    }

    /*
     * Get the nr of rows
     * @param Object $result A object from sqlDAL::readSql
     * @return int           The nr of rows
     */

    public static function num_rows($res)
    {
        global $global, $disableMysqlNdMethods, $crc, $num_row_cache, $isStandAlone;

        if($isStandAlone){
            return false;
        }
        if (!isset($num_row_cache)) {
            $num_row_cache = [];
        }
        // cache is working - but disable for proper test-results
        if (!isset($num_row_cache[$crc])) {
            if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
                // Mysqlnd
                $num_row_cache[$crc] = 0;
                if (!empty($res->num_rows)) {
                    $num_row_cache[$crc] = $res->num_rows;
                }
                return $num_row_cache[$crc];
            } else {
                // Mysqlnd-fallback - use fetchAllAssoc because this can be cached.
                $num_row_cache[$crc] = sizeof(self::fetchAllAssoc($res));
            }
        }
        return $num_row_cache[$crc];
    }

    // unused
    public static function cached_num_rows($data)
    {
        return sizeof($data);
    }

    /*
     * Make a fetch assoc on every row avaible
     * @param Object $result A object from sqlDAL::readSql
     * @return array           A array filled with all rows as a assoc array
     */

    public static function fetchAllAssoc($result)
    {
        global $crc, $fetchAllAssoc_cache, $isStandAlone;

        if($isStandAlone){
            return array();
        }
        if (!isset($fetchAllAssoc_cache)) {
            $fetchAllAssoc_cache = [];
        }
        if (!isset($fetchAllAssoc_cache[$crc])) {
            $ret = [];
            while ($row = self::fetchAssoc($result)) {
                $ret[] = $row;
            }
            $fetchAllAssoc_cache[$crc] = $ret;
        }
        return $fetchAllAssoc_cache[$crc];
    }

    /*
     * Make a single assoc fetch
     * @param Object $result A object from sqlDAL::readSql
     * @return int           A single row in a assoc array
     */

    public static function fetchAssoc($result)
    {
        global $global, $disableMysqlNdMethods, $isStandAlone;

        if($isStandAlone){
            return false;
        }
        ini_set('memory_limit', '-1');
        // here, a cache is more/too difficult, because fetch gives always a next. with this kind of cache, we would give always the same.
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            if ($result !== false) {
                try {
                    return $result->fetch_assoc();
                } catch (\Throwable $th) {
                    if (preg_match('/playlists_has_videos/', $th->getMessage())) {
                        try {
                            return $result->fetch_assoc();
                        } catch (\Throwable $th) {
                            if (preg_match('/MySQL server has gone away/i', $th->getMessage())) {
                                _mysql_close();
                                _mysql_connect();
                                return $result->fetch_assoc();
                            }
                            _error_log('fetchAssoc Error1: '.$th->getMessage(), AVideoLog::$ERROR);
                            return false;
                        }
                    }
                    _error_log('fetchAssoc Error2: '.$th->getMessage(), AVideoLog::$ERROR);
                    return false;
                }
            }
        } else {
            return self::iimysqli_result_fetch_assoc($result);
        }
        return false;
    }

    /*
     * Make a fetchArray on every row avaible
     * @param Object $result A object from sqlDAL::readSql
     * @return array           A array filled with all rows
     */

    public static function fetchAllArray($result)
    {
        global $crc, $fetchAllArray_cache, $isStandAlone;

        if($isStandAlone){
            return false;
        }
        if (!isset($fetchAllArray_cache)) {
            $fetchAllArray_cache = [];
        }
        // cache is working - but disable for proper test-results
        if (!isset($fetchAllArray_cache[$crc])) {
            $ret = [];
            while ($row = self::fetchArray($result)) {
                $ret[] = $row;
            }
            $fetchAllArray_cache[$crc] = $ret;
        } else {
            log_error("array-cache");
        }
        return $fetchAllArray_cache[$crc];
    }

    /*
     * Make a single fetch
     * @param Object $result A object from sqlDAL::readSql
     * @return int           A single row in a array
     */

    public static function fetchArray($result)
    {
        global $global, $disableMysqlNdMethods, $isStandAlone;

        if($isStandAlone){
            return false;
        }
        if ((function_exists('mysqli_fetch_all')) && ($disableMysqlNdMethods == false)) {
            return $result->fetch_array();
        } else {
            return self::iimysqli_result_fetch_array($result);
        }
        return false;
    }

    private static function eval_mysql_bind($stmt, $formats, $values)
    {
        if (($stmt->param_count != sizeof($values)) || ($stmt->param_count != strlen($formats))) {
            return false;
        }
        if ((!empty($formats)) && (!empty($values))) {
            $code = "return \$stmt->bind_param(\"" . $formats . "\"";
            $i = 0;
            foreach ($values as $val) {
                $code .= ", \$values[" . $i . "]";
                $i++;
            };
            $code .= ");";
            // echo $code. " : ".$preparedStatement;
            eval($code);
        }
        return true;
    }

    private static function iimysqli_stmt_get_result($stmt)
    {
        global $global;
        $metadata = mysqli_stmt_result_metadata($stmt);
        $ret = new iimysqli_result();
        $field_array = [];
        if (!$metadata) {
            die("Execute query error, because: {$stmt->error}");
        }
        $tmpFields = $metadata->fetch_fields();
        $i = 0;
        foreach ($tmpFields as $f) {
            $field_array[$i] = $f->name;
            $i++;
        }
        $ret->fields = $field_array;
        if (!$ret) {
            return null;
        }

        $ret->nCols = mysqli_num_fields($metadata);

        $ret->stmt = $stmt;

        mysqli_free_result($metadata);
        return $ret;
    }

    private static function iimysqli_result_fetch_assoc(&$result)
    {
        global $global;
        $ret = [];
        $code = "return mysqli_stmt_bind_result(\$result->stmt ";
        for ($i = 0; $i < $result->nCols; $i++) {
            $ret[$result->fields[$i]] = null;
            $code .= ", \$ret['" . $result->fields[$i] . "']";
        };

        $code .= ");";
        if (!eval($code)) {
            return false;
        };
        if (!mysqli_stmt_fetch($result->stmt)) {
            return false;
        };
        return $ret;
    }

    private static function iimysqli_result_fetch_array(&$result)
    {
        $ret = [];
        $code = "return mysqli_stmt_bind_result(\$result->stmt ";

        for ($i = 0; $i < $result->nCols; $i++) {
            $ret[$i] = null;
            $code .= ", \$ret['" . $i . "']";
        };
        $code .= ");";
        if (!eval($code)) {
            return false;
        };
        if (!mysqli_stmt_fetch($result->stmt)) {
            return false;
        };
        return $ret;
    }
}

function log_error($err)
{
    global $global;
    if (!empty($global['debug']) || isCommandLineInterface()) {
        echo $err;
    }

    _error_log("MySQL ERROR: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)), AVideoLog::$ERROR);
    _error_log($err, AVideoLog::$ERROR);
}
