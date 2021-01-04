<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!Permissions::canClearCache()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
_session_start();
$_SESSION['user']['sessionCache']['getAllCategoriesClearCache'] = 1;
clearCache();
ObjectYPT::deleteALLCache();
$obj->error = false;
die(json_encode($obj));