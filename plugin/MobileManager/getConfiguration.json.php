<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../videos/configuration.php';

$obj = AVideoPlugin::getObjectData("MobileManager");
$obj->EULA = nl2br($obj->EULA->value);
$obj->YPTSocket = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
$refl = new ReflectionClass('SocketMessageType');
$obj->webSocketTypes = json_encode($refl->getConstants());
$obj->webSocketURL = addQueryStringParameter(YPTSocket::getWebSocketURL(true), 'page_title', 'Mobile APP');

echo json_encode($obj);
