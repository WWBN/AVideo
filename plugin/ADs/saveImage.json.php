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

require_once $global['systemRootPath'] . 'plugin/API/API.php';
$obj = AVideoPlugin::getObjectDataIfEnabled("ADs");
if (empty($obj)) {
    $result->msg = __("The plugin is disabled");
    die(json_encode($result));
}

$is_admin = User::isAdmin();

if (empty($is_admin) && !ADs::canHaveCustomAds()) {
    gotToLoginAndComeBackHere(__("You can not do this"));
    exit;
}

$type = $_REQUEST['type'];
if (empty($type)) {
    $result->msg = __("Type is not defined");
    die(json_encode($result));
}

$typeFound = false;
foreach (ADs::$AdsPositions as $key => $value) {
    if ($type===$value[0]) {
        $typeFound = true;
        break;
    }
}

if (empty($typeFound)) {
    $result->msg = __("Type NOT found");
    die(json_encode($result));
}

if (!isset($_REQUEST['url']) || !IsValidURL($_REQUEST['url'])) {
    $_REQUEST['url'] = '';
}
$is_regular_user = intval(@$_REQUEST['is_regular_user']);
$paths = ADs::getNewAdsPath($type, $is_regular_user);
//var_dump($_REQUEST['is_regular_user'], $paths['txt']);exit;
saveCroppieImage($paths['path'], "image");
file_put_contents($paths['txt'], $_REQUEST['url']);

$result->type = $type;
$result->url = $_REQUEST['url'];
$result->imageURL = $paths['url'];
$result->fileName = $paths['fileName'];

$result->error = false;
if(empty($is_regular_user)){
    $result->save = ADs::saveAdsHTML($type);
}
// save plugin parameter

die(json_encode($result));
