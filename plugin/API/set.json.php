<?php
$configFile = __DIR__.'/../../videos/configuration.php';
if (!file_exists($configFile)) {
    [$scriptPath] = get_included_files();
    $path = pathinfo($scriptPath);
    $configFile = $path['dirname'] . "/" . $configFile;
}
$global['bypassSameDomainCheck'] = 1;

require_once $configFile;
require_once $global['systemRootPath'].'plugin/API/API.php';
allowOrigin(true);
header("Access-Control-Allow-Headers: Content-Type, ua-resolution");

$plugin = AVideoPlugin::loadPluginIfEnabled("API");
$objData = AVideoPlugin::getObjectDataIfEnabled("API");

if (empty($plugin)) {
    $obj = new ApiObject("API Plugin disabled");
    die(_json_encode($obj));
}

// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = _json_decode($inputJSON, true); //convert JSON into array
if (empty($input)) {
    $input = [];
} else {
    $input = object_to_array($input);
}
$parameters = array_merge($_GET, $_POST, $input);

// CSRF guard (2026-08-17): this file sets bypassSameDomainCheck above, and the
// /plugin/api/ path is exempt from the generic autoCSRFGuard, because callers who
// authenticate explicitly (APISecret, or user+pass) can't be forced to do so
// cross-site. But when neither is supplied, set() falls back to whatever session the
// request already carries, and User::login() is a no-op if that session is already
// logged in — so a forged user/pass does not prove the caller isn't just riding the
// victim's ambient cookie. Only trust an already-authenticated ambient session; require
// it to arrive as a same-origin POST so a cross-site GET/POST navigation can't reuse it.
$ambientlyLoggedIn = User::isLogged();
$hasExplicitCredentials = API::isAPISecretValid() || (!$ambientlyLoggedIn && !empty($parameters['user']) && (!empty($parameters['pass']) || !empty($parameters['password'])));
if (!$hasExplicitCredentials) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => true, 'msg' => 'Method not allowed']));
    }
    if (!requestComesFromSameDomainAsMyAVideo() && !isAVideoUserAgent()) {
        http_response_code(403);
        die(json_encode(['error' => true, 'msg' => 'Invalid Request ' . getRealIpAddr()]));
    }
}

$obj = $plugin->set($parameters);

if(is_object($obj)){
    $obj = _json_encode($obj);
}
header('Content-Type: application/json');
if (!empty($_REQUEST['gzip'])) {
    $obj = gzencode($obj, 9);
    header('Content-Encoding: gzip');
}

header('Content-Length: ' . strlen($obj));
die($obj);
