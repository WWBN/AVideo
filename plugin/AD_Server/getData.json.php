<?php
header('Content-Type: application/json');

require_once '../../videos/configuration.php';
allowOrigin();
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->views = 0;
$ad_server = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');
if(empty($ad_server)){
    $obj->msg = "not enabled";
    die(json_encode($obj));
}

$obj->error = false;
$obj->views = VastCampaignsLogs::getViews();

echo json_encode($obj);