<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'locale/function.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'video.php';
$obj = new Video($_POST['title'], "", $_POST['id']);
$obj->setClean_Title($_POST['clean_title']);
$obj->setDescription($_POST['description']);
$obj->setCategories_id($_POST['categories_id']);
$resp = $obj->save();
echo '{"status":"'.!empty($resp).'"}';