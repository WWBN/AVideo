<?php
require_once '../../videos/configuration.php';
session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$videos_id = floatval(intval($_POST['videos_id']));
if(empty($videos_id)){
    $obj->msg = "Video id is empty";   
    die(json_encode($obj));
}

$video = new Video("", "", $videos_id);
if(empty($video->getFilename())){
    $obj->msg = "Video does not exists";   
    die(json_encode($obj));
}

$value = floatval($_POST['value']);
if(empty($value)){
    $obj->msg = "value is empty";   
    die(json_encode($obj));
}

if(!User::isLogged()){
    $obj->msg = "Not logged";   
    die(json_encode($obj));
}

$cu = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');

if(empty($cu)){
    $obj->msg = "Plugin not enabled";   
    die(json_encode($obj));
}

$wallet = AVideoPlugin::loadPluginIfEnabled("YPTWallet");

if(empty($wallet)){
    $obj->msg = "Plugin wallet not enabled";   
    die(json_encode($obj));
}

$valid = Captcha::validation(@$_POST['captcha']);

if(empty($valid)){
    $obj->msg = "Invalid captcha";   
    die(json_encode($obj));
}

if(YPTWallet::transferBalance(User::getId(), $video->getUsers_id(), $value, "Donation to video ($videos_id) ".$video->getClean_title())){
    $obj->error = false;
    AVideoPlugin::afterDonation(User::getId(), $videos_id, $value);
}
die(json_encode($obj));