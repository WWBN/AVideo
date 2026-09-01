<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->id = 0;

$plugin = AVideoPlugin::loadPluginIfEnabled('LoginControl');

if (!User::isLogged()) {
    $obj->msg = "You can't do this";
    die(json_encode($obj));
}

forbidIfNotPost();
forbidIfInvalidToken();

if (User::isAdmin() && !empty($_REQUEST['users_id'])) {
    $users_id = intval($_REQUEST['users_id']);
}
if (empty($users_id)) {
    $users_id = User::getId();
}
if (empty($users_id)) {
    $obj->msg = "empty users id";
    die(json_encode($obj));
}

// SECURITY FIX (2026-09-01): a password-only attacker (PGP challenge still pending) could
// previously overwrite the victim's registered key with their own, then pass the challenge
// legitimately. Do not remove without re-checking LoginControl::userNeedsToChallengePGP().
if ($users_id == User::getId() && LoginControl::userNeedsToChallengePGP()) {
    $obj->msg = "Please complete your current PGP challenge before changing your key";
    die(json_encode($obj));
}

$obj->id = LoginControl::setPGPKey($users_id, @$_REQUEST['publicKey']);

$obj->error = empty($obj->id);

echo json_encode($obj);
