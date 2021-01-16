<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

require_once dirname(__FILE__) . '/../../videos/configuration.php';

$obj = AVideoPlugin::getObjectData("MobileManager");
$obj->EULA = nl2br($obj->EULA->value);

echo json_encode($obj);
