<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

// gettig the mobile submited value
$inputJSON = url_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if(!empty($input) && empty($_POST)){
    foreach ($input as $key => $value) {
        $_POST[$key]=$value;
    }
}
if(!empty($_POST['user']) && !empty($_POST['pass'])){
    $user = new User(0, $_POST['user'], $_POST['pass']);
    $user->login(false, true);
}

if (!User::canComment()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'comment.php';
if(!empty($_POST['id'])){
    $obj = new Comment("", 0, $_POST['id']);
    $obj->setComment($_POST['comment']);
}else{
    $obj = new Comment($_POST['comment'], $_POST['video']);
    $obj->setComments_id_pai($_POST['comments_id']);
}
echo '{"status":"'.$obj->save().'"}';
