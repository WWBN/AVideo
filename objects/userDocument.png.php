<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if(empty($_GET['users_id'])){
    die('users_id empty');
}
if(!User::isAdmin()){
    if(User::getId() != $_GET['users_id']){
        die('users_id is not you');
    }
}

header("Content-Type: image/png");

$document = User::getDocumentImage($_GET['users_id']);
if(empty($document)){
    $document = file_get_contents("{$global['systemRootPath']}view/img/id.png");
}
echo $document;