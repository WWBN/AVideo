<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->users_id = intval(@$_REQUEST['users_id']);

if (empty($obj->users_id)) {
    $obj->method = 'cancelSwapUser';
    $obj->new_users_id = User::cancelSwapUser();
}else{
    $obj->method = 'swapUser';
    $obj->new_users_id = !User::swapUser($obj->users_id);
}

$obj->users_id_now = User::getId();
$obj->canAdminUser = Permissions::canAdminUsers();

$obj->error = !(($obj->users_id_now == $obj->users_id) || !empty($obj->new_users_id));

$obj->session_id = session_id();
if(!$obj->error){
    $obj->msg = __('You are user').': '.User::getNameIdentification();
}

die(json_encode($obj));
