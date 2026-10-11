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
$filePath = createPrivateMySQLDumpPath();
if ($filePath === false) {
    _error_log('Cannot create a private database dump outside the web directories.');
    exit(1);
}
$extraOptions = []; // You can add custom options here if needed

// Call the function to dump the database
$result = dumpMySQLDatabase($filePath, $extraOptions);

if ($result === false) {
    @unlink($filePath);
    @rmdir(dirname($filePath));
    _error_log("Failed to create database dump.");
    exit(1);
} else {
    _error_log("Database dump created successfully: " . $result);
}

?>
