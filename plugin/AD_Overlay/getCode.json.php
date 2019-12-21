<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';

$response = new stdClass();
$response->error = true;
$response->msg = "";
$response->status = "";

$obj = AVideoPlugin::getObjectDataIfEnabled('AD_Overlay');
if (empty($_GET['users_id'])) {
    $response->msg = "we need a user";
    die(json_encode($response));
}

$ad = new AD_Overlay_Code(0);
$users_id = intval($_GET['users_id']);
$ad->loadFromUser($users_id);

$response->status = $ad->getStatus();
if(empty($response->status)){
    $response->msg = "<!-- no ad available for this user -->";
    die(json_encode($response));
}else if ($response->status === 'i') {
    if (!User::isAdmin()) {
        $response->msg = "the ad is disabled";
        die(json_encode($response));
    }
}

$response->error = false;
$response->msg = ($ad->getCode());
die(json_encode($response));

