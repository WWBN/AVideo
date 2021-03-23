<?php

require_once '../../videos/configuration.php';
session_write_close();
$filename = $global['systemRootPath'] . 'plugin/Live/view/OnAir.jpg';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$livet = LiveTransmition::getFromDbByUserName($_GET['u']);

if (empty($livet) || !Live::isLive($livet['users_id'])) { 
    $uploadedPoster = $global['systemRootPath'] . Live::getOfflineImage(false);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster );exit;
    if (file_exists($uploadedPoster)) {
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    }
}

$filename = $global['systemRootPath'] . Live::getPosterThumbsImage($livet['users_id'], $_REQUEST['live_servers_id'], $_REQUEST['live_index']);

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

if (Live::isLiveThumbsDisabled()) {
    $_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
    $uploadedPoster = $filename;
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster );exit;
    if (file_exists($uploadedPoster)) {
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    }
}

$name = "getLiveImage_{$livet['key']}_{$_GET['format']}";
$result = ObjectYPT::getCache($name, 600);
if (!empty($result)) {
    echo $result;
} else {
    $uuid = $livet['key'];
    if(!empty($_REQUEST['live_index']) && $_REQUEST['live_index']!=='false'){
        $uuid = "{$uuid}-{$_REQUEST['live_index']}";
    }
    
    //$uuid = LiveTransmition::keyNameFix($livet['key']);
    $p = AVideoPlugin::loadPlugin("Live");
    $video = Live::getM3U8File($uuid);
    
    //header('Content-Type: text/plain');var_dump($livet, $video);exit;
    $encoderURL = $config->_getEncoderURL();
    //$encoderURL = $config->getEncoderURL();
    
    $url = "{$encoderURL}getImage/" . base64_encode($video) . "/{$_GET['format']}";
    _error_log("Live:getImage $url");
    session_write_close();
    _mysql_close();
    $content = url_get_contents($url, '', 2, true);

    if (empty($content)) {
        echo file_get_contents($filename);
    } else {
        
    }

    ob_end_clean();

    if (!empty($content)) {
        if (strlen($content) === 70808) {
            _error_log("Live:getImage  It is the default image, try to show the poster ");
            echo file_get_contents($filename);
        } else {
            ObjectYPT::setCache($name, $content);
            echo $content;
        }
    } else {
        echo file_get_contents($filename);
        _error_log("Live:getImage  Get default image ");
    }
}