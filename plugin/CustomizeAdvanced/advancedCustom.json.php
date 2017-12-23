<?php
require_once '../../videos/configuration.php';
header('Content-Type: application/json');
$obj = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
echo json_encode($obj);