<?php

require_once '../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

$liveLink = new LiveLinksTable($_GET['id']);
if (empty($_GET['format'])) {
    $_GET['format'] = "png";
}
header('Content-Type: image/x-png');
$video = $liveLink->getLink();

$filename = $global['systemRootPath'] . 'plugin/Live/view/OnAir.jpg';
    
if (preg_match("/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $video)) {
    $url = $config->getEncoderURL() . "getImage/" . base64_encode($video) . "/{$_GET['format']}";
    if (empty($_SESSION[$url]['expire']) || $_SESSION[$url]['expire'] < time()) {
        $content = url_get_contents($url);
        session_start();
        $_SESSION[$url] = array('content' => $content, 'expire' => time("+2 min"));
    }
    if(!empty($_SESSION[$url]['content'])){
        echo $_SESSION[$url]['content'];
    }else{
        echo file_get_contents($filename);
    }
    error_log($url . " Image Expired " . intval($_SESSION[$url]['expire'] < time()));
} else {
    echo file_get_contents($filename);
    error_log($url . " Invalid URL ");
}