<?php

header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}
if (empty($_POST['name'])) {
    die('{"error":"'.__("Name can't be blank").'"}');
}
$obj = new PlayList(@$_POST['id']);
$obj->setName($_POST['name']);
$obj->setStatus($_POST['status']);
echo '{"status":"'.$obj->save().'"}';
