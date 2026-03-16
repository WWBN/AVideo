<?php
global $global, $config;
$doNotConnectDatabaseIncludeConfig = 1;
require_once __DIR__.'/../videos/configuration.php';
// No allowOrigin() call: this endpoint is consumed by same-origin JavaScript
// only (see view/js/session.js). Omitting CORS headers means the browser's
// same-origin policy already blocks cross-origin reads, preventing any
// third-party site from fetching the session ID via a credentialed request.
header('Content-Type: application/json');

$obj = new stdClass();
$obj->phpsessid = session_id();

echo _json_encode($obj);
