<?php
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');

if (!User::isLogged()) {
    forbiddenPage('You must login');
}
if (empty($_REQUEST['id'])) {
    $obj->msg = "ID is empty";
    die(json_encode($obj));
}

$o = new Publisher_user_preferences($_REQUEST['id']);
// the refresh button is shown to every user for their own connections
if (empty($o->getId()) || (!User::isAdmin() && $o->getUsers_id() != User::getId())) {
    forbiddenPage('You cannot edit this');
}

$resp = SocialMediaPublisher::revalidateTokenAndSave($_REQUEST['id']);

// only the outcome: the full response holds the old and new tokens
$obj->error = !empty($resp->error);
$obj->msg = $obj->error ? __('The connection could not be renewed. Please reconnect your account.') : __('The connection was renewed.');
if ($obj->error) {
    _error_log('SocialMediaPublisher refresh.json.php id=' . intval($_REQUEST['id']) . ' ' . (is_string($resp->msg) ? $resp->msg : ''));
}

die(json_encode($obj));
