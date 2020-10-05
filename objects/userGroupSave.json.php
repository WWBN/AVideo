<?php
error_reporting(0);
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/userGroups.php';
if (!User::canUpload() || empty($_POST['id'])) {
    die('{"error":"' . __("Permission denied") . '"}');
}
if (!is_array($_POST['id'])) {
    $_POST['id'] = array($_POST['id']);
}

require_once 'video.php';
$id = 0;
foreach ($_POST['id'] as $videos_id) {
    $obj = new Video("", "", $videos_id);
    if (empty($obj)) {
        die("Object not found");
    }
    if (!$obj->userCanManageVideo()) {
        $obj->msg = __("You can not Manage This Video");
        die(json_encode($obj));
    }
    if(!empty($_POST['add'])){
        UserGroups::addVideoGroups($videos_id, $_POST['users_groups_id']);
    }else{
        UserGroups::deleteVideoGroups($videos_id, $_POST['users_groups_id']);
    }
    $resp = true;
}
echo '{"status":"' . !empty($resp) . '"}';
