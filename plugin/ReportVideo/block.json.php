<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';

header('Content-Type: application/json');

forbidIfNotPost();
inputToRequest();
// Parse JSON before reading the target, including when the session is already logged in.
User::loginFromRequestIfNotLogged();
if (!User::isLogged()) {
    forbiddenPage('User not logged', true);
}

$resp = new stdClass();
$resp->error = true;
$resp->msg = "Not ready";
$resp->reported_users_id = intval($_REQUEST['users_id'] ?? 0);

if ($resp->reported_users_id <= 0) {
    $resp->msg = "The user is empty";
    die(json_encode($resp));
}

$plugin = AVideoPlugin::loadPluginIfEnabled('ReportVideo');

if (empty($plugin)) {
    $resp->msg = "Plugin not enabled";
    die(json_encode($resp));
}

if (User::getId() == $resp->reported_users_id) {
    $resp->msg = "You cannot block yourself";
    die(json_encode($resp));
}
$reportedUser = new User($resp->reported_users_id);
if (empty($reportedUser->getUser())) {
    $resp->msg = __('User not found');
    die(json_encode($resp));
}
try {
    if (empty($_REQUEST['unblock'])) {
        $resp = $plugin->block(User::getId(), $resp->reported_users_id);
    } else {
        $resp = $plugin->unblock(User::getId(), $resp->reported_users_id);
    }
} catch (\Throwable $th) {
    _error_log('ReportVideo block: ' . $th->getMessage());
    $resp->error = true;
    $resp->msg = __('An error occurred');
}
die(json_encode($resp));
