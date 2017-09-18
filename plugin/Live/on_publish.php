<?php

require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
$obj = new stdClass();
$obj->error = true;

// get GET parameters
$url = $_POST['tcurl'];
if(empty($url)){    
$url = $_POST['swfurl'];
}
$parts = parse_url($url);
    error_log(print_r($parts, true));
parse_str($parts["query"], $_GET);
    error_log(print_r($_GET, true));
if (!empty($_GET['p'])) {
    $obj->row = LiveTransmition::keyExists($_POST['name']);
    if (!empty($obj->row)) {
        $user = new User($obj->row['users_id']);
        if ($_GET['p'] === $user->getPassword()) {
            $obj->error = false;
        } else {
            error_log("Stream Publish error, Password does not match");
        }
    } else {
        error_log("Stream Publish error, Transmition name not found ({$_POST['name']})");
    }
} else {
    error_log("Stream Publish error, Password not found");
}

if (!empty($obj) && empty($obj->error)) {
    http_response_code(300);
    header("Location: {$_POST['name']}");
} else {
    http_response_code(401);
    error_log("Publish denied");
    error_log(print_r($_GET, true));
    error_log(print_r($_POST, true));
    error_log(print_r($obj, true));
}
//error_log(print_r($_POST, true));
//error_log(print_r($obj, true));
//echo json_encode($obj);