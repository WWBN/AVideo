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

if (!User::isAdmin()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
$dir = "{$global['systemRootPath']}videos/cache/";
if(!empty($_GET['FirstPage'])){
    $dir .= "firstPage/";
}
rrmdir($dir);
$dir = getCacheDir();
if(!empty($_GET['FirstPage'])){
    $dir .= "firstPage/";
}
rrmdir($dir);
$obj->error = false;
die(json_encode($obj));