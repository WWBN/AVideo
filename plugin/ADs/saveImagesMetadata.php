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

$is_admin = User::isAdmin();

if (empty($is_admin) && !ADs::canHaveCustomAds()) {
    forbiddenPage('You can not do this');
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

if (!isset($_REQUEST['url']) || !IsValidURL($_REQUEST['url'])) {
    $_REQUEST['url'] = '';
}

if(empty($_REQUEST['metadata'])){
    forbiddenPage('Metadata not found');
}

$is_regular_user = intval(@$_REQUEST['is_regular_user']);

$paths = ADs::getAdsPath($type, $is_regular_user);

if (empty($paths)) {
    forbiddenPage('Ads not find');
}

$elements = array();
foreach ($_REQUEST['metadata'] as $value) {
    $fileName = ADs::getFileName($paths['url'], $value['imgSrc']);
    $elements[$fileName] = array(
        'url'=>$value['url'], 
        'title'=>$value['title'], 
        'order'=>$value['order']
    );
}

$result->paths = $paths;
$result->saved = array();
$files = _glob($paths['path'], '/.png$/');

foreach ($files as $value) {
    $fileName = ADs::getFileName($paths['path'], $value);
    if (empty($fileName)) {
        continue;
    }
    if (empty($elements[$fileName])) {
        continue;
    }
    $txtPath = "{$paths['path']}{$fileName}.txt";

    $result->saved[] = array($txtPath , ADs::setTXT($txtPath, $elements[$fileName]['url'], $elements[$fileName]['title'], $elements[$fileName]['order']));
}

$result->error = empty($result->saved);

die(json_encode($result));
