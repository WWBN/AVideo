<?php

header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../Objects/StreamAuthCache.php';

// Log the request
_error_log("preauthorize.json.php - Request received from IP: " . getRealIpAddr());
_error_log("preauthorize.json.php - POST data: " . json_encode($_POST));

// SECURITY REVIEW (2026-08-14): reported that accepting credentials via $_GET here
// bypasses the POST-only CSRF guard and leaks them into logs/Referer — real, but NOT
// fixed because this endpoint is called by external/unenumerable RTMP client
// integrations (no in-repo caller found) and removing the GET fallback could silently
// break those; the rate-limit gap in the same report was fixed in
// StreamAuthCache::processPreauthorization(). Needs the maintainer to confirm no
// external client still depends on GET before this can be removed.
// Get credentials from POST or GET
$username = !empty($_POST['user']) ? $_POST['user'] : @$_GET['user'];
$password = !empty($_POST['pass']) ? $_POST['pass'] : @$_GET['password'];

// Process preauthorization using shared method
$obj = StreamAuthCache::processPreauthorization($username, $password);

echo json_encode($obj);
