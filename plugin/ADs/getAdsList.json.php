<?php
global $global, $config;
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$result = new stdClass();
$result->error = true;
$result->msg = '';

$obj = AVideoPlugin::getObjectDataIfEnabled("ADs");
if (empty($obj)) {
    forbiddenPage('The plugin is disabled');
}

$type = $_REQUEST['type'];
if (empty($type)) {
    forbiddenPage('Type is not defined');
}

$typeFound = false;
foreach (ADs::AdsPositions as $key => $value) {
    if ($type === $value[0]) {
        $typeFound = true;
        break;
    }
}

if (empty($typeFound)) {
    forbiddenPage('Type NOT found');
}
$result->error = false;

$is_regular_user = intval(@$_REQUEST['is_regular_user']);
$result->ads = ADs::getAds($type, $is_regular_user);

die(json_encode($result));
