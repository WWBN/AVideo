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
parse_str($parts["query"], $_GET);
_error_log("NGINX ON Publish parse_url: ".json_encode($parts));
_error_log("NGINX ON Publish parse_str: ".json_encode($_GET));


if(empty($_POST['name']) && !empty($_GET['name'])){
    $_POST['name'] = $_GET['name'];
}
if(empty($_POST['name']) && !empty($_GET['key'])){
    $_POST['name'] = $_GET['key'];
}


if (!empty($_GET['p'])) {
    $_GET['p'] = str_replace("/", "", $_GET['p']);
    _error_log("NGINX ON Publish check if key exists");
    $obj->row = LiveTransmition::keyExists($_POST['name']);
    _error_log("NGINX ON Publish key exists return ". json_encode($obj->row));
    if (!empty($obj->row)) {
        _error_log("NGINX ON Publish new User({$obj->row['users_id']})");
        $user = new User($obj->row['users_id']);
        if(!$user->thisUserCanStream()){
            _error_log("NGINX ON Publish User [{$obj->row['users_id']}] can not stream");
        }else if ($_GET['p'] === $user->getPassword()) {
            _error_log("NGINX ON Publish get LiveTransmitionHistory");
            $lth = new LiveTransmitionHistory();
            $lth->setTitle($obj->row['title']);
            $lth->setDescription($obj->row['description']);
            $lth->setKey($_POST['name']);
            $lth->setUsers_id($user->getBdId());
            _error_log("NGINX ON Publish saving LiveTransmitionHistory");
            $lth->save();
            _error_log("NGINX ON Publish saved LiveTransmitionHistory");
            $obj->error = false;
            
        } else {
            _error_log("NGINX ON Publish error, Password does not match");
        }
    } else {
        _error_log("NGINX ON Publish error, Transmition name not found ({$_POST['name']}) ", AVideoLog::$SECURITY);
    }
} else {
    _error_log("NGINX ON Publish error, Password not found ", AVideoLog::$SECURITY);
}
_error_log("NGINX ON Publish deciding ...");
if (!empty($obj) && empty($obj->error)) {
    _error_log("NGINX ON Publish success");
    http_response_code(200);
    header("HTTP/1.1 200 OK");
    echo "success";
    exit;
} else {
    _error_log("NGINX ON Publish denied ", AVideoLog::$SECURITY);
    http_response_code(401);
    header("HTTP/1.1 401 Unauthorized Error");
    exit;
}
//_error_log(print_r($_POST, true));
//_error_log(print_r($obj, true));
//echo json_encode($obj);