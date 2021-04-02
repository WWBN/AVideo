<?php

require_once '../../videos/configuration.php';
session_write_close();
$filename = $global['systemRootPath'] . 'plugin/Live/view/OnAir.jpg';
//echo file_get_contents($filename);exit;

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';

if(empty($_GET['id'])){
    header('Content-Type: image/jpg');
    echo file_get_contents($filename);
    exit;
}

$liveLink = new LiveLinksTable($_GET['id']);
if (empty($_GET['format'])) {
    $_GET['format'] = "png";
    header('Content-Type: image/x-png');
} else if ($_GET['format'] === 'jpg') {
    header('Content-Type: image/jpg');
} else if ($_GET['format'] === 'gif') {
    header('Content-Type: image/gif');
} else if ($_GET['format'] === 'webp') {
    header('Content-Type: image/webp');
} else {
    $_GET['format'] = "png";
    header('Content-Type: image/x-png');
}

if(LiveLinks::isLiveThumbsDisabled()){
    $_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
    $uploadedPoster = $global['systemRootPath'] . Live::getPosterThumbsImage($liveLink->getUsers_id(), $_REQUEST['live_servers_id']);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster );exit;
    if(file_exists($uploadedPoster)){
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    }
}
$video = $liveLink->getLink();

if (preg_match("/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $video)) {
    
    $encoderURL = $config->_getEncoderURL();
    //$encoderURL = $config->getEncoderURL();
    
    $url = "{$encoderURL}getImage/" . base64_encode($video) . "/{$_GET['format']}";
    $name = "liveLinks_getImage_".md5($url);
    $content = ObjectYPT::getCache($name, 600);
    if(Live::isDefaultImage($content)){
        $content = '';
    }
    if(empty($content)){
        session_write_close();
        _mysql_close();
        $content = url_get_contents($url, "", 2);
        if(!empty($content)){
            ObjectYPT::setCache($name, $content);
        }
    }
}

if(!empty($content)){
    echo $content;
}else{
     echo local_get_contents($filename);
}