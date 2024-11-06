<?php
require_once __DIR__.'/../videos/configuration.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (php_sapi_name() !== 'cli') {
    die('Command Line only');
}
$global['printLogs'] = 1;
// Example usage
$filePath = Video::getStoragePath() . 'mysqldump-' . date('YmdHis') . '.sql';
$extraOptions = []; // You can add custom options here if needed

// Call the function to dump the database
$result = dumpMySQLDatabase($filePath, $extraOptions);

if ($result === false) {
    _error_log("Failed to create database dump.");
} else {
    _error_log("Database dump created successfully: " . $result);
}

?>
