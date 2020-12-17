<?php
error_reporting(0);
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = true;
$obj->categories_id = 0;
$obj->image1 = 0;
$obj->image2 = 0;

if(!Category::canCreateCategory()){
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
$objCat = new Category(intval(@$_POST['id']));
$objCat->setName($_POST['name']);
$objCat->setClean_name($_POST['clean_name']);
$objCat->setDescription(nl2br ($_POST['description']));
$objCat->setIconClass($_POST['iconClass']);
$objCat->setNextVideoOrder($_POST['nextVideoOrder']);
$objCat->setParentId($_POST['parentId']);
$objCat->setPrivate($_POST['private']);
$objCat->setAllow_download($_POST['allow_download']);
$objCat->setOrder($_POST['order']);



$obj->categories_id = $objCat->save();
//$objCat->setType($_POST['type'],$id);

if(!empty($obj->categories_id)){
    $obj->error = false;
    $path = Category::getCategoryPhotoPath($obj->categories_id);
    $obj->image1 = saveCroppieImage($path['path'], "image1");
    $obj->image1P = $path['path'];
    $path = Category::getCategoryBackgroundPath($obj->categories_id);
    $obj->image2 = saveCroppieImage($path['path'], "image2");
    $obj->image2P = $path['path'];
}

die(json_encode($obj));
