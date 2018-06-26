<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'plugin/LiveChat/Objects/LiveChatObj.php';
header('Content-Type: application/json');

$obj = YouPHPTubePlugin::getObjectData("LiveChat");

$rows = LiveChatObj::getFromChat($_POST['live_stream_code'], $obj->loadLastMessages);

echo json_encode($rows); ?>