<?php
require_once 'category.php';
header('Content-Type: application/json');
$categories = Category::getAllCategories();
$total = Category::getTotalCategories();
$breaks = array("<br />","<br>","<br/>");  
foreach ($categories as $key => $value) {
    $categories[$key]['iconHtml'] = "<span class='$value[iconClass]'></span>";     
    $categories[$key]['description'] = str_ireplace($breaks, "\r\n", $value['description']); 
}
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($categories).'}';
