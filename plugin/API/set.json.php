<?php
$configFile = '../../videos/configuration.php';
if (!file_exists($configFile)) {
    [$scriptPath] = get_included_files();
    $path = pathinfo($scriptPath);
    $configFile = $path['dirname'] . "/" . $configFile;
}

require_once $configFile;
require_once $global['systemRootPath'].'plugin/API/API.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");

$plugin = AVideoPlugin::loadPluginIfEnabled("API");
$objData = AVideoPlugin::getObjectDataIfEnabled("API");

if (empty($plugin)) {
    $obj = new ApiObject("API Plugin disabled");
    die(json_encode($obj));
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

$obj = $plugin->set($parameters);

die(json_encode($obj));
