<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'locale/function.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canComment()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'comment.php';
$obj = new Comment($_POST['comment'], $_POST['video']);
echo '{"status":"'.$obj->save().'"}';