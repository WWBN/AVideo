<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";

// mutates plugin config; GET is <img>-reachable and the auto CSRF guard only arms for POST
forbidIfNotPost();

$resp->users_id = intval($_POST['users_id']);
$resp->add = intval($_POST['add']);

if (empty($resp->users_id)) {
    forbiddenPage('User is empty');
}

if (!User::isAdmin()) {
    forbiddenPage('Admin only');
}

// CSRF protection: SameSite=None on session cookies (cross-origin iframe embeds)
// means a cross-site POST will carry the admin session; a token is required.
forbidIfInvalidToken();

$plugin = AVideoPlugin::loadPluginIfEnabled('Gallery');

if (empty($plugin)) {
    forbiddenPage('Gallery not enabled');
}


$resp->response = Gallery::setAddChannelToGallery($resp->users_id, $resp->add);

$resp->error = empty($resp->response);

die(json_encode($resp));
