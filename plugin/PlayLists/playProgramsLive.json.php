<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
header('Content-Type: application/json');

_error_log("playProgramsLive:: Start ". json_encode($_GET));
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$users_id = User::getId();
if (!$users_id) {
    $obj->msg = __("Permission denied");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

if (!User::canStream()) {
    $obj->msg = __("User cannot stream");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$playlistPlugin = AVideoPlugin::getObjectDataIfEnabled('PlayLists');

if (empty($playlistPlugin)) {
    $obj->msg = __("Programs plugin not enabled");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$api = AVideoPlugin::getObjectDataIfEnabled('API');

if (empty($obj)) {
    $obj->msg = __("API plugin not enabled");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$live = AVideoPlugin::getObjectDataIfEnabled("Live");
if (empty($live)) {
    $obj->msg = __("Live Plugin is not enabled");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$playlists_id = intval($_GET['playlists_id']);
if (empty($playlists_id)) {
    $obj->msg = __("Programs id error");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$pl = new PlayList($playlists_id);
if (User::getId() != $pl->getUsers_id() && !User::isAdmin()) {
    $obj->msg = __("Programs does not belong to you");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}

$lt = new LiveTransmition(0);
$lt->loadByUser($users_id);
$key = $lt->getKey();

// get the encoder
$encoder = $config->_getEncoderURL();

$obj->encoder = $encoder;


$status = json_decode(url_get_contents($encoder."status"));
if(empty($status->version) || version_compare($status->version, "3.2") < 0){
    $obj->msg = __("Your Encoder MUST be version 3.2 or greater");
    _error_log("playProgramsLive:: {$obj->msg}");
    die(json_encode($obj));
}
ini_set('max_execution_time', 0);
Live::stopLive($users_id);
$webSiteRootURL = urlencode($global['webSiteRootURL']);
$live_servers_id = Live::getCurrentLiveServersId();
$videosListToLive = "{$encoder}videosListToLive?playlists_id={$playlists_id}&APISecret={$api->APISecret}&webSiteRootURL={$webSiteRootURL}&user=".User::getUserName()."&pass=".User::getUserPass();
//$obj->url = $videosListToLive;
$obj->videosListToLive = url_get_contents($videosListToLive);
$obj->error = false;
$obj->msg = __("Your stream will start soon");
die(json_encode($obj));