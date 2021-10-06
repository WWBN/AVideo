<?php

error_log(json_encode($_SERVER));
require_once './functions.php';
header('Content-Type: application/json');

if (!empty($_REQUEST['list'])) {
    die(json_encode(listLives()));
} else if (!empty($_REQUEST['stop'])) {
    die(json_encode(stopUnused()));
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->webRTCServerURL = $webRTCServerURL;
if(!empty($_REQUEST['webSiteRootURL'])){
    if(isValidURL($_REQUEST['webSiteRootURL'])){
        $obj->webSiteRootURL = $_REQUEST['webSiteRootURL'];
    }else{
        $obj->webSiteRootURL = base64_decode($_REQUEST['webSiteRootURL']);
    }    
}else{
    $obj->webSiteRootURL = @$_SERVER['HTTP_REFERER'];
}
$parts = explode('plugin/', $obj->webSiteRootURL);

$obj->webSiteRootURL = $parts[0];

$obj->token = @$_REQUEST['token'];

if (empty($obj->webSiteRootURL)) {
    $obj->webSiteRootURL = @$_SERVER['HTTP_ORIGIN'];
}

if (empty($obj->webSiteRootURL)) {
    $obj->msg = "webSiteRootURL is empty";
    die(json_encode($obj));
}
// check if it is ban
if (!isVerified($obj->webSiteRootURL)) {
    $obj->msg = "Site is banned";
    die(json_encode($obj));
}

if (empty($obj->token)) {
    $obj->msg = "token is empty";
    die(json_encode($obj));
}

if(!preg_match('/192.168.1.4/', $obj->webSiteRootURL)){
    $obj->verifyURL = "{$obj->webSiteRootURL}plugin/Live/verifyToken.json.php?token={$obj->token}";
    $obj->response = url_get_contents($obj->verifyURL);

    if (empty($obj->response)) {
        $obj->msg = "We could not verify the token in your site {$obj->verifyURL}";
        die(json_encode($obj));
    }
    $obj->json = json_decode($obj->response);
    error_log($obj->response);
    if (!is_object($obj->json)) {
        $obj->msg = "Error on your site response " . $obj->response;
        die(json_encode($obj));
    }

    $obj->key = $obj->json->key;
    if (empty($obj->key)) {
        $obj->msg = "We could not get your stream key";
        die(json_encode($obj));
    }
}


$obj->RTMPLinkWithOutKey = $obj->json->RTMPLinkWithOutKey;

$obj->host = parse_url($obj->webSiteRootURL, PHP_URL_HOST);
$obj->id = $obj->host . '_' . $obj->key;

$obj->error = false;

switch ($_REQUEST['command']) {
    case 'start':
        $indexes = array('stream');

        foreach ($indexes as $value) {
            $obj->$value = preg_replace('/[^0-9a-z._:\/?=-]/i', '', @$_REQUEST[$value]);
            if (empty($obj->$value)) {
                $obj->msg = "{$value} is empty";
                die(json_encode($obj));
            }
        }

        stopLive($obj->id);
        sleep(1);
        $obj->response = startLive($obj->key, $obj->RTMPLinkWithOutKey, $obj->stream, $obj->id);

        break;
    case 'stop':
        $obj->response = stopLive($obj->id);

        break;
    default:
        break;
}

if (is_object($obj->response)) {
    $obj->error = $obj->response->error;
    $obj->msg = $obj->response->msg;
}

die(json_encode($obj));
