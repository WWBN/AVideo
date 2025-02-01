<?php
header('Content-Type: application/json');

if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}
allowOrigin();
$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->key = "";
if (empty($objM)) {
    $obj->msg = "Plugin disabled";
    die(json_encode($obj));
}

$cacheName = "meetkey";
$obj->key = ObjectYPT::getCache($cacheName, 86400); // 1 day

if (empty($obj->key) || strlen($obj->key) < 50) {
    $server = $objM->server->value;
    if ($server == 'custom') {
        $server = $objM->CUSTOM_JITSI_DOMAIN;
        $server = explode(':', $server)[0];
    }
    if (empty($server)) {
        $obj->msg = "The server URL is empty";
    } else {
        $url = "http://key.ypt.me?server=" . urlencode($server);
        //$obj->url = $url;
        $obj->key = url_get_contents($url);
        $obj->length = strlen($obj->key); // 1 day
        if (!empty($obj->key) && $obj->length > 50) {
            $obj->error = false;
            $obj->msg = "got a new key";
            ObjectYPT::setCache($cacheName, $obj->key);
        } else {
            $obj->msg = $obj->key;
            $obj->key = "";
        }
    }
} else {
    $obj->error = false;
}

die(json_encode($obj));
