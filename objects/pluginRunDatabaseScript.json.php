<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('{"error":"' . __("POST method required") . '"}');
}
if (!isGlobalTokenValid()) {
    http_response_code(403);
    die('{"error":"' . __("Invalid token") . '"}');
}

$pluginName = trim(preg_replace('/[^0-9a-z_]/i', '', $_POST['name']));

if (empty($pluginName)) {
    die('{"error":"' . __("Name can't be blank") . '"}');
}
ini_set('max_execution_time', 300);
$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->name = $pluginName;
$templine = '';
$fileName = Plugin::getDatabaseFileName($pluginName);
$obj->fileName = $fileName;
if ($fileName) {
    $lines = file($fileName);
    foreach ($lines as $line) {
        if (substr($line, 0, 2) == '--' || $line == '') {
            continue;
        }
        $templine .= $line;
        if (substr(trim($line), -1, 1) == ';') {
            if (!$global['mysqli']->query($templine)) {
                _error_log('pluginRunDatabaseScript query failed for plugin ' . $pluginName . ': ' . $global['mysqli']->error);
                $obj->msg = __('Error performing query');
                die(json_encode($obj));
            }
            $templine = '';
        }
    }
    $obj->error = false;
    $obj->msg = "All queries executed";
} else {
    $obj->msg = "File not found";
}

die(json_encode($obj));
