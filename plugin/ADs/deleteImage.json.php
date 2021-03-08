<?php
global $global, $config;
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$result = new stdClass();
$result->error = true;
$result->msg = '';


if (!User::isAdmin()) {
    $result->msg = __("You can not do this");
    die(json_encode($result));
}

require_once $global['systemRootPath'] . 'plugin/API/API.php';
$obj = AVideoPlugin::getObjectDataIfEnabled("ADs");
if (empty($obj)) {
    $result->msg = __("The plugin is disabled");
    die(json_encode($result));
}

$type = $_REQUEST['type'];
if (empty($type)) {
    $result->msg = __("Type is not defined");
    die(json_encode($result));
}

$typeFound = false;
foreach (ADs::$AdsPositions as $key => $value) {
    if($type===$value[0]){
        $typeFound = true;
        break;
    }
}

if (empty($typeFound)) {
    $result->msg = __("Type NOT found");
    die(json_encode($result));
}

$fileName = preg_replace('/[^0-9a-z]/i', '', $_REQUEST['fileName']);

if (empty($fileName)) {
    $result->msg = __("Invalid filename");
    die(json_encode($result));
}


$paths = ADs::getAdsPath($type);

$files = _glob($paths['path'], "/{$fileName}/");
foreach ($files as $value) {
    unlink($value);
}
$result->type = $type;
$result->save = ADs::saveAdsHTML($type);

$result->error = false;
die(json_encode($result));