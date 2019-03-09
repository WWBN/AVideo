<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';

header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = "Not ready";

if (empty($_POST['videos_id']) && !empty($_GET['videos_id'])) {
   $_POST['videos_id'] = $_GET['videos_id'];
}
if(!empty($_GET['user']) && !empty($_GET['pass'])){
    $user = new User(0, $_GET['user'], $_GET['pass']);
    $user->login(false, true);
}
if (empty($_POST['videos_id'])) {
    $resp->msg = "The video is empty";
    die(json_encode($resp));
}

if (!User::isLogged()) {
    $resp->msg = "User not logged";
    die(json_encode($resp));
}

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('ReportVideo');

if (empty($plugin)) {
    $resp->msg = "Plugin not enabled";
    die(json_encode($resp));
}

$resp = $plugin->report(User::getId(), $_POST['videos_id']);
die(json_encode($resp));