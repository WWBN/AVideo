<?php
function verify($url) {
    ini_set('default_socket_timeout', 5);
    $cacheFile = sys_get_temp_dir() . "/" . md5($url) . "_verify.log";
    $lifetime = 86400; //24 hours
    error_log("Verification Start {$url}");
    $verifyURL = "https://search.avideo.com/verify.php?url=" . urlencode($url);
    if (!file_exists($cacheFile) || (time() > (filemtime($cacheFile) + $lifetime))) {
        error_log("Verification Creating the Cache {$url}");
        $result = url_get_contents($verifyURL, '', 5);
        file_put_contents($cacheFile, $result);
    } else {
        error_log("Verification GetFrom Cache {$url}");
        $result = file_get_contents($cacheFile);
    }
    error_log("Verification Response ($verifyURL): {$result}");
    return json_decode($result);
}

function isVerified($url) {
    $resultV = verify($url);
    if (!empty($resultV) && !$resultV->verified) {
        error_log("Error on Login not verified");
        return false;
    }
    return true;
}

function make_path($path) {
    if (substr($path, -1) !== '/') {
        $path = pathinfo($path, PATHINFO_DIRNAME);
    }
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

function getSelfUserAgent() {
    $agent = "AVideo WebRTC";
    return $agent;
}

function url_get_contents($url, $ctx = "", $timeout = 0, $debug = false) {
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
    if ($debug) {
        error_log("url_get_contents: Start $url, $ctx, $timeout " . getSelfURI() . " " . getRealIpAddr() . " " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
    }
    $agent = getSelfUserAgent();

    if (empty($ctx)) {
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
    } else {
        $context = $ctx;
    }
    if (ini_get('allow_url_fopen')) {
        if ($debug) {
            error_log("url_get_contents: allow_url_fopen {$url}");
        }
        try {
            $tmp = @file_get_contents($url, false, $context);
            if ($tmp != false) {
                $response = ($tmp);
                if ($debug) {
                    //error_log("url_get_contents: SUCCESS file_get_contents($url) {$response}");
                    error_log("url_get_contents: SUCCESS file_get_contents($url)");
                }
                return $response;
            }
            if ($debug) {
                error_log("url_get_contents: ERROR file_get_contents($url) ");
            }
        } catch (ErrorException $e) {
            if ($debug) {
                error_log("url_get_contents: allow_url_fopen ERROR " . $e->getMessage() . "  {$url}");
            }
            return "url_get_contents: " . $e->getMessage();
        }
    } elseif (function_exists('curl_init')) {
        if ($debug) {
            error_log("url_get_contents: CURL  {$url} ");
        }
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
        if ($debug) {
            error_log("url_get_contents: CURL SUCCESS {$url}");
        }
        return remove_utf8_bom($output);
    }
    if ($debug) {
        error_log("url_get_contents: Nothing yet  {$url}");
    }

    return false;
}
