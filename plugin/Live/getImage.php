<?php
require_once '../../videos/configuration.php';
session_write_close();
$filename = $global['systemRootPath'] . 'plugin/Live/view/OnAir.jpg';

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

$livet =  LiveTransmition::getFromDbByUserName($_GET['u']);
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

if(Live::isLiveThumbsDisabled()){
    $_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
    $uploadedPoster = $global['systemRootPath'] . Live::getPosterThumbsImage($livet['users_id'], $_REQUEST['live_servers_id']);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster );exit;
    if(file_exists($uploadedPoster)){
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    }
}

$lt = new LiveTransmition($livet['id']);
_error_log("Live:getImage  start");
if($lt->userCanSeeTransmition()){
    $uuid = LiveTransmition::keyNameFix($livet['key']);
    $p = AVideoPlugin::loadPlugin("Live");
    $video = Live::getM3U8File($uuid);
    $url = $config->getEncoderURL()."getImage/". base64_encode($video)."/{$_GET['format']}";
    _error_log("Live:getImage $url");
        
    if(!empty($_SESSION[$url]['content']) && strlen($_SESSION[$url]['content']) === 70808){
        _error_log("Live:getImage  It is the default image, unset it ");
        _session_start();
        unset($_SESSION[$url]);
    }
    
    if (empty($_SESSION[$url]['expire']) || $_SESSION[$url]['expire'] < time()) {
        $content = url_get_contents($url);
        _session_start();
        _error_log("Live:getImage  Image Expired in ".  date("d/m/Y H:i:s", @$_SESSION[$url]['expire'])." NOW is ".  date("d/m/Y H:i:s"));
        $_SESSION[$url] = array('content' => $content, 'expire' => strtotime("+2 min"));
        _error_log("Live:getImage  New Image will Expired in ".  date("d/m/Y H:i:s", $_SESSION[$url]['expire'])." NOW is ".  date("d/m/Y H:i:s"));
    }
    if(!empty($_SESSION[$url]['content'])){
        ob_end_clean();
        echo $_SESSION[$url]['content'];
        _error_log("Live:getImage  Cached Good until ".  date("d/m/Y H:i:s", $_SESSION[$url]['expire'])." NOW is ".  date("d/m/Y H:i:s")." strlen: ". strlen($_SESSION[$url]['content']));
    }else{
        ob_end_clean();
        echo file_get_contents($filename);
        _error_log("Live:getImage  Get default image ");
    }
    
}else{
    _error_log("Live:getImage  Can not see the image");
}
$p = AVideoPlugin::loadPluginIfEnabled("Cache");
if(!empty($p)){
    $p->getEnd();
}