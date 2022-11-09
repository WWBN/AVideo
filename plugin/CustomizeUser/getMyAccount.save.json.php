<?php
require_once '../../videos/configuration.php';
session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
if(!User::canUpload()){
    $obj->msg = "Cannot Upload";   
    die(json_encode($obj));
}

$userWebsite = preg_replace('/[^a-z0-9_\/@.:?&=;%-]/i', '', @$_POST['userWebsite']);

if(!empty($userWebsite) && !isValidURL($userWebsite)){
    $obj->msg = "User Site is invalid {$_POST['userWebsite']} = {$userWebsite}";   
    die(json_encode($obj));
}

$cobj = AVideoPlugin::getObjectData("CustomizeUser");

$user = new User(User::getId());
$obj->added = $user->addExternalOptions('userWebsite', $userWebsite);

$obj->error = empty($obj->added);

die(json_encode($obj));