<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
@header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->key = $_REQUEST['key'];
$obj->msg = $_REQUEST['msg'];

if (empty($obj->key)) {
    $obj->msg = 'empty key';
    die(json_encode($obj));
}

$row = LiveTransmition::keyExists($obj->key);

if (empty($row)) {
    $obj->msg = 'empty row';
    die(json_encode($obj));
}
$users_id = intval($row['users_id']);

if (empty($users_id)) {
    $obj->msg = 'Invalid user';
    die(json_encode($obj));
}

if (!User::isAdmin($users_id)) {
    $obj->msg = 'Only notify admin';
    die(json_encode($obj));
}

$obj->error = false;

if (empty($_REQUEST['error'])) {
    $obj->socketResponse = sendSocketSuccessMessageToUsers_id($obj->msg, $users_id);
} else {
    $obj->socketResponse = sendSocketErrorMessageToUsers_id($obj->msg, $users_id);
}

die(json_encode($obj));
