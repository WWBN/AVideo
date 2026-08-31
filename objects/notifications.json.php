<?php
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}

require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

allowOrigin();
// Getting the mobile submitted value
$inputJSON = url_get_contents('php://input');
$input = _json_decode($inputJSON, true); //convert JSON into array
if (!empty($input) && empty($_POST)) {
    foreach ($input as $key => $value) {
        $_POST[$key]=$value;
    }
}
if (!empty($_POST['user']) && !empty($_POST['pass'])) {
    $user = new User(0, $_POST['user'], $_POST['pass']);
    $user->login(false, true);
}

$obj = new stdClass();
if (AVideoPlugin::loadPluginIfEnabled("Live")) {
    //$liveStats = url_get_contents("{$global['webSiteRootURL']}plugin/Live/stats.json.php");
    $obj->live = getStatsNotifications();
    // never echo streams the caller isn't authorized to see (same reasoning as plugin/Live/stats.json.php)
    unset($obj->live['hidden_applications']);
}

// remove tags and HTML code from the title, for the mobile app
foreach ($obj->live["applications"] as $key => $value) {
    $obj->live['applications'][$key]['title'] = strip_tags(html_entity_decode($obj->live['applications'][$key]['title']));
}

echo json_encode($obj);
