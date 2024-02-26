<?php

//streamer config
$global['createDatabase'] = 1;
$doNotIncludeConfig = 1;
require_once '../videos/configuration.php';

$global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, '', @$mysqlPort);
$createSQL = "CREATE DATABASE IF NOT EXISTS {$mysqlDatabase};";
$global['mysqli']->query($createSQL);
$global['mysqli']->select_db($mysqlDatabase);

if (php_sapi_name() !== 'cli') {
    return die('Command Line only');
}

ob_end_flush();

$globPattern = "{$global['systemRootPath']}videos/mysqldump-*.sql";
echo "Searching [{$globPattern}]" . PHP_EOL;
$glob = glob($globPattern);
foreach ($glob as $key => $file) {
    echo "($key) {$file}" . PHP_EOL;
}

echo "Type the number of what file you want to restore or just press enter to get the latest" . PHP_EOL;
$option = trim(readline(""));

if ($option === '') {
    $filename = end($glob);
} else {
    $option = intval($option);
    $filename = $glob[$option];
}
/*
echo 'We will make a backup first ...' . PHP_EOL;
$restore = 1;

//include './mysqlDump.php';

echo PHP_EOL . "Backup file created at {$file}" . PHP_EOL;
*/
executeFile($filename);

function executeFile($filename) {
    global $global;
    $templine = '';
    // Read in entire file
    $lines = file($filename);
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            if (!$global['mysqli']->query($templine)) {
                echo ('sqlDAL::executeFile ' . $filename . ' Error performing query \'<strong>' . $templine . '\': ' . $global['mysqli']->error . '<br /><br />');
            }
            // Reset temp variable to empty
            $templine = '';
        }
    }
}
