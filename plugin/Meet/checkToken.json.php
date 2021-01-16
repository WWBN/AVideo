<?php
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}
$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($objM)) {
    die("Plugin disabled");
}
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->match = false;
$obj->secret = $_GET['secret'];

if(empty($_GET['secret'])){
    $obj->msg = "Empty Token";
    die(json_encode($obj));
}

if($objM->secret === $_GET['secret']){
    $obj->msg = "Token and secret match {$_GET['secret']}";
    $obj->error = false;
    $obj->match = true;
}else{
    $obj->msg = "Different token and secret ";
}

die(json_encode($obj));
?>