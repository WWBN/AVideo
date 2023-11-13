<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (empty($_POST['id'])) {
    forbiddenPage("Id is empty");
}
require_once 'video.php';
if (!is_array($_POST['id'])) {
    $_POST['id'] = [$_POST['id']];
}
$id = 0;
$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->users_id = User::getId();
foreach ($_POST['id'] as $value) {
    $video = new Video("", "", $value);
    if(empty($video->getUsers_id()) || $video->getUsers_id() != User::getId()){
        if (!$video->userCanManageVideo()) {
            $obj->msg = __('You can not Manage This Video');
            die(_json_encode($obj));
        }
    }
    $id = $video->delete();
    $obj->error = empty($id);
}

die(_json_encode($obj));
