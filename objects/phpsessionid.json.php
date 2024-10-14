<?php
global $global, $config;

require_once __DIR__.'/../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->phpsessid = session_id();

echo _json_encode($obj);