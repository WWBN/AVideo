<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$t = LiveTransmition::getFromDbByUserName($_GET['u']);
$lt = new LiveTransmition($t['id']);
if($lt->userCanSeeTransmition()){
    header('Content-Type: image/x-png');
    $uuid = $t['key'];
    $p = YouPHPTubePlugin::loadPlugin("Live");
    $video = "{$p->getPlayerServer()}/{$uuid}/index.m3u8";
    $url = $config->getEncoderURL()."getImage/". base64_encode($video);
    echo file_get_contents($url);
}