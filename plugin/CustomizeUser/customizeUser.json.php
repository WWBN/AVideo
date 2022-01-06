<?php
require_once '../../videos/configuration.php';
session_write_close();
header('Content-Type: application/json');
$name = "customizeUser.json.php";
$obj = ObjectYPT::getCache($name, 60);
if (empty($obj)) {
    $obj = AVideoPlugin::getObjectData("CustomizeUser");
    ObjectYPT::setCache($name, $obj);
}
echo json_encode($obj);
