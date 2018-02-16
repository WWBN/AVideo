<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
require_once 'video.php';
if (!is_array($_POST['id'])) {
    $_POST['id'] = array($_POST['id']);
}
$id = 0;
foreach ($_POST['id'] as $value) {    
    $obj = new Video("", "", $value);
    $obj->setIsSuggested($_POST['isSuggested']);
    $id = $obj->save();
}

echo '{"status":"'.$id.'"}';
