<?php
header('Content-Type: application/json');
$obj = new stdClass();
if(empty($_GET['key'])){
    die(json_encode($obj));
}
$obj->key = $_GET['key'];
require_once '../../../videos/configuration.php';
require_once '../Objects/LiveOnlineUsers.php';
require_once '../../../objects/user.php';


$liveUsers = new LiveOnlineUsers(0);
$liveUsers->loadFromTransmitionKey($obj->key);
$obj->beat = $liveUsers->save();
$obj->users = $liveUsers->getUsersFromTransmitionKey($obj->key);
//$p = YouPHPTubePlugin::loadPlugin("LiveUsers");

echo json_encode($obj);