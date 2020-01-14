<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');

$obj = new stdClass();
$obj->password = @$_REQUEST['pass'];
$obj->encryptedPassword=encryptPassword($obj->password);

echo json_encode($obj);



