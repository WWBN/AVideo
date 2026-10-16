<?php
require_once '../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');
$name = "advancedCustom.json.php";
$obj = ObjectYPT::getCache($name, 60);
if(empty($obj)){
    $obj = AVideoPlugin::getObjectData("CustomizeAdvanced");
    $objS = AVideoPlugin::getObjectData("Scheduler");
    $objV = AVideoPlugin::getObjectDataIfEnabled("VideoHLS");

    $obj->autoConvertToMp4 = false;
    if(!empty($objV)){
        $obj->autoConvertToMp4 = $objV->autoConvertToMp4;
    }

    $obj->disableReleaseDate = $objS->disableReleaseDate;
    
    ObjectYPT::setCache($name, $obj);
}
// This display preference must reflect the admin's latest choice even while other settings are cached.
$localEncoderSettings = AVideoPlugin::getDataObjectIfEnabled('CustomizeAdvanced');
$obj->showLocalEncoder = !empty($localEncoderSettings->showLocalEncoder);
echo json_encode($obj);