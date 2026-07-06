<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';

enforceRateLimit('user_notifications_get', 6, 60);

$obj = new stdClass();
$obj->msg = '';
$obj->error = false;
$obj->users_id = User::getId();

if (!User::isLogged() || empty($obj->users_id)) {
    $obj->notifications = array();
    echo json_encode($obj);
    exit;
}

$cacheName = 'UserNotifications/getNotifications/' . $obj->users_id;
$cached = ObjectYPT::getSessionCache($cacheName, 10);
if (is_array($cached)) {
    $obj->notifications = $cached;
    echo json_encode($obj);
    exit;
}

$obj->notifications = User_notifications::getAllForUsers_id($obj->users_id);

if(empty($obj->notifications) || !is_array($obj->notifications)){
    $obj->notifications = array();
}

ObjectYPT::setSessionCache($cacheName, $obj->notifications);

echo json_encode($obj);
