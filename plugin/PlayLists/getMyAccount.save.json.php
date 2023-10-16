<?php
require_once '../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
if(!User::isLogged()){
    $obj->msg = "Not logged";   
    die(json_encode($obj));
}
if(!isset($_POST['autoadd_playlist'])){
    $obj->msg = "autoadd_playlist is empty";   
    die(json_encode($obj));
}

$obj->autoadd_playlist = @$_POST['autoadd_playlist'];
$pp = AVideoPlugin::loadPluginIfEnabled('PlayLists');

if(empty($pp)){
    $obj->msg = "Plugin not enabled";   
    die(json_encode($obj));
}

$response = PlayLists::setAutoAddPlaylist(User::getId(), $_POST['autoadd_playlist']);

$obj->error = empty($response);

die(json_encode($obj));