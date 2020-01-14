<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}
$obj = new PlayList($_POST['playlist_id']);
if(User::getId() != $obj->getUsers_id()){
    die('{"error":"'.__("Permission denied").'"}');
}
$obj->setName($_POST['name']);
echo '{"status":"'.$obj->save().'"}';
