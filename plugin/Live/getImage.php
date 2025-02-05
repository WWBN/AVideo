<?php
set_time_limit(10);
$facebookSizeRecomendationW = 1200;
$facebookSizeRecomendationH = 630;

$lifetime = 300;

if (empty($_REQUEST['format'])) {
    $_REQUEST['format'] = "png";
    header('Content-Type: image/x-png');
} elseif ($_REQUEST['format'] === 'jpg') {
    header('Content-Type: image/jpg');
} elseif ($_REQUEST['format'] === 'gif') {
    header('Content-Type: image/gif');
    $lifetime *= 3;
} elseif ($_REQUEST['format'] === 'webp') {
    header('Content-Type: image/webp');
    $lifetime *= 3;
} else {
    $_REQUEST['format'] = "png";
    header('Content-Type: image/x-png');
}

$f = md5(@$_REQUEST['u'] . '_' . @$_REQUEST['live_servers_id'] . '_' . @$_REQUEST['live_index'] . '_' . @$_REQUEST['playlists_id_live']);
$cacheFileImageName = dirname(__FILE__) . "/../../videos/cache/liveImage_{$f}.{$_REQUEST['format']}";
$cacheFileImageNameResized = dirname(__FILE__) . "/../../videos/cache/liveImage_{$f}_{$facebookSizeRecomendationW}X{$facebookSizeRecomendationH}.{$_REQUEST['format']}";
if (empty($_REQUEST['debug']) && file_exists($cacheFileImageName) && (time() - $lifetime <= filemtime($cacheFileImageName))) {
    if (file_exists($cacheFileImageNameResized)) {
        $content = file_get_contents($cacheFileImageNameResized);
        if (!empty($content)) {
            echo $content;
            exit;
        }
    } else if (file_exists($cacheFileImageName)) {
        $content = file_get_contents($cacheFileImageName);
        if (!empty($content)) {
            echo $content;
            exit;
        }
    }
} else {
    if (file_exists($cacheFileImageName)) {
        unlink($cacheFileImageName);
    }
    if (file_exists($cacheFileImageNameResized)) {
        unlink($cacheFileImageNameResized);
    }
}

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_session_write_close();
_error_log('Get Image');
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$livet = LiveTransmition::getFromRequest();
//header('Content-Type: text/plain');var_dump(Live::isLive($livet['users_id']), $livet);exit;
if (!empty($_REQUEST['live_schedule']) && !empty($livet['scheduled_time']) && isTimeForFuture($livet['scheduled_time'], $livet['timezone'])) {
    $array = Live_schedule::getPosterPaths($_REQUEST['live_schedule'], 0);
    $uploadedPoster = $array['path'];
    header('Content-Type: image/jpg');
    if (!file_exists($cacheFileImageNameResized)) {
        //im_resizeV2($uploadedPoster, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
        scaleUpImage($uploadedPoster, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
    }
    echo file_get_contents($cacheFileImageNameResized);
    _error_log('getImage: live does not start yet');
    exit;
}

if (!empty($_REQUEST['debug'])) {
    _error_log('getImage: start');
}
if (empty($livet)) {
    $uploadedPoster = $global['systemRootPath'] . Live::getOfflineImage(false);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster, empty($livet), Live::isLive($livet['users_id']) );exit;
    if (file_exists($uploadedPoster)) {
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        _error_log('getImage: showing offline poster');
        exit;
    } else {
        if (!empty($_REQUEST['debug'])) {
            _error_log('getImage: File NOT exists 1 ' . $uploadedPoster);
        }
    }
} elseif (!Live::isLive($livet['users_id'])) {
    $uploadedPoster = $global['systemRootPath'] . Live::getPoster($livet['users_id'], $_REQUEST['live_servers_id'], $livet['key']);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster, empty($livet), Live::isLive($livet['users_id']) );exit;
    if (file_exists($uploadedPoster)) {
        if (!empty($_REQUEST['debug'])) {
            _error_log('getImage: File NOT exists 2 ' . $uploadedPoster .' '.json_encode($livet));
        }
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    } else {
        if (!empty($_REQUEST['debug'])) {
            _error_log('getImage: File NOT exists 3 ' . $uploadedPoster);
        }
    }
}
if (!empty($_REQUEST['debug'])) {
    _error_log('getImage: continue ' . getSelfURI());
}
$filename = $global['systemRootPath'] . Live::getPosterThumbsImage($livet['users_id'], $_REQUEST['live_servers_id'], false);

if (Live::isLiveThumbsDisabled()) {
    $uploadedPoster = $filename;
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster );exit;
    if (file_exists($uploadedPoster) && !is_dir($uploadedPoster)) {
        header('Content-Type: image/jpg');
        if (!file_exists($cacheFileImageNameResized)) {
            //im_resizeV2($uploadedPoster, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
            scaleUpImage($uploadedPoster, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
        }
        echo file_get_contents($cacheFileImageNameResized);
        exit;
    }
}

$uuid = $livet['key'];

if (!empty($_REQUEST['live_index']) && $_REQUEST['live_index'] !== 'false') {
    $uuid = "{$uuid}-{$_REQUEST['live_index']}";
}

$name = "getLiveImage_{$uuid}_{$_REQUEST['format']}";
if(empty($_REQUEST['debug'])){
    $result = ObjectYPT::getCache($name, $lifetime, true);
}

$socketMessage = [];
$socketMessage['cacheName1'] = $name;
$socketMessage['iscache'] = !empty($result);
$socketMessage['src'] = getSelfURI();
//$socketMessage['src'] = addQueryStringParameter(getSelfURI(), 'cache', time());
$socketMessage['live'] = $livet;
$socketMessage['live_servers_id'] = $_REQUEST['live_servers_id'];

if (!empty($result) && !Live::isDefaultImage($result)) {
    file_put_contents($cacheFileImageName, $result);
    if (!file_exists($cacheFileImageNameResized)) {
        //im_resizeV2($cacheFileImageName, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
        scaleUpImage($uploadedPoster, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
    }
    echo file_get_contents($cacheFileImageNameResized);
} else {
    $socketMessage['key'] = $uuid;
    $socketMessage['autoEvalCodeOnHTML'] = "if(typeof refreshGetLiveImage == 'function'){refreshGetLiveImage('.live_{$socketMessage['live_servers_id']}_{$socketMessage['key']}');}";

    //$uuid = LiveTransmition::keyNameFix($livet['key']);
    $p = AVideoPlugin::loadPlugin("Live");
    $video = Live::getM3U8File($uuid);

    $encoderURL = $config->_getEncoderURL();
    //$encoderURL = $config->getEncoderURL();

    //$url = "{$encoderURL}getImage/" . base64_encode($video) . "/{$_REQUEST['format']}";
    $url = "{$encoderURL}objects/getImage.php";
    $url = addQueryStringParameter($url, 'base64Url', base64_encode($video));
    $url = addQueryStringParameter($url, 'format', $_REQUEST['format']);

    if (!empty($_REQUEST['debug'])) {
        _error_log("Live:getImage $url");
    }
    //header('Content-Type: text/plain');var_dump($url);exit;
    _session_write_close();
    _mysql_close();
    $content = url_get_contents($url, '', 2);

    if (empty($content)) {
        if (!file_exists($cacheFileImageNameResized)) {
            //im_resizeV2($filename, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
            scaleUpImage($filename, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
        }
        echo file_get_contents($cacheFileImageNameResized);
    } else {
    }

    ob_end_clean();

    if (!empty($content)) {
        $isDefaultImage = Live::isDefaultImage($content);
        if ($isDefaultImage) {
            //header('Content-Type: text/plain');var_dump(__LINE__, $url);exit;
            if (!empty($_REQUEST['debug'])) {
                _error_log("Live:getImage  It is the default image, try to show the poster");
            }
            echo $content;
        } else {
            //header('Content-Type: text/plain');var_dump(__LINE__, $url);exit;
            $socketMessage['cacheName2'] = $name;
            $socketMessage['cacheName3'] = ObjectYPT::setCache($name, $content);
            $socketMessage['cacheName4'] = strlen($content);
            echo $content;
            //$socketObj = sendSocketMessageToAll($socketMessage, 'socketLiveImageUpdateCallback');
        }
    } else {
        $result = file_get_contents($filename);
        if (!Live::isDefaultImage($result)) {
            if (!file_exists($cacheFileImageNameResized)) {
                //im_resizeV2($filename, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
                scaleUpImage($filename, $cacheFileImageNameResized, $facebookSizeRecomendationW, $facebookSizeRecomendationH);
            }
            echo file_get_contents($cacheFileImageNameResized);
        } else {
            echo file_get_contents($cacheFileImageName);
        }

        if (!empty($_REQUEST['debug'])) {
            _error_log("Live:getImage  Get default image ");
        }
    }
}
