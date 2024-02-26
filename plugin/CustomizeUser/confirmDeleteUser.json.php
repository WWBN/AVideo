<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/captcha.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

if (empty($_REQUEST['captcha'])) {
    $obj->msg = 'Empty captcha';
    die(json_encode($obj));
}

$valid = Captcha::validation(@$_REQUEST['captcha']);

if (empty($valid)) {
    $obj->msg = 'Invalid captcha';
    die(json_encode($obj));
}

$obj->users_id = intval(@$_REQUEST['users_id']);
User::loginFromRequest();
if (empty($obj->users_id) || !Permissions::canAdminUsers()) {
    $obj->users_id = User::getId();
}

if (empty($obj->users_id)) {
    $obj->msg = 'Empty users_id';
    die(json_encode($obj));
}

$user = new User($obj->users_id);


$videos = Video::getAllVideosLight('', $obj->users_id);

foreach ($videos as $value) {
    
    if($value['users_id'] != $obj->users_id){
        continue;
    }
    
    $video = new Video('', '', $value['id']);
    $video->delete();
}

$obj->delete = $user->delete();

$obj->error = empty($obj->delete);

if(empty($obj->error)){
    $obj->msg = 'User Deleted';
    if($obj->users_id == User::getId()){
        //$obj->msg = 'User Deleted';
        User::logoff();
    }
}

die(json_encode($obj));
