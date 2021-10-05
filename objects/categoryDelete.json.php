<?php
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
require_once 'category.php';
$obj = new Category($_POST['id']);
echo '{"status":"'.$obj->delete().'"}';
