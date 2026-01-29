<?php

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../Objects/StreamAuthCache.php';

// Log the request
_error_log("preauthorize.json.php - Request received from IP: " . getRealIpAddr());
_error_log("preauthorize.json.php - POST data: " . json_encode($_POST));

// Get credentials from POST or GET
$username = !empty($_POST['user']) ? $_POST['user'] : @$_GET['user'];
$password = !empty($_POST['pass']) ? $_POST['pass'] : @$_GET['password'];

// Process preauthorization using shared method
$obj = StreamAuthCache::processPreauthorization($username, $password);

echo json_encode($obj);
