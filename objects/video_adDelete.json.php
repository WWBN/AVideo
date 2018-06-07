<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}
require_once 'video_ad.php';
$obj = new Video_ad("", "", "", "", $_POST['id']);
echo '{"status":"'.$obj->delete().'"}';
