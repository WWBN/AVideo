<?php
// Mints a one-time Companion sign-in link for the current AVideo ADMIN
// (server-to-server, using the plugin's integration key) - the browser only
// ever receives the resulting short-lived link, never the key.
require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->login_url = '';

if (!AVideoPlugin::isEnabledByName('AI')) {
    $obj->msg = __('AI plugin is disabled');
    echo _json_encode($obj);
    exit;
}
// Companion access is for installation administrators only.
if (!User::isAdmin()) {
    $obj->msg = __('Only administrators can open the Companion dashboard');
    echo _json_encode($obj);
    exit;
}
forbidIfNotPost();
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
if ($action === 'token') {
    // The token rendered in the page head expires; the button fetches a
    // fresh one right before asking for the link (same pattern as the tab).
    $obj->error = false;
    $obj->globalToken = getToken(300);
    echo _json_encode($obj);
    exit;
}
if (!isGlobalTokenValid()) {
    $obj->msg = __('Invalid token');
    echo _json_encode($obj);
    exit;
}
$user = new User(User::getId());
$displayName = trim((string) $user->getNameIdentification());
if ($displayName === '') {
    $displayName = trim((string) $user->getUser());
}
$loginUrl = Companion::adminLoginUrl(User::getId(), $displayName);
if (empty($loginUrl)) {
    $obj->msg = __('Companion is not connected or is unreachable right now');
    echo _json_encode($obj);
    exit;
}
$obj->error = false;
$obj->login_url = $loginUrl;
echo _json_encode($obj);
