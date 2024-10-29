<?php
global $global, $config;
$doNotConnectDatabaseIncludeConfig = 1;
require_once __DIR__.'/../videos/configuration.php';
allowOrigin();
header('Content-Type: application/json');

$obj = new stdClass();
$obj->phpsessid = session_id();

echo _json_encode($obj);