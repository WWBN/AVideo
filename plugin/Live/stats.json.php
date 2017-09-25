<?php
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "OFFLINE";
$obj->nclients = 0;
if(empty($_POST['name']) && !empty($_GET['name'])){
    $_POST['name'] = $_GET['name'];
}else if(empty($_POST['name'])){
    $_POST['name'] = "undefined";
}
$obj->name = $_POST['name'];
$obj->applications = array();
require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
$p = YouPHPTubePlugin::loadPlugin("Live");
$xml = $p->getStatsObject();
$xml = json_encode($xml);
$xml = json_decode($xml);
$stream = false;
$lifeStream = array();
//$obj->server = $xml->server;
if(!empty($xml->server->application) && !is_array($xml->server->application)){
    $application = $xml->server->application;
    $xml->server->application = array();
    $xml->server->application[] = $application;
}
if(!empty($xml->server->application[0]->live->stream)){
    $lifeStream = $xml->server->application[0]->live->stream;
    if(!is_array($xml->server->application[0]->live->stream)){
        $lifeStream = array();
        $lifeStream[0] = $xml->server->application[0]->live->stream;
    }
}

$obj->countLifeStream = count($lifeStream);
foreach ($lifeStream as $value){
    if(!empty($value->name)){
        $row = LiveTransmition::keyExists($value->name);
        if(empty($row) || empty($row['public'])){
            continue;
        }
        $u = new User($row['users_id']);
        $userName = $u->getNameIdentificationBd();
        $user = $u->getUser();
        $photo = $u->getPhotoURL();
        $obj->applications[] = array("key"=>$value->name, "name"=>$userName, "user"=>$user, "photo"=>$photo, "title"=>$row['title']);
        if($value->name === $_POST['name']){
            $obj->error = (!empty($value->publishing))?false:true;
            $obj->msg = (!$obj->error)?"ONLINE":"Waiting for Streamer";
            $obj->stream = $value;
            $obj->nclients = intval($value->nclients);
            break;
        }
    }
}
echo json_encode($obj);