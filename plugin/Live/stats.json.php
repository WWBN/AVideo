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

var_dump($xml);
if(!empty($xml->server->application[1]->live->stream)){
    $lifeStream = $xml->server->application[1]->live->stream;
    if(!is_array($xml->server->application[1]->live->stream)){
        $lifeStream = array();
        $lifeStream[0] = $xml->server->application[1]->live->stream;
    }
}
foreach ($lifeStream as $value){
    if(!empty($value->name)){
        $row = LiveTransmition::keyExists($value->name);
        if(empty($row)){
            continue;
        }
        $u = new User($row['users_id']);
        $userName = $u->getNameIdentificationBd();
        $user = $u->getUser();
        $obj->applications[] = array("key"=>$value->name, "name"=>$userName, "user"=>$user, "title"=>$row['title']);
        if($value->name === $_POST['name']){
            $obj->error = (empty($value->publishing)||$value->publishing)?false:true;
            $obj->msg = (!$obj->error)?"ONLINE":"Waiting for Streamer";
            $obj->stream = $value;
            $obj->nclients = intval($value->nclients);
            break;
        }
    }
}
echo json_encode($obj);