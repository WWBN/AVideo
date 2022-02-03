<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

error_reporting(E_ALL);

$obj = new stdClass();
$obj->error = true;
$obj->msg = true;
$obj->categories_id = 0;
$obj->image1 = 0;
$obj->image2 = 0;
$obj->usergroups_ids_array = @$_REQUEST['usergroups_ids_array'];

if (!Category::canCreateCategory()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

$objCat = new Category(intval(@$_POST['id']));
$objCat->setName($_POST['name']);
$objCat->setClean_name($_POST['clean_name']);
$objCat->setDescription($_POST['description']);
$objCat->setIconClass($_POST['iconClass']);
//$objCat->setNextVideoOrder($_POST['nextVideoOrder']);
$objCat->setSuggested($_POST['suggested']);
$objCat->setParentId($_POST['parentId']);
$objCat->setPrivate($_POST['private']);
$objCat->setAllow_download($_POST['allow_download']);
$objCat->setOrder($_POST['order']);
_error_log('CategoryAddnew: Saving '.$_POST['name']);
$obj->categories_id = $objCat->save();
//$objCat->setType($_POST['type'],$id);

if (!empty($obj->categories_id)) {
    _error_log('CategoryAddnew: '.$obj->categories_id);
    $obj->error = false;
    $path = Category::getCategoryPhotoPath($obj->categories_id);
    $obj->image1 = saveCroppieImage($path['path'], "image1");
    $obj->image1P = $path['path'];
    _error_log('CategoryAddnew: save image 1 '.$path['path']);
    $path = Category::getCategoryBackgroundPath($obj->categories_id);
    $obj->image2 = saveCroppieImage($path['path'], "image2");
    $obj->image2P = $path['path'];
    _error_log('CategoryAddnew: save image 2 '.$path['path']);
    
    // save usergroups
    _error_log('CategoryAddnew: save usergroups '. json_encode($obj->usergroups_ids_array));
    Category::setUsergroups($obj->categories_id, $obj->usergroups_ids_array);
}
die(json_encode($obj));
