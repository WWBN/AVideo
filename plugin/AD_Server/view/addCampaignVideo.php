<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new VastCampaignsVideos(0);
$o->setVast_campaigns_id($_POST['vast_campaigns_id']);
$o->setVideos_id($_POST['videos_id']);
$o->setLink($_POST['uri']);
$o->setAd_title($_POST['title']);
$o->setStatus('a');

if($o->save()){
    $obj->error = false;
}
echo json_encode($obj);
