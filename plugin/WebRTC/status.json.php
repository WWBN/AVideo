<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

$objP = AVideoPlugin::getDataObject('WebRTC');

header('Content-Type: application/json');
$obj = array('error'=>false, 'msg'=>'');

$obj['port'] = $objP->port;
$obj['isActive'] = WebRTC::checkIfIsActive();
$obj['json'] = WebRTC::getJson();
$obj['log'] = WebRTC::getLog();

echo json_encode($obj);
?>
