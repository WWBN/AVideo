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
foreach (ADs::AdsPositions as $key => $value) {
    if ($type === $value[0]) {
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

$result->type = $type;
$result->saved = false;
$result->edited = false;
if (empty($_REQUEST['filename'])) {
    $paths = ADs::getNewAdsPath($type, $is_regular_user);
    $result->pathSaved = $paths['path'];
    $result->saved = saveCroppieImage($paths['path'], "image");
    $result->error = false;
} else {
    $paths = ADs::getAdsPath($type, $is_regular_user);
    if (empty($paths)) {
        forbiddenPage('Ads not find');
    }

    $files = _glob($paths['path'], '/.png$/');

    foreach ($files as $value) {
        $fileName = ADs::getFileName($paths['path'], $value);
        if (empty($fileName)) {
            continue;
        }
        if ($fileName == $_REQUEST['filename']) {
            $result->pathSaved = $value;
            $result->saved = saveCroppieImage($value, "image");
            $result->edited = true;
            $result->error = false;
            break;
        }
    }
}

if (empty($is_regular_user)) {
    $result->save = ADs::saveAdsHTML($type);
}
// save plugin parameter

die(json_encode($result));
