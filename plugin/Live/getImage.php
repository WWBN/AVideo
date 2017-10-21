<?php

$time_start = microtime(true);
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';


$time_end = microtime(true);
$time = $time_end - $time_start;
if ($time > 1) {
    error_log(__FILE__." ".__LINE__ . 'Execution time : ' . $time . ' seconds');
}

$t = LiveTransmition::getFromDbByUserName($_GET['u']);
if (empty($_GET['format'])) {
    $_GET['format'] = "png";
}
$lt = new LiveTransmition($t['id']);

$time_end = microtime(true);
$time = $time_end - $time_start;
if ($time > 1) {
    error_log(__FILE__." ".__FILE__." ".__LINE__ . 'Execution time : ' . $time . ' seconds');
}

if ($lt->userCanSeeTransmition()) {
    header('Content-Type: image/x-png');
    $uuid = $t['key'];
    $p = YouPHPTubePlugin::loadPlugin("Live");
    $video = "{$p->getPlayerServer()}/{$uuid}/index.m3u8";
    $url = $config->getEncoderURL() . "getImage/" . base64_encode($video) . "/{$_GET['format']}";

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    if ($time > 1) {
        error_log(__FILE__." ".__LINE__ . 'Execution time : ' . $time . ' seconds');
    }

    if (empty($_SESSION[$url]['expire']) || $_SESSION[$url]['expire'] < time()) {
        $_SESSION[$url] = array('content' => file_get_contents($url), 'expire' => time("+2 min"));
    }

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    if ($time > 1) {
        error_log(__FILE__." ".__LINE__ . 'Execution time : ' . $time . ' seconds');
    }
    echo $_SESSION[$url]['content'];
    error_log($url . " Image Expired " . intval($_SESSION[$url]['expire'] < time()));
}

$time_end = microtime(true);
$time = $time_end - $time_start;
if ($time > 1) {
    error_log(__FILE__." ".__LINE__ . 'Execution time : ' . $time . ' seconds');
}