<?php

//streamer config
$global['createDatabase'] = 1;
$doNotIncludeConfig = 1;
require_once __DIR__ . '/../videos/configuration.php';

if (php_sapi_name() !== 'cli') {
    return die('Command Line only');
}

ob_end_flush();

$globPattern = "{$global['systemRootPath']}videos/mysqldump-*.sql";
echo "Searching [{$globPattern}]" . PHP_EOL;
$glob = glob($globPattern);
foreach ($glob as $key => $file) {
    echo "($key) {$file} " . humanFileSize(filesize($file)) . PHP_EOL;
}

// Check for command line argument
if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == '-1') {
    $filename = end($glob);
} else {
    echo "Type the number of what file you want to restore or just press enter to get the latest" . PHP_EOL;
    $option = trim(readline(""));

    if ($option === '') {
        $filename = end($glob);
    } else {
        $option = intval($option);
        $filename = $glob[$option];
    }
}
/*
echo 'We will make a backup first ...' . PHP_EOL;
$restore = 1;

//include './mysqlDump.php';

echo PHP_EOL . "Backup file created at {$file}" . PHP_EOL;
*/

$global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, '', @$mysqlPort);
try {
    $createSQL = "DROP DATABASE IF EXISTS {$mysqlDatabase};";
    $global['mysqli']->query($createSQL);
} catch (\Throwable $th) {
    echo ($th->getMessage());
}
$createSQL = "CREATE DATABASE IF NOT EXISTS {$mysqlDatabase};";
echo $createSQL . PHP_EOL;
$global['mysqli']->query($createSQL);
$global['mysqli']->select_db($mysqlDatabase);

echo "Execute filename {$filename}" . PHP_EOL;
executeFile($filename);

function executeFile($filename) {
    global $global;
    $templine = '';
    // Read in entire file
    $lines = file($filename);
    $lockedTables = [];

    // Função para bloquear tabelas
    function lockTables($tables) {
        global $global;
        $lockQuery = 'LOCK TABLES ' . implode(' WRITE, ', $tables) . ' WRITE;';
        if (!$global['mysqli']->query($lockQuery)) {
            throw new Exception('Error locking tables: ' . $global['mysqli']->error);
        }
    }

    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || trim($line) == '')
            continue;

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1) == ';') {
            // Perform the query
            try {
                if (!$global['mysqli']->query($templine)) {
                    throw new Exception($global['mysqli']->error);
                }
            } catch (\Exception $th) {
                $error = $th->getMessage();
                if (preg_match("/Table '(.*?)' was not locked with LOCK TABLES/", $error, $matches)) {
                    $tableName = $matches[1];
                    if (!in_array($tableName, $lockedTables)) {
                        $lockedTables[] = $tableName;
                        try {
                            lockTables($lockedTables);
                            // Retry the query after locking the tables
                            if (!$global['mysqli']->query($templine)) {
                                throw new Exception('Error performing query after locking tables: ' . $global['mysqli']->error);
                            }
                        } catch (\Exception $lockException) {
                            echo 'ERROR: Failed to lock tables: ' . $lockException->getMessage() . PHP_EOL;
                        }
                    } else {
                        echo 'ERROR: Table was not locked and could not be locked: ' . $error . PHP_EOL;
                    }
                } else {
                    echo 'ERROR: ' . $error . PHP_EOL;
                }
            }
            // Reset temp variable to empty
            $templine = '';
        }
    }

    // Unlock all tables at the end
    try {
        $global['mysqli']->query('UNLOCK TABLES;');
    } catch (\Exception $th) {
        echo 'ERROR: Failed to unlock tables: ' . $th->getMessage() . PHP_EOL;
    }
}
