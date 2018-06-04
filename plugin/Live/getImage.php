<?php
require_once '../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if(!empty($_GET['c'])){
    $user = User::getChannelOwner($_GET['c']);
    if(!empty($user)){
        $_GET['u'] = $user['user'];
    }
}

$t = LiveTransmition::getFromDbByUserName($_GET['u']);
if(empty($_GET['format'])){
    $_GET['format'] = "png";
}
$lt = new LiveTransmition($t['id']);
if($lt->userCanSeeTransmition()){
    header('Content-Type: image/x-png');
    $uuid = $t['key'];
    $p = YouPHPTubePlugin::loadPlugin("Live");
    $video = "{$p->getPlayerServer()}/{$uuid}/index.m3u8";
    $url = $config->getEncoderURL()."getImage/". base64_encode($video)."/{$_GET['format']}";
    if(empty($_SESSION[$url]['expire']) || $_SESSION[$url]['expire'] < time()){
        $content = url_get_contents($url);
        session_start();
        $_SESSION[$url] = array('content'=>$content, 'expire' => strtotime("+2 min") ); 
    }
    echo $_SESSION[$url]['content'];
    error_log($url." Image Expired ".intval($_SESSION[$url]['expire'] < time()));
}
$p = YouPHPTubePlugin::loadPluginIfEnabled("Cache");
if(!empty($p)){
    $p->getEnd();
}