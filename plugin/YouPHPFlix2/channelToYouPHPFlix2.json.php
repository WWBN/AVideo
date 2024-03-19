<?php

require_once '../../videos/configuration.php';

header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "";
$resp->users_id = intval($_REQUEST['users_id']);
$resp->add = intval($_REQUEST['add']);

if (empty($resp->users_id)) {
    forbiddenPage('User is empty');
}

if (!User::isAdmin()) {
    forbiddenPage('Admin only');
}

$plugin = AVideoPlugin::loadPluginIfEnabled('YouPHPFlix2');

if (empty($plugin)) {
    forbiddenPage('YouPHPFlix2 not enabled');
}


$resp->response = YouPHPFlix2::setAddChannelToYouPHPFlix2($resp->users_id, $resp->add);
    
$resp->error = empty($resp->response);

die(json_encode($resp));
