<?php
$recheckTolerance = 600; // 10 min
require_once dirname(__FILE__) . '/../../videos/configuration.php';
error_reporting(0);
session_write_close();

if (empty($_GET['uuid'])) {
    die("uuid empty");
}
$uuid = decryptString($_GET['uuid']);

if (empty($uuid)) {
    die("uuid decrypt error");
}
header("Content-Type: audio/x-mpegurl");

if (empty($_SESSION['m3u8Verified']) || $_SESSION['m3u8Verified'] + $recheckTolerance < time()) {
    require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
    // check user groups
    $lt = new LiveTransmition(0);
    $lt->loadByKey($uuid);
    if (!$lt->userCanSeeTransmition()) {
        die("Private stream");
    }

    // check PPV
    if (AVideoPlugin::isEnabledByName("PayPerViewLive")) {
        if (!PayPerViewLive::canUserWatchNow(User::getId(), $lt->getUsers_id())) {
            die("PPV stream");
        }
    }
    unset($_SESSION['m3u8Verified']);
}

if(!isset($_SESSION['playerServer']) || !is_array($_SESSION['playerServer'])){
    _session_start();
    $_SESSION['playerServer'] = array();
}
if(!isset($_SESSION['useAadaptiveMode']) || !is_array($_SESSION['useAadaptiveMode'])){
    _session_start();
    $_SESSION['useAadaptiveMode'] = array();
}

$live_servers_id = Live::getCurrentLiveServersId();

if (true || empty($_SESSION['playerServer'][$live_servers_id])) {
    _session_start();
    $obj = AVideoPlugin::getObjectData('Live');
    $_SESSION['playerServer'][$live_servers_id] = Live::getPlayerServer();
    $_SESSION['useAadaptiveMode'][$live_servers_id] = Live::getUseAadaptiveMode();
} else {
    @$global['mysqli']->close();
}
if ($_SESSION['useAadaptiveMode'][$live_servers_id]) {
    $complement = $_SESSION['playerServer'][$live_servers_id] . "/";
    $url = $_SESSION['playerServer'][$live_servers_id] . "/{$uuid}.m3u8";
    $content = url_get_contents($url);
}
if (empty($content)) {
    $complement = $_SESSION['playerServer'][$live_servers_id] . "/{$uuid}/";
    $url = $_SESSION['playerServer'][$live_servers_id] . "/{$uuid}/index.m3u8";
    $content = url_get_contents($url);
    if (!empty($content)) {
        _session_start();
        $_SESSION['useAadaptiveMode'][$live_servers_id] = 0;
    }
}
if (empty($_SESSION['useAadaptiveMode'][$live_servers_id]) && empty($content)) {
    $complement = $_SESSION['playerServer'][$live_servers_id] . "/";
    $url = $_SESSION['playerServer'][$live_servers_id] . "/{$uuid}.m3u8";
    $content = url_get_contents($url);
    if (!empty($content)) {
        _session_start();
        $_SESSION['useAadaptiveMode'][$live_servers_id] = 1;
    }
}

if(empty($content)){ // get the default loop
    //$complement = "{$global['webSiteRootURL']}plugin/Live/view/loopBGHLS/";
    //$content = file_get_contents("{$global['systemRootPath']}plugin/Live/view/loopBGHLS/index.m3u8");
    include "{$global['systemRootPath']}plugin/Live/view/loopBGHLS/index.m3u8.php";
    exit;
}

if (empty($_SESSION['m3u8Verified'])) {
    _session_start();
    $_SESSION['m3u8Verified'] = time();
}
$lines = preg_split("/((\r?\n)|(\r\n?))/", $content);
for ($i = 0; $i < count($lines); $i++) {
    if (preg_match('/.*\.(m3u8|ts|key)$/i', $lines[$i])) {
        echo $complement;
    }
    echo $lines[$i] . PHP_EOL;
}
