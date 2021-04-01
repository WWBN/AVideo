<?php
global $global, $config;
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$result = new stdClass();
$result->error = true;
$result->msg = '';
$result->url = '';
$result->imageURL = '';


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

if(!IsValidURL(@$_REQUEST['url'])){
    $_REQUEST['url'] = '';
}

$paths = ADs::getNewAdsPath($type);

saveCroppieImage($paths['path'], "image");
file_put_contents($paths['txt'], @$_REQUEST['url']);

$result->type = $type;
$result->url = $_REQUEST['url'];
$result->imageURL = $paths['url'];
$result->fileName = $paths['fileName'];

$result->error = false;
$result->save = ADs::saveAdsHTML($type);

// save plugin parameter

die(json_encode($result));