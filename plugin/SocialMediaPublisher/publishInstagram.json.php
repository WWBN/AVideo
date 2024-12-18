<?php
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');

//$obj = SocialMediaPublisher::scanInstagam();

$obj = InstagramUploader::publishMediaIfIsReady($_REQUEST['accessToken'], $_REQUEST['containerId'], $_REQUEST['instagramAccountId']);

die(json_encode($obj));
