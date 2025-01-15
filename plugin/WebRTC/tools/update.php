<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$obj = AVideoPlugin::getDataObjectIfEnabled('WebRTC');

if (empty($obj)) {
    return die('Plugin disabled');
}
$global['printLogs'] = 1;

// Enable error reporting
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', '1'); // Display errors on the browser
ini_set('display_startup_errors', '1'); // Display startup errors

WebRTC::checkAndUpdate();
WebRTC::startIfIsInactive();