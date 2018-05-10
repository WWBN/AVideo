<?php
error_reporting(0);
require_once 'category.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
$categories = Category::getAllCategories();
$total = Category::getTotalCategories();
$breaks = array("<br />","<br>","<br/>");  
foreach ($categories as $key => $value) {
    $categories[$key]['iconHtml'] = "<span class='$value[iconClass]'></span>";     
    $categories[$key]['description'] = str_ireplace($breaks, "\r\n", $value['description']);
    $sql = "SELECT * FROM `category_type_cache` WHERE categoryId = ?";
    $res = sqlDAL::readSql($sql,"i",array($value['id'])); 
    $catTypeCache = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if($catTypeCache!=false){
        if($catTypeCache['manualSet']=="0"){
            $categories[$key]['type'] = "3";
        } else {
            $categories[$key]['type'] = $catTypeCache['type'];
        }
    } else {
        $categories[$key]['type'] = "3";
    } 
}
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($categories).'}';
