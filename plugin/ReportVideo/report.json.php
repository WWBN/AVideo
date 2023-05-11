<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';

header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "Not ready";

if (empty($_REQUEST['videos_id']) && !empty($_GET['videos_id'])) {
    $_REQUEST['videos_id'] = $_GET['videos_id'];
}
if (!empty($_GET['user']) && !empty($_GET['pass'])) {
    $user = new User(0, $_GET['user'], $_GET['pass']);
    $user->login(false, true);
}

if (empty($_REQUEST['videos_id']) && empty($_REQUEST['comments_id'])) {
    if (empty($_REQUEST['videos_id'])) {
        $resp->msg = "The video is empty";
        die(json_encode($resp));
    }
    if (empty($_REQUEST['comments_id'])) {
        $resp->msg = "The comment is empty";
        die(json_encode($resp));
    }
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

if (!empty($_REQUEST['videos_id'])) {
    $resp = $plugin->report(User::getId(), $_REQUEST['videos_id']);
}else{
    // fake report
    $resp = new stdClass();
    $resp->error = false;
    $resp->msg = "Comment {$_REQUEST['comments_id']} was reported as inappropriate";

    $siteOwnerSent = sendEmailToSiteOwner($resp->msg, $resp->msg);
}
die(json_encode($resp));
