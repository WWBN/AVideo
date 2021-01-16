<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_servers.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Live_servers(@$_POST['id']);
$o->setName($_POST['name']);
$o->setUrl(@$_POST['url']);
$o->setStatus($_POST['status']);
$o->setRtmp_server($_POST['rtmp_server']);
$o->setPlayerServer($_POST['playerServer']);
$o->setStats_url($_POST['stats_url']);
$o->setDisableDVR(@$_POST['disableDVR']);
$o->setDisableGifThumbs(@$_POST['disableGifThumbs']);
$o->setUseAadaptiveMode(@$_POST['useAadaptiveMode']);
$o->setProtectLive(@$_POST['protectLive']);
$o->setGetRemoteFile($_POST['getRemoteFile']);
$o->setRestreamerURL($_POST['restreamerURL']);
$o->setControlURL($_POST['controlURL']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
