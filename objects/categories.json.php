<?php

require_once 'category.php';
header('Content-Type: application/json');
$categories = Category::getAllCategories();
$total = Category::getTotalCategories();
foreach ($categories as $key => $value) {
    $categories[$key]['iconHtml'] = "<span class='$value[iconClass]'></span>";
}
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($categories).'}';