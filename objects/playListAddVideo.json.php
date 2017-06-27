<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}

$obj = new PlayList($_POST['playlists_id']);
if(empty($obj || User::getId()!=$obj->getUsers_id()) || empty($_POST['videos_id'])){
    return false;
}

echo '{"status":"'.$obj->addVideo($_POST['videos_id'], $_POST['add']).'"}';