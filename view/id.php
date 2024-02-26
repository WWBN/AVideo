<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = false;
$obj->id = getPlatformId();

echo json_encode($obj);