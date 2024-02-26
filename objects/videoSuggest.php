<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->msg = '';
$obj->error = true;
$obj->idsToSave = [];
$obj->idSaved = [];

require_once $global['systemRootPath'] . 'objects/user.php';
if (!Permissions::canModerateVideos()) {
    forbiddenPage('Permission denied');
}
require_once $global['systemRootPath'] . 'objects/video.php';
if (!is_array($_POST['id'])) {
    $obj->idsToSave = [$_POST['id']];
}else{
    $obj->idsToSave = $_POST['id'];
}
foreach ($obj->idsToSave as $value) {
    $video = new Video('', '', $value);
    $video->setIsSuggested($_POST['isSuggested']);
    $obj->idSaved[] = $video->save();
}

if(!empty($obj->idSaved)){
    $obj->error = false;
    clearCache(true);
}

echo _json_encode($obj);
