<?php
$configFile = '../../../../videos/configuration.php';
$localServer = true;
if (file_exists($configFile)) {
    include_once $configFile;
    $live = AVideoPlugin::getObjectDataIfEnabled('Live');
    if (empty($live)) {
        return false;
    }
    $webRTCServerURL = Live::getWebRTCServerURL();
    $OME_HLS_STREAM_PORT = 7770;
    $OME_API_PORT = 7771;
    $OME_SOCKET_PORT = 7772;
    $OME_STREAM_PORT_TLS = 7773;
    $OME_TCP_RELAY_ADDRESS = 7774;
    $OME_ICE_CANDIDATES = '7775-7779';
    $AccessToken = $global['salt'].$ServerHost;
    $pushRTMP = false;
    
    $files = array(
        'CertPath'=>'/etc/letsencrypt/live/'.$ServerHost.'/cert.pem', 
        'KeyPath'=>'/etc/letsencrypt/live/'.$ServerHost.'/privkey.pem', 
        'ChainCertPath'=>'/etc/letsencrypt/live/'.$ServerHost.'/chain.pem'
    );
    
} else {
    $configFile = dirname(__FILE__) . '/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
        require_once dirname(__FILE__) . '/extra_functions.php';
    } else {
        die('you need a configuration file on '.$configFile.PHP_EOL);
    }
}

$parse = parse_url($webRTCServerURL);
$domain = $parse['host'];
$domain = str_replace("www.", "", $domain);
$domain = preg_match("/^\..+/", $domain) ? ltrim($domain, '.') : $domain;
$domain = preg_replace('/:[0-9]+$/','', $domain);
$ServerHost = $domain;

$AccessToken = md5($AccessToken);

$AccessToken = $AccessToken . md5($ServerHost) . $OME_API_PORT;

function getLinks($stream) {
    global $ServerHost, $OME_SOCKET_PORT, $OME_STREAM_PORT_TLS, $OME_HLS_STREAM_PORT;
    $links = array(
        'publish_webrtc' => 'wss://' . $ServerHost . ':' . $OME_SOCKET_PORT . '/app/' . $stream . '?direction=send&transport=tcp',
        'publish_rtmp' => 'rtmp://' . $ServerHost . ':' . $OME_SOCKET_PORT . '/app/' . $stream,
        'webrtc' => 'wss://' . $ServerHost . ':' . $OME_SOCKET_PORT . '/app/' . $stream,
        'hls' => 'https://' . $ServerHost . ':' . $OME_STREAM_PORT_TLS . '/app/' . $stream . '/playlist.m3u8',
        'hls_local' => 'http://127.0.0.1:' . $OME_HLS_STREAM_PORT . '/app/' . $stream . '/playlist.m3u8',
        'mpeg' => 'https://' . $ServerHost . ':' . $OME_STREAM_PORT_TLS . '/app/' . $stream . '/manifest.mpd',
        'mpeg_low_latency' => 'https://' . $ServerHost . ':' . $OME_STREAM_PORT_TLS . '/app/' . $stream . '/manifest_ll.mpd'
    );

    return $links;
}

function startLive($key, $RTMPLinkWithOutKey, $stream, $id) {
    global $ServerHost, $OME_API_PORT;
    $postFields = array(
        'id' => $id,
        'stream' => array('name' => $stream),
        'protocol' => "rtmp ",
        'url' => $RTMPLinkWithOutKey,
        'streamKey' => $key
    );

    $url = "http://{$ServerHost}:{$OME_API_PORT}/v1/vhosts/default/apps/app:startPush";

    return requestWebRTCAPI($url, $postFields);
}

function stopLive($id) {
    global $ServerHost, $OME_API_PORT;
    $postFields = array(
        'id' => $id
    );

    $url = "http://{$ServerHost}:{$OME_API_PORT}/v1/vhosts/default/apps/app:stopPush";

    return requestWebRTCAPI($url, $postFields);
}

function listLives() {
    global $ServerHost, $OME_API_PORT;

    $url = "http://{$ServerHost}:{$OME_API_PORT}/v1/vhosts/default/apps/app:pushes";

    return requestWebRTCAPI($url, $postFields);
}

function stopUnused() {
    $obj = listLives();
    $stopped = array();
    foreach ($obj->responseJSON->response as $key => $value) {
        if ($value->secondsAgo > 60) {
            if (empty($value->sentBytes) && empty($value->sentTime)) {
                if ($value->state !== 'pushing') {
                    $stopped[] = stopLive($value->id);
                }
            }
        }
    }
    return $stopped;
}

function requestWebRTCAPI($url, $postFields = array()) {

    global $AccessToken;

    $obj = new stdClass();
    $obj->error = true;
    $obj->response = false;
    $obj->msg = '';
    $obj->now = date('Y-m-d\TH:i:s');


    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Accept: application/json",
        "authorization: Basic " . base64_encode($AccessToken),
    ));

    if (!empty($postFields)) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
    }

    $obj->response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        $obj->msg = "WebRTCLiveCam:request cURL Error #:" . $err;
        error_log($obj->msg);
        return $obj;
    }
    $obj->responseJSON = json_decode($obj->response);
    if ($obj->responseJSON->statusCode != 200) {
        $obj->msg = $obj->responseJSON->message;
    } else {
        $time = time();
        foreach ($obj->responseJSON->response as $key => $value) {
            $obj->responseJSON->response[$key]->secondsAgo = $time - strtotime($obj->responseJSON->response[$key]->createdTime);
        }

        $obj->error = false;
    }

    return $obj;
}
