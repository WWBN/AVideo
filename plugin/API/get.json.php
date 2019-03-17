<?php
$configFile = '../../videos/configuration.php';
if (!file_exists($configFile)) {
    list($scriptPath) = get_included_files();
    $path = pathinfo($scriptPath);
    $configFile = $path['dirname'] . "/" . $configFile;
}
require_once $configFile;
require_once $global['systemRootPath'].'plugin/API/API.php';
header('Content-Type: application/json');

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("API");
$objData = YouPHPTubePlugin::getObjectDataIfEnabled("API");

if(empty($plugin)){
    $obj = new ApiObject("API Plugin disabled");
    die(json_encode($obj));
}

$parameters = array_merge($_GET, $_POST);

$obj = $plugin->get($parameters);

die(json_encode($obj));