<?php

$recheckTolerance = 600; // 10 min
require_once '../../videos/configuration.php';
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
if (empty($_SESSION['playerServer'])) {
    _session_start();
    $obj = AVideoPlugin::getObjectData('Live');
    $_SESSION['playerServer'] = $obj->playerServer;
    $_SESSION['useAadaptiveMode'] = $obj->useAadaptiveMode;
} else {
    @$global['mysqli']->close();
}
if ($_SESSION['useAadaptiveMode']) {
    $complement = $_SESSION['playerServer'] . "/";
    $url = $_SESSION['playerServer'] . "/{$uuid}.m3u8";
    $content = url_get_contents($url);
}

if (empty($content)) {
    $complement = $_SESSION['playerServer'] . "/{$uuid}/";
    $url = $_SESSION['playerServer'] . "/{$uuid}/index.m3u8";
    $content = url_get_contents($url);
}

if (empty($_SESSION['m3u8Verified'])) {
    _session_start();
    $_SESSION['m3u8Verified'] = time();
}
$lines = preg_split("/((\r?\n)|(\r\n?))/", $content);
for ($i = 0; $i < count($lines); $i++) {
    if (preg_match('/.*\.(m3u8|ts)$/i', $lines[$i])) {
        echo $complement;
    }
    echo $lines[$i] . PHP_EOL;
}
