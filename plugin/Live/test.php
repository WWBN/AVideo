<?php

$timeStarted = microtime(true);

$statsURL = $_REQUEST['statsURL'];
if (empty($statsURL) || $statsURL == "php://input" || !preg_match("/^http/", $statsURL)) {
    _log('this is not a URL ');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

_log('Starting try to get URL ' . $statsURL);

$result = url_get_contents($statsURL, 2);

if ($result) {
    _log('<span style="background-color: green; padding: 1px 4px; color: #FFF;">SUCCESS</span>');
} else {
    _log('<span style="background-color: red; padding: 1px 4px; color: #FFF;">FAIL</span>');
}

_log('Finish try to get URL ');

$timeElapsed = number_format(microtime(true) - $timeStarted,5);
if($timeElapsed>=2){
    _log('IMPORTANT: your stats took longer than 2 seconds to respond, the Streamer has a 2 seconds timeout rule ');
}

function url_get_contents($url, $timeout = 0) {
    _log('url_get_contents start timeout=' . $timeout);
    $agent = "AVideoStreamer";

    $opts = array(
        'http' => array('header' => "User-Agent: {$agent}\r\n"),
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true,
        ),
    );
    if (!empty($timeout)) {
        ini_set('default_socket_timeout', $timeout);
        $opts['http'] = array('timeout' => $timeout);
    }

    $context = stream_context_create($opts);

    if (ini_get('allow_url_fopen')) {
        try {
            $tmp = file_get_contents($url, false, $context);
            if (empty($tmp)) {
                _log('file_get_contents fail return an empty content');
                return false;
            }else{
                _log('file_get_contents works');
                return true;
            }
        } catch (ErrorException $e) {
            _log('file_get_contents fail catch error: ' . $e->getMessage());
            return false;
        }
    } elseif (function_exists('curl_init')) {
        _log('allow_url_fopen is NOT enabled but curl_init is, we will try CURL');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (!empty($timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout + 10);
        }
        $output = curl_exec($ch);
        curl_close($ch);

        if (empty($output)) {
            _log('curl_init fail to download');
            return false;
        } else {
            _log('curl_init success to download');
            return true;
        }
    } else {
        _log('IMPORTANT: allow_url_fopen is NOT enabled also curl_init is NOT enable, please investigate it and make sure it is enabled');
    }


    _log('Try wget');
    // try wget
    $tmpDir = sys_get_temp_dir();
    if (empty($tmpDir)) {
        _log('IMPORTANT: your sys_get_temp_dir is empty');
        return false;
    }
    if (!is_writable($tmpDir)) {
        _log('IMPORTANT: we cannot write in your temp directory ' . $tmpDir);
        return false;
    }
    $tmpDir = rtrim($tmpDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    $filename = $tmpDir . md5($url);
    if (wget($url, $filename)) {
        $result = file_get_contents($filename);
        unlink($filename);
        if (!empty($result)) {
            _log('wget works ');
            return true;
        } else {
            _log('wget fail ');
        }
    }
    unlink($filename);

    return false;
}

function wget($url, $filename) {
    if (empty($url) || $url == "php://input" || !preg_match("/^http/", $url)) {
        _log('this is not a URL ');
        return false;
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        _log('this is a windows OS ');
        return false;
    }
    $cmd = "wget --tries=1 {$url} -O {$filename} --no-check-certificate";
    exec($cmd);
    if (!file_exists($filename)) {
        _log('wget download fail, we cannot read the file: ' . $filename);
        return false;
    }
    if (empty(filesize($filename))) {
        _log('wget download fail, the file is empty: ' . $filename);
        return false;
    } else {
        _log('wget download success, the file is NOT empty: ' . $filename);
        return true;
    }
}

function _log($msg) {
    global $timeStarted;
    $timeElapsed = number_format(microtime(true) - $timeStarted,5);
    echo '[' . date('Y-m-d H:i:s') . "] Time Elapsed: {$timeElapsed} seconds - " . $msg . '<br>' . PHP_EOL;
}
