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
    if (empty($fileName)) {
        _error_log('ADs saveImagesMetadata: unable to resolve file name from image URL: ' . json_encode($value));
        continue;
    }
    $elements[$fileName] = array(
        'url'=>$value['url'],
        'title'=>$value['title'],
        'order'=>$value['order']
    );
}

$result->paths = $paths;
$result->saved = array();
$files = _glob($paths['path'], '/.png$/');
if (empty($files)) {
    _error_log('ADs saveImagesMetadata: no PNG files found in path ' . $paths['path']);
}

foreach ($files as $value) {
    $fileName = ADs::getFileName($paths['path'], $value);
    if (empty($fileName)) {
        continue;
    }
    if (empty($elements[$fileName])) {
        continue;
    }
    $txtPath = "{$paths['path']}{$fileName}.txt";
    $saved = ADs::setTXT($txtPath, $elements[$fileName]['url'], $elements[$fileName]['title'], $elements[$fileName]['order']);
    if ($saved === false) {
        _error_log('ADs saveImagesMetadata: failed to save metadata file ' . $txtPath . ' for image ' . $fileName);
        continue;
    }
    $result->saved[] = array($txtPath, $saved);
}

$result->error = empty($result->saved);
if ($result->error) {
    $result->msg = 'No metadata was saved. Check the image URL format and filesystem permissions.';
    _error_log('ADs saveImagesMetadata: no metadata saved. paths=' . json_encode($paths) . ' metadata=' . json_encode($_REQUEST['metadata']));
}

die(json_encode($result));
