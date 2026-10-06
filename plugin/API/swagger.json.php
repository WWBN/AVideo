<?php
//$doNotConnectDatabaseIncludeConfig = 1;
$doNotStartSessionIncludeConfig = 1;

$configFile = __DIR__ . '/../../videos/configuration.php';
require_once $configFile;
require_once __DIR__ . '/API.php';

use OpenApi\Generator;

if (!API::canAccessDocumentation()) {
    forbiddenPage('API documentation access denied. Your IP: ' . getRealIpAddr() . '. Ask the administrator to add this IP to the API documentation allowed IPs.', true);
}
header('Cache-Control: private, no-store');
$plugins = Plugin::getAllEnabled();
$sources = array();

// Sort plugins alphabetically by dirName
usort($plugins, function ($a, $b) {
    return strcmp($a['dirName'], $b['dirName']);
});

foreach ($plugins as $value) {
    $p = AVideoPlugin::loadPlugin($value['dirName']);
    if (class_exists($value['dirName'])) {
        $sources[] = "{$global['systemRootPath']}plugin/{$value['dirName']}/{$value['dirName']}.php";
    }
}

$openapi = Generator::scan($sources);

header('Content-Type: application/json');
echo $openapi->toJson();
