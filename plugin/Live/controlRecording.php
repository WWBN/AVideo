<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

// Log to a specific file for CLI debugging
$logFile = $global['systemRootPath'] . 'videos/controlRecording_debug.log';
function logToFile($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$msg}\n", FILE_APPEND);
}

logToFile("=== controlRecording.php START ===");
logToFile("isCommandLineInterface: " . (isCommandLineInterface() ? 'true' : 'false'));

if (!isCommandLineInterface()) {
    logToFile("ERROR: Not command line interface, exiting");
    return die('Command Line only');
}
$global['printLogs'] = 1;
AVideoPlugin::loadPlugin('Live');

$key = $argv[1];
$live_servers_id = intval(@$argv[2]);
$start = intval(@$argv[3]);

logToFile("Arguments: key={$key}, live_servers_id={$live_servers_id}, start={$start}");
logToFile("Raw argv: " . json_encode($argv));

_error_log("controlRecording.php: Arguments received - key={$key}, live_servers_id={$live_servers_id}, start={$start}");
_error_log("controlRecording.php: Raw argv=" . json_encode($argv));

logToFile("Calling Live::controlRecording...");
$result = Live::controlRecording($key, $live_servers_id, $start);
logToFile("Result: " . json_encode($result));
logToFile("=== controlRecording.php END ===");

_error_log("controlRecording.php: Result=" . json_encode($result));
