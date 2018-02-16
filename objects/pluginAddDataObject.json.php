<?php

header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
if(empty($_POST['id'])){
    die('{"error":"'.__("ID can't be blank").'"}');    
}
$obj = new Plugin($_POST['id']);
$obj->setObject_data(addcslashes($_POST['object_data'],'\\'));

echo '{"status":"'.$obj->save().'"}';