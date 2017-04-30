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
$obj = new Video("", "", $_POST['id']);
echo '{"status":"'.$obj->delete().'"}';
