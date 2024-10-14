<?php


function mysqlBeginTransaction()
{
    global $global;
    //_error_log('Begin transaction ' . getSelfURI());
    /**
     *
     * @var array $global
     * @var object $global['mysqli']
     */
    $global['mysqli']->autocommit(false);
}

function mysqlRollback()
{
    global $global;
    _error_log('Rollback transaction ' . getSelfURI(), AVideoLog::$ERROR);
    /**
     *
     * @var array $global
     * @var object $global['mysqli']
     */
    $global['mysqli']->rollback();
    $global['mysqli']->autocommit(true);
}

function mysqlCommit()
{
    global $global;
    //_error_log('Commit transaction ' . getSelfURI());
    /**
     *
     * @var array $global
     * @var object $global['mysqli']
     */
    $global['mysqli']->commit();
    $global['mysqli']->autocommit(true);
}


function getDatabaseTimezoneName()
{
    global $global, $_getDatabaseTimezoneName;
    if (isset($_getDatabaseTimezoneName)) {
        return $_getDatabaseTimezoneName;
    }
    $sql = "SELECT @@system_time_zone as time_zone";
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $_getDatabaseTimezoneName = $data['time_zone'];
    } else {
        $_getDatabaseTimezoneName = false;
    }

    $_getDatabaseTimezoneName = fixTimezone($_getDatabaseTimezoneName);

    return $_getDatabaseTimezoneName;
}



function getDatabaseTime()
{
    global $global, $_getDatabaseTime;
    if (isset($_getDatabaseTime)) {
        return $_getDatabaseTime;
    }
    $sql = "SELECT CURRENT_TIMESTAMP";
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $row = $data;
    } else {
        $row = false;
    }
    $_getDatabaseTime = strtotime($row['CURRENT_TIMESTAMP']);
    return $_getDatabaseTime;
}

/**
 * Convert a valid ISO 8601 date to MySQL format (Y-m-d H:i:s).
 * If the date is invalid, return an empty string.
 *
 * @param string $date
 * @return string
 */
function convertToMySQLDate(string $date): string
{
    // Try to parse the date with DateTime
    try {
        $dateTime = new DateTime($date);
        // Return the date in MySQL format
        return $dateTime->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        // If the date is invalid, return an empty string
        return '';
    }
}


function getMySQLDate()
{
    global $global;
    $sql = "SELECT now() as time FROM configurations LIMIT 1";
    // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $row = $data['time'];
    } else {
        $row = false;
    }
    return $row;
}

function _mysql_connect($persistent = false, $try = 0)
{
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort, $mysql_connect_was_closed, $mysql_connect_is_persistent;

    $checkValues = ['mysqlHost', 'mysqlUser', 'mysqlPass', 'mysqlDatabase'];

    foreach ($checkValues as $value) {
        if (!isset($$value)) {
            _error_log("_mysql_connect Variable NOT set $value");
        }
    }

    try {
        if (!_mysql_is_open()) {
            if(!class_exists('mysqli')){
                _error_log('ERROR: mysqli class not loaded '.php_ini_loaded_file());
                die('ERROR: mysqli class not loaded');
            }
            //_error_log('MySQL Connect '. json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
            $mysql_connect_was_closed = 0;
            $mysql_connect_is_persistent = $persistent;
            $global['mysqli'] = new mysqli(($persistent ? 'p:' : '') . $mysqlHost, $mysqlUser, $mysqlPass, '', @$mysqlPort);
            if (isCommandLineInterface() && !empty($global['createDatabase'])) {
                $createSQL = "CREATE DATABASE IF NOT EXISTS {$mysqlDatabase};";
                _error_log($createSQL);
                $global['mysqli']->query($createSQL);
            }
            $global['mysqli']->select_db($mysqlDatabase);
            if (!empty($global['mysqli_charset'])) {
                $global['mysqli']->set_charset($global['mysqli_charset']);
            }
            if (isCommandLineInterface()) {
                //_error_log("_mysql_connect HOST=$mysqlHost,DB=$mysqlDatabase");
            }
        }
    } catch (Exception $exc) {
        if (empty($try)) {
            _error_log('Error on connect, trying again [' . mysqli_connect_error() . '] IP='.getRealIpAddr());
            _mysql_close();
            sleep(5);
            return _mysql_connect($persistent, $try + 1);
        } else {
            _error_log($exc->getTraceAsString());
            include $global['systemRootPath'] . 'view/include/offlinePage.php';
            exit;
            return false;
        }
    }
    return true;
}

function _mysql_commit()
{
    global $global;
    if (_mysql_is_open()) {
        try {
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            @$global['mysqli']->commit();
        } catch (Exception $exc) {
        }
        //$global['mysqli'] = false;
    }
}

function _mysql_close()
{
    global $global, $mysql_connect_was_closed, $mysql_connect_is_persistent;
    if (!$mysql_connect_is_persistent && _mysql_is_open()) {
        //_error_log('MySQL Closed '. json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        $mysql_connect_was_closed = 1;
        try {
            /**
             *
             * @var array $global
             * @var object $global['mysqli']
             */
            @$global['mysqli']->close();
        } catch (Exception $exc) {
        }
        //$global['mysqli'] = false;
    }
}

function _mysql_is_open()
{
    global $global, $mysql_connect_was_closed;
    try {
        /**
         *
         * @var array $global
         * @var object $global['mysqli']
         */
        //if (is_object($global['mysqli']) && (empty($mysql_connect_was_closed) || !empty(@$global['mysqli']->ping()))) {
        if (!empty($global['mysqli']) && is_object($global['mysqli']) && empty($mysql_connect_was_closed) && isset($global['mysqli']->server_info) && is_resource($global['mysqli']) && get_resource_type($global['mysqli']) === 'mysql link') {
            return true;
        }
    } catch (Exception $exc) {
        return false;
    }
    return false;
}


function lockForUpdate($tableName, $condition)
{
    global $global;
    /**
     *
     * @var array $global
     * @var object $global['mysqli']
     */

    // Begin transaction if not already started
    mysqlBeginTransaction();

    // Prepare the SQL statement to lock the row
    $sql = "SELECT * FROM {$tableName} WHERE {$condition} FOR UPDATE";

    if ($result = $global['mysqli']->query($sql)) {
        if ($result->num_rows > 0) {
            // The row exists and is now locked for this transaction
            _error_log("Row locked successfully for condition: {$condition}");
            return true;
        } else {
            // No rows matched the condition, nothing to lock
            _error_log("No rows found to lock for condition: {$condition}");
            return false;
        }
    } else {
        // SQL error occurred
        _error_log("Error locking row for condition: {$condition} - " . $global['mysqli']->error, AVideoLog::$ERROR);
        return false;
    }
}

function setDefaultSort($defaultSortColumn, $defaultSortOrder)
{
    if (empty($_REQUEST['sort']) && empty($_GET['sort']) && empty($_POST['sort']) && empty($_GET['order'][0]['dir'])) {
        $_POST['sort'][$defaultSortColumn] = $defaultSortOrder;
    }
}