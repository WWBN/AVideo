<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/Message.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if (!isCommandLineInterface()) {
    die();
}

$count = 0;
$responses = array();

_log('** Starting socket test **');

$_SERVER["HTTP_USER_AGENT"] = $AVideoStreamer_UA;
$socketobj = AVideoPlugin::getDataObject("YPTSocket");
$address = $socketobj->host;
$port = $socketobj->port;
$socketobj->forceNonSecure = false;
        
$url = "://localhost:{$port}";
$SocketURL = 'ws' . $url;
_test_send($SocketURL, 'ws');
if (empty($socketobj->forceNonSecure)) {
    $SocketURL = 'wss' . $url;
    _test_send($SocketURL, 'wss');
}

$url = "://127.0.0.1:{$port}";
$SocketURL = 'ws' . $url;
_test_send($SocketURL, 'ws');
if (empty($socketobj->forceNonSecure)) {
    $SocketURL = 'wss' . $url;
    _test_send($SocketURL, 'wss');
}

$url = "://{$address}:{$port}";
$SocketURL = 'ws' . $url;
_test_send($SocketURL, 'ws');
if (empty($socketobj->forceNonSecure)) {
    $SocketURL = 'wss' . $url;
    _test_send($SocketURL, 'wss');
}

function _test_send($SocketURL, $msg) {
    global $SocketSendObj, $count;
    $_count = $count;
    $_msg = "{$SocketURL} on " . date('Y/m/d H:i:s');
    _log("Testing connection with [{$_count}]: " . $_msg);

    $SocketSendObj = new stdClass();
    $SocketSendObj->webSocketToken = _test_getEncryptedInfo($_msg);
    $SocketSendObj->msg = $_msg;
    $SocketURL .= "?webSocketToken={$SocketSendObj->webSocketToken}";
    \Ratchet\Client\connect($SocketURL)->then(function($conn) use ($_count) {
        global $SocketSendObj;
        $conn->on('message', function($msg) use ($conn, $_count) {
            global $responses;
            $json = _json_decode($msg->getPayload());
            //var_dump($json);
            $parts = explode(':', $json->msg->test_msg);
            $c = new AVideoSocketConfiguration($parts[0], $parts[2], $parts[1], true);
            $responses[] = $c;
            $c->log();
            printIfComplete();
        });

        $conn->send(json_encode($SocketSendObj));

        $conn->close();
    }, function ($e) {
        global $responses;
        preg_match('/(tcp|tls):\/\/([^:]+):([0-9]+)/i', $e->getMessage(), $matches);
        if (empty($matches)) {
            _log("ERROR on get connect response [" . $e->getMessage() . "]");
            //$responses[] = $e->getMessage();
        }else{
            $c = new AVideoSocketConfiguration($matches[1], $matches[3], $matches[2], false, $e->getMessage());
            $responses[] = $c;
            $c->log();
        }
        printIfComplete();
    });

    $count++;
}

function _test_getEncryptedInfo($msg) {
    $timeOut = 43200; // valid for 12 hours
    $msgObj = new stdClass();
    $msgObj->from_users_id = 0;
    $msgObj->isAdmin = 1;
    $msgObj->test_msg = $msg;
    $msgObj->user_name = SocketMessageType::TESTING;
    $msgObj->browser = SocketMessageType::TESTING;
    $msgObj->yptDeviceId = SocketMessageType::TESTING . "-" . uniqid();
    $msgObj->token = getToken($timeOut);
    $msgObj->time = time();
    $msgObj->ip = '127.0.0.1';
    $msgObj->send_to_uri_pattern = '';
    $msgObj->autoEvalCodeOnHTML = array();
    $msgObj->selfURI = SocketMessageType::TESTING . '-terminal';
    $msgObj->videos_id = 0;
    $msgObj->live_key = '';
    $msgObj->location = false;

    return encryptString(json_encode($msgObj));
}

function _log($msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    ob_flush();
}

class AVideoSocketConfiguration {

    public $wss;
    public $port;
    public $host;
    public $success;
    public $message;

    function __construct($wss, $port, $host, $success, $message = '') {
        if ($wss == 'tls') {
            $wss = 'wss';
        } else if ($wss == 'tcp') {
            $wss = 'ws';
        }
        $this->wss = $wss;
        $this->port = intval(preg_replace('/([0-9]+).*/', '$1', $port));
        $this->host = preg_replace('/[^0-9a-z_.-]/i', '', $host);
        $this->success = $success;
        $this->message = $message;
    }

    function log() {
        $msg = $this->getSecureText();
        if ($this->success) {
            if ($this->isLocalhost()) {
                $msg .= "\e[1;33;40m";
                $msg .= "Good news, your localhost connects, that means your port [{$this->port}] is open ";
            } else {
                $msg .= "\e[1;32;40m";
                $msg .= 'CONNECTION SUCCESS ';
            }
        } else {
            $msg .= "\e[1;31;40m";
            $msg .= 'CONNECTION FAIL ';
        }
        $msg .= $this->toURL();
        $msg .= "\e[0m ";
        $msg .= $this->message;

        _log($msg);
    }

    function getSecureText() {
        $msg = "";
        if ($this->isSecure()) {
            $msg .= "\e[1;47;42m";
            $msg .= "   SECURE CONNECTION   \e[0m ";
        } else {
            $msg .= "\e[1;37;43m";
            $msg .= " NOT SECURE CONNECTION \e[0m ";
        }
        return $msg;
    }

    function toURL() {
        return "{$this->wss}://$this->host:$this->port";
    }

    function isLocalhost() {
        if (preg_match('/localhost/i', $this->host) || preg_match('/127.0.0.1/i', $this->host)) {
            return true;
        }
        return false;
    }

    function isSecure() {
        return $this->wss == 'wss';
    }

}

function printIfComplete() {
    global $count, $responses;
    if (count($responses) == $count) {
        foreach ($responses as $key => $value) {
            if (empty($value) || empty($value->success) || $value->isLocalhost()) {
                unset($responses[$key]);
            }
        }
        $msg = ' We found ' . count($responses) . ' possible configurations:' . PHP_EOL;
        foreach ($responses as $value) {
            $msg .= PHP_EOL . '              ' . $value->getSecureText() . PHP_EOL;
            $msg .= '-------------------------------------------------------' . PHP_EOL;
            $msg .= '*** Force not to use wss (non secure): ' . ($value->wss == 'ws' ? 'Checked' : 'Unchecked') . ' ' . PHP_EOL;
            $msg .= '*** Server Port: ' . ($value->port) . PHP_EOL;
            $msg .= '*** Server host: ' . ($value->host) . PHP_EOL;
            $msg .= '-------------------------------------------------------' . PHP_EOL . PHP_EOL;
        }
        /*
        if(empty($responses)){
            $msg .= 'Restarting socket server' . PHP_EOL;
            $msg .= restartServer();
            $msg .= 'Restarting complete' . PHP_EOL;
        }
         * 
         */
        _log($msg);
    }
}
