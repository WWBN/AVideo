<?php

header('Content-Type: application/json');
require_once 'user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'category.php';
$obj = new Category(@$_POST['id']);
$obj->setName($_POST['name']);
$obj->setClean_name($_POST['clean_name']);
echo '{"status":"'.$obj->save().'"}';