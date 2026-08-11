<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
require_once $global['systemRootPath'] . 'plugin/Live/standAloneFiles/restreamProfiles.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('Live');

if (!User::canStream()) {
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Live_restreams($_POST['id'] ?? '');

if (!empty($o->getUsers_id()) && !User::isAdmin() && $o->getUsers_id() != User::getId()) {
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

if (!User::isAdmin()) {
    $_POST['users_id'] = User::getId();
}

if (empty($_POST['users_id'])) {
    $_POST['users_id'] = User::getId();
}

$streamUrl = trim((string) ($_POST['stream_url'] ?? ''));
$streamKey = trim((string) ($_POST['stream_key'] ?? ''));
$destination = rtrim($streamUrl, '/') . '/' . ltrim($streamKey, '/');
if (empty($streamUrl) || empty($streamKey) || clearCommandURL($destination) === '') {
    $obj->msg = __('Invalid restream URL or stream key');
    die(json_encode($obj));
}

$o->setName($_POST['name']);
$o->setStream_url($streamUrl);
$o->setStream_key($streamKey);
$o->setStatus($_POST['status']);
$o->setParameters($_POST['parameters']);
$o->setUsers_id($_POST['users_id']);

if ($id = $o->save()) {
    $obj->error = false;
}

echo json_encode($obj);
