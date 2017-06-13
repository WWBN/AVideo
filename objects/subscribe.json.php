<?php

require_once 'subscribe.php';
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = "";
$obj->subscribe = "";
if(empty($_POST['email'])){
    $obj->error = __("Email can not be blank");
    die(json_encode($obj));
}
$subscribe = new Subscribe(0, $_POST['email']);
$subscribe->toggle();
$obj->subscribe = $subscribe->getStatus();
die(json_encode($obj));