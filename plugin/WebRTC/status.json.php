<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

$objP = AVideoPlugin::getDataObject('WebRTC');

header('Content-Type: application/json');
$obj = array('error'=>false, 'msg'=>'');

$file = WebRTC::getWebRTC2RTMPFile();

$obj['port'] = $objP->port;
$obj['isActive'] = WebRTC::checkIfIsActive();
$obj['json'] = WebRTC::getJson();
$obj['log'] = WebRTC::getLog();
$obj['is_executable'] = is_executable($file);
$obj['portOpenInternally'] = isPortOpenInternal('127.0.0.1', $objP->port) ;
$obj['portOpenExternally'] = isPortOpenExternal($objP->port);
$obj['portOpenExternallyResponse'] = $isPortOpenExternalResponse;
$obj['file'] = $file;
$obj['file_exists'] = file_exists($file);
echo json_encode($obj);
?>
