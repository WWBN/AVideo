<?php
ini_set('max_execution_time', 2);
set_time_limit(2);
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
$_GET['lifetime'] = "10";
require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
session_write_close();
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

require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
// the live users plugin
$liveUsersEnabled = YouPHPTubePlugin::isEnabled("cf145581-7d5e-4bb6-8c12-48fc37c0630d");

$obj->disableGif = $p->getDisableGifThumbs();
$obj->countLiveStream = count($lifeStream);
foreach ($lifeStream as $value){
    if(!empty($value->name)){
        $row = LiveTransmition::keyExists($value->name);
        if(empty($row) || empty($row['public'])){
            continue;
        }
        
        $users = false;
        if($liveUsersEnabled){
            $filename = $global['systemRootPath'] . 'plugin/LiveUsers/Objects/LiveOnlineUsers.php';
            if(file_exists($filename)){
                require_once $filename;
                $liveUsers = new LiveOnlineUsers(0);
                $users = $liveUsers->getUsersFromTransmitionKey($value->name);
            }
        }
        
        $u = new User($row['users_id']);
        $userName = $u->getNameIdentificationBd();
        $user = $u->getUser();
        $photo = $u->getPhotoDB();
        $UserPhoto = $u->getPhoto();
        $obj->applications[] = array("key"=>$value->name, "users"=>$users, "name"=>$userName, "user"=>$user, "photo"=>$photo, "UserPhoto"=>$UserPhoto, "title"=>$row['title']);
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

include $global['systemRootPath'].'objects/include_end.php';