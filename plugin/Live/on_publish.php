<?php

require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once './Objects/LiveTransmitionHistory.php';
$obj = new stdClass();
$obj->error = true;

_error_log("NGINX ON Publish POST: ".json_encode($_POST));
_error_log("NGINX ON Publish GET: ".json_encode($_GET));

// get GET parameters
$url = $_POST['tcurl'];
if (empty($url)) {
    $url = $_POST['swfurl'];
}
$parts = parse_url($url);
_error_log(print_r($parts, true));
parse_str($parts["query"], $_GET);
_error_log(print_r($_GET, true));


if(empty($_POST['name']) && !empty($_GET['name'])){
    $_POST['name'] = $_GET['name'];
}
if(empty($_POST['name']) && !empty($_GET['key'])){
    $_POST['name'] = $_GET['key'];
}


if (!empty($_GET['p'])) {
    $_GET['p'] = str_replace("/", "", $_GET['p']);
    $obj->row = LiveTransmition::keyExists($_POST['name']);
    if (!empty($obj->row)) {
        $user = new User($obj->row['users_id']);
        if(!$user->thisUserCanStream()){
            _error_log("User [{$obj->row['users_id']}] can not stream");
        }else if ($_GET['p'] === $user->getPassword()) {
            $lth = new LiveTransmitionHistory();
            $lth->setTitle($obj->row['title']);
            $lth->setDescription($obj->row['description']);
            $lth->setKey($_POST['name']);
            $lth->setUsers_id($user->getBdId());
            $lth->save();
            $obj->error = false;
            
        } else {
            _error_log("Stream Publish error, Password does not match");
        }
    } else {
        _error_log("Stream Publish error, Transmition name not found ({$_POST['name']})");
    }
} else {
    _error_log("Stream Publish error, Password not found");
}

if (!empty($obj) && empty($obj->error)) {
    http_response_code(200);
} else {
    http_response_code(401);
    _error_log("Publish denied");
    _error_log(print_r($_GET, true));
    _error_log(print_r($_POST, true));
    _error_log(print_r($obj, true));
}
//_error_log(print_r($_POST, true));
//_error_log(print_r($obj, true));
//echo json_encode($obj);