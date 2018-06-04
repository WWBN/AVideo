<?php

require_once '../../videos/configuration.php';
session_write_close();
header('Content-Type: image/x-png');
$filename = $global['systemRootPath'] . 'plugin/Live/view/OnAir.jpg';
//echo file_get_contents($filename);exit;

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

$liveLink = new LiveLinksTable($_GET['id']);
if (empty($_GET['format'])) {
    $_GET['format'] = "png";
}
$video = $liveLink->getLink();

    
if (preg_match("/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $video)) {
    $url = $config->getEncoderURL() . "getImage/" . base64_encode($video) . "/{$_GET['format']}";
    if (empty($_SESSION[$url]['expire']) || $_SESSION[$url]['expire'] < time()) {
        $content = url_get_contents($url);
        session_start();
        error_log($url . " Image Expired in ".  date("d/m/Y H:i:s", $_SESSION[$url]['expire'])." NOW is ".  date("d/m/Y H:i:s"));
        $_SESSION[$url] = array('content' => $content, 'expire' => strtotime("+2 min"));
        error_log($url . " New Image will Expired in ".  date("d/m/Y H:i:s", $_SESSION[$url]['expire'])." NOW is ".  date("d/m/Y H:i:s"));
    }
    if(!empty($_SESSION[$url]['content'])){
        echo $_SESSION[$url]['content'];
        error_log($url . " Cached Good until ".  date("d/m/Y H:i:s", $_SESSION[$url]['expire'])." NOW is ".  date("d/m/Y H:i:s"));
    }else{
        echo file_get_contents($filename);
        error_log($url . " Get default image ");
    }
    
} else {
    echo file_get_contents($filename);
    error_log($url . " Invalid URL ");
}