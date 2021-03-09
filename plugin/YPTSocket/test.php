<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/Message.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';

if (!isCommandLineInterface()) {
    die();
}

$_SERVER["HTTP_USER_AGENT"] = $AVideoStreamer_UA;
$socketobj = AVideoPlugin::getDataObject("YPTSocket");
$address = $socketobj->host;
$port = $socketobj->port;

$SocketSendObj = new stdClass();
$SocketSendObj->webSocketToken = _test_getEncryptedInfo();

$url = "://{$address}:{$port}?webSocketToken={$SocketSendObj->webSocketToken}";
$SocketURL = 'ws' . $url;
_test_send($SocketURL, 'ws');
$SocketURL = 'wss' . $url;
_test_send($SocketURL, 'wss');

$url = "://localhost:{$port}?webSocketToken={$SocketSendObj->webSocketToken}";
$SocketURL = 'ws' . $url;
_test_send($SocketURL, 'ws');
$SocketURL = 'wss' . $url;
_test_send($SocketURL, 'wss');

$url = "://127.0.0.1:{$port}?webSocketToken={$SocketSendObj->webSocketToken}";
$SocketURL = 'ws' . $url;
_test_send($SocketURL, 'ws');
$SocketURL = 'wss' . $url;
_test_send($SocketURL, 'wss');

function _test_send($SocketURL, $msg) {
    global $SocketSendObj;
    echo PHP_EOL . "** Testing {$SocketURL} [$msg]" . PHP_EOL;
    $SocketSendObj->msg = "Testing [$msg] " . date('Y/m/d H:i:s');
    
    \Ratchet\Client\connect($SocketURL)->then(function($conn) {
        global $SocketSendObj;
        $conn->on('message', function($msg) use ($conn) {
            echo "Message received " . json_encode($msg) . PHP_EOL;
        });

        $conn->send(json_encode($SocketSendObj));

        $conn->close();
    }, function ($e) {
        echo ("Could not connect: {$e->getMessage()}" . PHP_EOL);
    });
}

function _test_getEncryptedInfo() {
    $timeOut = 43200; // valid for 12 hours
    $msgObj = new stdClass();
    $msgObj->from_users_id = 0;
    $msgObj->isAdmin = 1;
    $msgObj->user_name = "Testing code";
    $msgObj->browser = "Testing terminal";
    $msgObj->yptDeviceId = "testing-device-" . uniqid();
    $msgObj->token = getToken($timeOut);
    $msgObj->time = time();
    $msgObj->ip = '127.0.0.1';
    $msgObj->send_to_uri_pattern = '';
    $msgObj->autoEvalCodeOnHTML = array();
    $msgObj->selfURI = 'terminal';
    $msgObj->videos_id = 0;
    $msgObj->live_key = '';
    $msgObj->location = false;

    return encryptString(json_encode($msgObj));
}
