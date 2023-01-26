<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideoTags/Objects/TagsTypes.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->add = !_empty($_REQUEST['add']);
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

if ($obj->add) {
    $obj->msg = 'Tag subscribed';
    $obj->response = Tags_subscriptions::subscribe($obj->decryptedIdAndUser['tags_id'], $obj->decryptedIdAndUser['users_id']);
} else {
    $obj->msg = 'Tag unsubscribed';
    $obj->response = Tags_subscriptions::unsubscribe($obj->decryptedIdAndUser['tags_id'], $obj->decryptedIdAndUser['users_id']);
}
$obj->error = empty($obj->response);

die(json_encode($obj));
?>