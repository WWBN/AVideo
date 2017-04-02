<?php
header('Content-Type: application/json');
require_once 'user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'video.php';
$obj = new Video($_POST['title'], "", $_POST['id']);
$obj->setClean_Title($_POST['clean_title']);
$obj->setDescription($_POST['description']);
$obj->setCategories_id($_POST['categories_id']);
$resp = $obj->save();
echo '{"status":"'.!empty($resp).'"}';