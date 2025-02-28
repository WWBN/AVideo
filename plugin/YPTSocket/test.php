<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/Message.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED);
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

if (!isPortOpen('127.0.0.1', $port)) {
    _log("Port {$port} is not open on localhost");
    die();
}else{
    _log("Sucess: Port {$port} is open on localhost");
}

if (!isDomainResolving($address)) {
    _log("Domain {$address} is not resolving");
    die();
}else{
    _log("Sucess: Domain {$address} is resolving");
}

if (!isPortOpen($address, $port)) {
    _log("Port {$port} is not open on {$address}");
    die();
}else{
    _log("Sucess: Port {$port} is open on {$address}");
}

// Use the function
checkSSL($address);  // For the domain's SSL check
checkSSL('127.0.0.1');  // For the localhost SSL check

_log("Testing Socket connection");

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

function isPortOpen($host, $port) {
    $output = [];
    $result = null;
    exec("nc -zv {$host} {$port} 2>&1", $output, $result);
    foreach ($output as $line) {
        _log($line);
    }
    return $result === 0;
}

function isDomainResolving($domain) {
    $output = [];
    $result = null;
    exec("nslookup {$domain} 2>&1", $output, $result);
    foreach ($output as $line) {
        _log($line);
    }
    return $result === 0;
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
            $payload = $msg->getPayload();
            $json = _json_decode($payload);
            //var_dump($json);
            if(!empty($json->msg) && !is_object($json->msg)){
                $parts = explode(':', $json->msg->test_msg);
                $c = new AVideoSocketConfiguration($parts[0], $parts[2], $parts[1], true);
                $responses[] = $c;
                $c->log();
            }else{
                $responses[] = "Could not decode response {$payload}";
            }
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
    $msgObj->yptDeviceId = SocketMessageType::TESTING . "-" . _uniqid();
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

function checkSSL($host) {
    $url = "https://{$host}";
    _log("Checking SSL for {$host}");
    
    // Initialize a cURL session
    $ch = curl_init();
    
    // Set options for the cURL request
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // we only want the headers
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // verify SSL cert
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // verify if SSL matches hostname

    // Execute the request
    $response = curl_exec($ch);
    
    if ($response === false) {
        // Handle SSL error
        $error = curl_error($ch);
        _log("SSL issue detected: {$error}");
    } else {
        // SSL appears to be fine
        _log("SSL is valid for {$host}");
    }
    
    // Close the cURL session
    curl_close($ch);
}

