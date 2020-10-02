<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->name = '';

if (empty($_REQUEST['playlist_id'])) {
    $obj->msg = "playlist_id cannot be empty";
    die(json_encode($obj));
}
if (empty($_REQUEST['name'])) {
    $obj->msg = "name cannot be empty";
    die(json_encode($obj));
}

$obj->name = $_REQUEST['name'];

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
$playList = new PlayList($_REQUEST['playlist_id']);
if(!User::isAdmin() && User::getId() != $playList->getUsers_id()){
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

$playList->setName($obj->name);
$obj->error = empty($playList->save());

die(json_encode($obj));
