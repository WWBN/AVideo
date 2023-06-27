<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

$plugin = AVideoPlugin::loadPluginIfEnabled("PlayLists");

if (empty($plugin)) {
    die('{"error":"Plugin not enabled"}');
}
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}
if (empty($_POST['name'])) {
    die('{"error":"'.__("Name can't be blank").'"}');
}
$obj = new PlayList(@$_POST['id']);

if (PlayLists::canManageAllPlaylists()) {
    if(!empty($_REQUEST['users_id'])){
        $obj->setUsers_id($_REQUEST['users_id']);
    }
}else{
    if(!empty($obj->getUsers_id())){
        forbidIfItIsNotMyUsersId($obj->getUsers_id());
    }
}
$obj->setName($_POST['name']);
$obj->setStatus($_POST['status']);
echo '{"status":"'.$obj->save().'", "users_id":"'.$obj->getUsers_id().'"}';
