<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
if (empty($_POST['id'])) {
    die('{"error":"'.__("ID can not be empty").'"}');
}
require_once 'category.php';
$obj = new Comment("", 0, $_POST['id']);
echo '{"status":"'.$obj->delete().'"}';
