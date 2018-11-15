<?php
error_reporting(0);
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

if(!Category::canCreateCategory()){
    die('{"error":"'.__("Permission denied").'"}');
}
$obj = new Category(@$_POST['id']);
$obj->setName($_POST['name']);
$obj->setClean_name($_POST['clean_name']);
$obj->setDescription(nl2br ($_POST['description']));
$obj->setIconClass($_POST['iconClass']);
$obj->setNextVideoOrder($_POST['nextVideoOrder']);
$obj->setParentId($_POST['parentId']);
$obj->setPrivate($_POST['private']);



$id = $obj->save();
$obj->setType($_POST['type'],$id);

echo '{"status":"'.$id.'"}';
