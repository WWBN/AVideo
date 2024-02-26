<?php

header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/category.php';


$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->id = intval(@$_REQUEST['id']);

if (!Category::canCreateCategory()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

if (!Category::deleteAssets($obj->id)) {
    $obj->error = false;
}else{
    $obj->msg = __("Error on delete");
}

die(json_encode($obj));
