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

$plugin = AVideoPlugin::loadPluginIfEnabled("API");
$objData = AVideoPlugin::getObjectDataIfEnabled("API");

if (empty($plugin)) {
    $obj = new ApiObject("API Plugin disabled");
} else {
    $obj = new ApiObject("API Plugin enabled", false);
}


die(json_encode($obj));
