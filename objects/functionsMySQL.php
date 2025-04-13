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
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort, $mysql_connect_was_closed, $mysql_connect_is_persistent, $isStandAlone;
    if (!empty($isStandAlone)) {
        _error_log('StandAlone Mode');
        return false;
    }
    $checkValues = ['mysqlHost', 'mysqlUser', 'mysqlPass', 'mysqlDatabase'];

    foreach ($checkValues as $value) {
        if (!isset($$value)) {
            _error_log("_mysql_connect Variable NOT set $value " . json_encode(debug_backtrace()));
        }
    }

    try {
        if (!_mysql_is_open()) {
            if (!class_exists('mysqli')) {
                _error_log('ERROR: mysqli class not loaded ' . php_ini_loaded_file());
                die('ERROR: mysqli class not loaded');
            }
            //_error_log('MySQL Connect IP=' . getRealIpAddr() . ' UA=' . $_SERVER['HTTP_USER_AGENT'] . ' ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
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
            _error_log('Error on connect, trying again [' . mysqli_connect_error() . '] IP=' . getRealIpAddr());
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

/**
 * Determines if it's safe to close the MySQL connection.
 *
 * @return bool True if the connection can be closed, false otherwise.
 */
function canCloseConnection()
{
    // Do not close connection on the main frontend rendering page
    if (!empty($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] === '/view/modeYoutube.php') {
        return false;
    }

    global $mysql_connect_is_persistent;

    // Don't close if connection is persistent
    if ($mysql_connect_is_persistent) {
        return false;
    }

    // Don't close if MySQL is not open
    if (!_mysql_is_open()) {
        return false;
    }

    // Don't close if running in CLI
    if (isCommandLineInterface()) {
        return false;
    }

    // Don't close if request is from localhost
    if (getRealIpAddr() === '127.0.0.1') {
        return false;
    }

    return true;
}


function _mysql_close()
{
    global $global, $mysql_connect_was_closed;
    if (canCloseConnection()) {
        _error_log('MySQL Closed IP=' . getRealIpAddr() . ' ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
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


/**
 * Check if the MySQL connection is open and valid.
 *
 * @return bool True if the connection is open, false otherwise.
 */
function _mysql_is_open()
{
    global $global, $mysql_connect_was_closed;
    try {
        // Check that the mysqli object exists and is valid
        if (empty($global['mysqli']) || !($global['mysqli'] instanceof mysqli)) {
            //error_log("MySQL connection is not available or not an instance of mysqli.");
            return false;
        }

        // Check if we've flagged the connection as closed
        if (!empty($mysql_connect_was_closed)) {
            //error_log("MySQL connection flagged as closed.");
            return false;
        }

        // If there is a connection error, log it
        if ($global['mysqli']->connect_errno) {
            error_log("MySQL connection error: " . $global['mysqli']->connect_error);
            return false;
        }

        $result = $global['mysqli']->query("SELECT 1");
        if ($result) {
            $result->free();
            return true;
        } else {
            error_log("MySQL connection test query failed. Connection appears to be closed. Error: " . $global['mysqli']->error);
            return false;
        }
    } catch (Exception $exc) {
        error_log("Exception in _mysql_is_open: " . $exc->getMessage());
        return false;
    }
    return true;
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



/**
 * Function to dump MySQL database with optional parameters for exclusion and custom options.
 *
 * @param string $filePath The full path where the dump file will be saved.
 * @param array $extraOptions Additional MySQL options for the dump (optional).
 * @param array $status Array for tracking status (optional).
 * @param string|null $bfile Path to the lock file (optional).
 * @return string|false Returns the filename if success or false on failure.
 */
function dumpMySQLDatabase($filePath, $extraOptions = [], &$status = [], $bfile = null)
{
    global $mysqlHost, $mysqlPort, $mysqlUser, $mysqlPass, $mysqlDatabase;

    // Log the start of the process
    _error_log("Starting MySQL database dump process");

    // Initialize lock file with current step
    $status = [
        'step' => 'Starting database dump',
        'currentFile' => $filePath,
        'message' => 'Database dump is in progress'
    ];
    updateLockFile($status, $bfile);

    // Hardcoded tables to exclude from the dump
    $excludeTables = ['CachesInDB', 'audit'];  // Add more tables as needed

    // Default MySQL port
    if (empty($mysqlPort)) {
        $mysqlPort = 3306;
    }

    // Create a connection to the database to retrieve all table names
    $connection = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort);
    if ($connection->connect_error) {
        _error_log("Connection failed: " . $connection->connect_error);
        return false;
    }

    // Get all tables from the database
    _error_log("Fetching tables from database");
    $res = sqlDAL::readSql("SHOW TABLES");
    if (!$res) {
        _error_log("Failed to retrieve tables from database");
        return false;
    }

    $row = sqlDAL::fetchAllAssoc($res);
    sqlDAL::close($res);

    if (empty($row)) {
        _error_log("No tables found in the database");
        return false;
    }

    $tables = [];
    foreach ($row as $value) {
        $tableName = reset($value);
        if (!in_array($tableName, $excludeTables)) {
            $tables[] = $tableName;
        } else {
            _error_log("Excluding table from dump: $tableName");
        }
    }

    if (empty($tables)) {
        _error_log("No tables selected for the dump");
        return false;
    }

    // Convert the tables array to a string
    $tableList = implode(" ", $tables);
    _error_log("Tables to be dumped: $tableList");

    // Base mysqldump command with necessary options
    $cmd = "mysqldump --host=$mysqlHost --port=$mysqlPort --user='$mysqlUser' --password='$mysqlPass' "
        . "--default-character-set=utf8mb4 --column-statistics=0 --add-drop-table --add-locks "
        . "--extended-insert --single-transaction --quick $mysqlDatabase $tableList";

    // Append any additional options
    if (!empty($extraOptions)) {
        foreach ($extraOptions as $option) {
            if (!empty($option)) {
                $cmd .= " $option";
            }
        }
    }

    // Specify the file path to save the dump
    $cmd .= " > {$filePath}";
    _error_log("Executing dump command: $cmd");

    // Update lock file before executing the dump
    $status['step'] = 'Running mysqldump';

    updateLockFile($status, $bfile);

    // Execute the command and wait for completion
    exec($cmd, $output, $result);

    // Check if the dump was successful
    if ($result !== 0 || !file_exists($filePath) || filesize($filePath) == 0) {
        _error_log("Error occurred while taking the database dump. Command: $cmd");
        return false;
    }

    _error_log("Database dumped successfully to {$filePath}");

    // Final update for the lock file
    $status['step'] = 'Database dump complete';
    updateLockFile($status, $bfile);

    return $filePath; // Return the file path on success
}


function updateLockFile($status, $bfile)
{

    if (empty($bfile)) {
        return false;
    }

    // Check if the 'startTime' is already set, if not, set it to the current time
    if (!isset($status['startTime'])) {
        $status['startTime'] = date("Y-m-d H:i:s"); // Set the start time when the process starts
    }

    // Update the 'lastUpdateTime' with the current time each time the lock file is updated
    $status['lastUpdateTime'] = date("Y-m-d H:i:s");

    // Write the status to the lock file
    file_put_contents($bfile, json_encode($status, JSON_PRETTY_PRINT)); // Added JSON_PRETTY_PRINT for better readability
}



/**
 * Function to restore a MySQL backup from a given SQL file.
 *
 * @param string $filename The path to the SQL file to restore.
 * @return bool Returns true if successful, false if there were errors.
 */
function restoreMySQLBackup($filename)
{
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;

    echo "Restoring MySQL backup from file: {$filename}" . PHP_EOL;

    // Step 1: Create a connection to the MySQL server
    $mysqli = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, '', $mysqlPort);
    if ($mysqli->connect_error) {
        echo "Connection failed: " . $mysqli->connect_error . PHP_EOL;
        return false;
    }

    // Step 2: Drop and recreate the database
    try {
        echo "Dropping existing database if it exists..." . PHP_EOL;
        $dropSQL = "DROP DATABASE IF EXISTS {$mysqlDatabase};";
        if (!$mysqli->query($dropSQL)) {
            throw new Exception($mysqli->error);
        }

        echo "Creating database..." . PHP_EOL;
        $createSQL = "CREATE DATABASE IF NOT EXISTS {$mysqlDatabase};";
        if (!$mysqli->query($createSQL)) {
            throw new Exception($mysqli->error);
        }

        $mysqli->select_db($mysqlDatabase);

        // Step 3: Execute the SQL file to restore the backup
        return executeSQLFile($mysqli, $filename);
    } catch (Exception $e) {
        echo "Error occurred: " . $e->getMessage() . PHP_EOL;
        return false;
    } finally {
        $mysqli->close();
    }
}


/**
 * Executes an SQL file to restore the database.
 *
 * @param mysqli $mysqli MySQLi connection.
 * @param string $filename Path to the SQL file to execute.
 * @return bool Returns true if successful, false if errors occurred.
 */
function executeSQLFile($mysqli, $filename)
{
    $templine = '';
    $lockedTables = [];
    $lines = file($filename); // Read in the SQL file

    if (!$lines) {
        echo "Failed to read SQL file: {$filename}" . PHP_EOL;
        return false;
    }

    // Function to lock tables
    function lockTables($mysqli, $tables)
    {
        $lockQuery = 'LOCK TABLES ' . implode(' WRITE, ', $tables) . ' WRITE;';
        if (!$mysqli->query($lockQuery)) {
            throw new Exception('Error locking tables: ' . $mysqli->error);
        }
    }

    // Function to check if table exists
    function tableExists($mysqli, $tableName)
    {
        $result = $mysqli->query("SHOW TABLES LIKE '$tableName'");
        return $result && $result->num_rows > 0;
    }

    // Loop through each line of the SQL file
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (substr($line, 0, 2) == '--' || trim($line) == '') {
            continue;
        }

        $templine .= $line; // Append the line to the current SQL query

        // If the line ends with a semicolon, execute the query
        if (substr(trim($line), -1) == ';') {
            try {
                if (!$mysqli->query($templine)) {
                    throw new Exception($mysqli->error);
                }
            } catch (Exception $th) {
                $error = $th->getMessage();
                if (preg_match("/Table '(.*?)' was not locked with LOCK TABLES/", $error, $matches)) {
                    $tableName = $matches[1];
                    if (!in_array($tableName, $lockedTables) && tableExists($mysqli, $tableName)) {
                        $lockedTables[] = $tableName;
                        try {
                            lockTables($mysqli, $lockedTables);
                            // Retry the query after locking the tables
                            if (!$mysqli->query($templine)) {
                                throw new Exception('Error performing query after locking tables: ' . $mysqli->error);
                            }
                        } catch (Exception $lockException) {
                            echo 'ERROR: Failed to lock tables: ' . $lockException->getMessage() . PHP_EOL;
                        }
                    } else {
                        echo 'ERROR: Table was not locked and could not be locked: ' . $error . PHP_EOL;
                    }
                } else {
                    echo 'ERROR: ' . $error . PHP_EOL;
                }
            }
            $templine = ''; // Reset for the next query
        }
    }

    // Unlock all tables at the end
    try {
        $mysqli->query('UNLOCK TABLES;');
    } catch (Exception $th) {
        echo 'ERROR: Failed to unlock tables: ' . $th->getMessage() . PHP_EOL;
    }

    return true;
}
