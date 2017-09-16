<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'video.php';
$obj = new Video("", "", $_POST['id']);
if(empty($obj)){
    die("Object not found");
}
$obj->setStatus($_POST['status']);
$resp = $obj->save();
echo '{"status":"'.!empty($resp).'"}';
