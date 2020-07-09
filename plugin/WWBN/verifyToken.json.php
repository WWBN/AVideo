<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->token = null;

if(empty($_REQUEST['token'])){
    $obj->msg = "Token is empty";
    die(json_encode($obj));
}

$obj->token = decryptString($_REQUEST['token']);
if(empty($obj->token)){
    $obj->msg = "Token is invalid";
    die(json_encode($obj));
}

$obj->error = false;
die(json_encode($obj));