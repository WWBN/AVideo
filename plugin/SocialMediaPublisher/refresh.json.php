<?php
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');

if (!User::isAdmin()) {
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}
if (empty($_REQUEST['id'])) {
    $obj->msg = "ID is empty";
    die(json_encode($obj));
}

$o = new Publisher_user_preferences($_REQUEST['id']);
if (!User::isAdmin() && $o->getUsers_id() != User::getId()) {
    $obj->msg = "You cannot edit this";
    die(json_encode($obj));
}

$obj = SocialMediaPublisher::revalidateTokenAndSave($_REQUEST['id']);

die(json_encode($obj));
