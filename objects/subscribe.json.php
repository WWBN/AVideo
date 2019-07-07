<?php

require_once 'subscribe.php';
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = "";
$obj->subscribe = "";

// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
unset($_POST["redirectUri"]);
if(!empty($input) && empty($_POST)){
    foreach ($input as $key => $value) {
        $_POST[$key]=$value;
    }
}

// gettig the mobile submited value
if(empty($_POST) && !empty($_GET)){
    foreach ($_GET as $key => $value) {
        $_POST[$key]=$value;
    }
}

if(!empty($_POST['user']) && !empty($_POST['pass'])){
    $user = new User(0, $_POST['user'], $_POST['pass']);
    $user->login(false, true);
}

if (!User::isLogged()) {
    $obj->error = "Must be logged";
    die(json_encode($obj));
}

$_POST['email'] = User::getEmail_();
if (empty($_POST['user_id'])) {
    $obj->error = __("User can not be blank");
    die(json_encode($obj));
}
$subscribe = new Subscribe(0, $_POST['email'], $_POST['user_id'], User::getId());
$subscribe->toggle();
$obj->subscribe = $subscribe->getStatus();
die(json_encode($obj));
