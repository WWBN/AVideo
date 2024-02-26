<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';
$obj = new stdClass();
$obj->msg = '';
$obj->error = false;
$obj->users_id = User::getId();
$obj->notifications = User_notifications::getAllForUsers_id($obj->users_id);

if(empty($obj->notifications) || !is_array($obj->notifications)){
    $obj->notifications = array();
}

echo json_encode($obj);
