<?php
// gettig the mobile submited value
if(empty($_POST) && !empty($_GET)){
    foreach ($_GET as $key => $value) {
        $_POST[$key]=$value;
    }
}
require_once 'subscribe.php';
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = "";
$obj->subscribe = "";
if (empty($_POST['email'])) {
    $obj->error = __("Email can not be blank");
    die(json_encode($obj));
}
if (empty($_POST['user_id'])) {
    $obj->error = __("User can not be blank");
    die(json_encode($obj));
}
$subscribe = new Subscribe(0, $_POST['email'], $_POST['user_id']);
$subscribe->toggle();
$obj->subscribe = $subscribe->getStatus();
die(json_encode($obj));
