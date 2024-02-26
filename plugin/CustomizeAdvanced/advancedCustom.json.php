<?php
require_once '../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');
$name = "advancedCustom.json.php";
$obj = ObjectYPT::getCache($name, 60);
if(empty($obj)){
    $obj = AVideoPlugin::getObjectData("CustomizeAdvanced");
    $objS = AVideoPlugin::getObjectData("Scheduler");

    $obj->disableReleaseDate = $objS->disableReleaseDate;
    
    ObjectYPT::setCache($name, $obj);
}
echo json_encode($obj);