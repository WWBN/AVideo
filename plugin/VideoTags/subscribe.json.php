<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsTypes.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->tags_id = 0;
$obj->response = false;

if (empty($_REQUEST['encryptedIdAndUser'])) {
    $obj->msg = 'Encrypted info not found';
    die(json_encode($obj));
}

$obj->decryptedIdAndUser = decryptString($_REQUEST['encryptedIdAndUser']);

if (empty($obj->decryptedIdAndUser)) {
    $obj->msg = 'Decryption error';
    die(json_encode($obj));
}

$obj->decryptedIdAndUser = object_to_array(json_decode($obj->decryptedIdAndUser));

if (empty($obj->decryptedIdAndUser['tags_id'])) {
    $obj->msg = 'tags_id error';
    die(json_encode($obj));
}

if (empty($obj->decryptedIdAndUser['users_id']) || $obj->decryptedIdAndUser['users_id'] !== User::getId()) {
    $obj->msg = 'users_id error';
    die(json_encode($obj));
}

$obj->tags_id = $obj->decryptedIdAndUser['tags_id'];
$obj->users_id = $obj->decryptedIdAndUser['users_id'];
$obj->notify = intval(@$_REQUEST['notify']);

if(!isset($_REQUEST['add'])){ //toggle
    if($obj->notify>=0){
        $obj->add = true;
    }else{
        $obj->isSubscribed = VideoTags::isUserSubscribed($obj->users_id, $obj->tags_id);
        $obj->add = empty($obj->isSubscribed);
    }
}else{
    $obj->add = !_empty($_REQUEST['add']);
}

if ($obj->add) {
    $obj->msg = 'Tag subscribed';
    $obj->response = Tags_subscriptions::subscribe($obj->decryptedIdAndUser['tags_id'], $obj->decryptedIdAndUser['users_id'], intval(@$_REQUEST['notify']));
} else {
    $obj->msg = 'Tag unsubscribed';
    $obj->response = Tags_subscriptions::unsubscribe($obj->decryptedIdAndUser['tags_id'], $obj->decryptedIdAndUser['users_id']);
}
clearFirstPageCache();
//ObjectYPT::clearSessionCache();
//ObjectYPT::deleteAllSessionCache();
$obj->error = empty($obj->response);

die(json_encode($obj));
?>