<?php
error_reporting(0);
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload() || empty($_POST['id'])) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if (!is_array($_POST['id'])) {
    $_POST['id'] = array($_POST['id']);
}

require_once 'video.php';

$id = 0;

$obj = new stdClass();
$obj->error = true;
$obj->status = array();
$obj->msg = '';

foreach ($_POST['id'] as $value) {
    $obj2 = new stdClass();
    $obj2->error = true;
    $obj2->videos_id = $value;
    $obj2->status = $_POST['status'];
    $obj2->msg = '';
    
    
    $v = new Video("", "", $value);
    if (empty($v)) {
        $obj2->msg = __("Video NOT Found");
        $obj->status[] = $obj2;
        continue;
    }
    if (!$v->userCanManageVideo() && !Permissions::canModerateVideos()) {
        $obj2->msg = __("You can not Manage This Video");
        $obj->status[] = $obj2;
        continue;
    }
    $v->setStatus($_POST['status']);
    $obj2->error = false;
    $obj->status[] = $obj2;
}

foreach ($obj->status as $value) {
    if($value->error){
        break;
    }
    $obj->error = false;
}

die(json_encode($obj));
