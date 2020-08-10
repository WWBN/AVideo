<?php

header('Content-Type: application/json');

require_once '../../videos/configuration.php';
$response = new stdClass();
$response->error = true;
$response->msg = "";
$obj = AVideoPlugin::getObjectDataIfEnabled('AD_Overlay');
if (!User::isLogged()) {
    $response->msg = "Must Login";
    die(json_encode($response));
}
if (empty($obj)) {
    $response->msg = "not enabled";
    die(json_encode($response));
}
if (empty($obj->allowUserAds) && !User::isAdmin()) {
    $response->msg = "Admin Only";
    die(json_encode($response));
}

if(empty($_POST['addOverlayCode'])){
    $response->msg = "Code is empty";
    die(json_encode($response));
}

$ad = new AD_Overlay_Code(0);
$users_id = User::getId();
if(User::isAdmin()){
    if(!empty($_POST['users_id'])){
        $users_id = intval($_POST['users_id']);
    }
}
$ad->loadFromUser($users_id);
$ad->setCode($_POST['addOverlayCode']);
if(empty($obj->AdminMustApproveUserAds)){
    $ad->setStatus('a');
}else{
    $ad->setStatus('i');
}

if(User::isAdmin()){
    if(!empty($_POST['approveAd']) ){
        if(strtolower($_POST['approveAd'])=='false'){
            $ad->setStatus('i');
        }else{
            $ad->setStatus('a');
        }
    }
    if(!empty($_POST['deleteAd']) ){
        if(strtolower($_POST['deleteAd'])!='false'){
            $ad->setCode("<!-- Deleted by admin on ".date("Y-m-d h:i:s")." -->");
        }
    }
}

if($ad->save()){
    if(!User::isAdmin()){
        sendEmailToSiteOwner("AD Overlay Code Update", "The user [".User::getId()."]".User::getNameIdentification()." send an update to his Overlay Code");
    }
    $response->error = false;
}
die(json_encode($response));

