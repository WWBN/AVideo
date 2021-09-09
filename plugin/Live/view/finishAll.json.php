<?php
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if(empty($objP)){
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

if(!User::isAdmin()){
    $obj->msg = __('Not Admin');
    die(json_encode($obj));
}
$obj->error = !LiveTransmitionHistory::finishALL();

if(empty($obj->error)){
    $obj->msg = __('All lives were marked as finished');
}

die(json_encode($obj));
?>
