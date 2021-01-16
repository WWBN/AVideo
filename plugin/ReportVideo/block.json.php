<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';

header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "Not ready";

if (empty($_REQUEST['users_id'])) {
    $resp->msg = "The user is empty";
    die(json_encode($resp));
}

if (!User::isLogged()) {
    $resp->msg = "User not logged";
    die(json_encode($resp));
}

$plugin = AVideoPlugin::loadPluginIfEnabled('ReportVideo');

if (empty($plugin)) {
    $resp->msg = "Plugin not enabled";
    die(json_encode($resp));
}

if (User::getId() == $_POST['users_id']) {
    $resp->msg = "You cannot block yourself";
    die(json_encode($resp));
}
if (empty($_REQUEST['unblock'])) {
    $resp = $plugin->block(User::getId(), $_POST['users_id']);
} else {
    $resp = $plugin->unblock(User::getId(), $_POST['users_id']);
}
die(json_encode($resp));
