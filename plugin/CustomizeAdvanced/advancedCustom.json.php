<?php
require_once '../../videos/configuration.php';
session_write_close();
header('Content-Type: application/json');
$obj = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
echo json_encode($obj);
include $global['systemRootPath'].'objects/include_end.php';