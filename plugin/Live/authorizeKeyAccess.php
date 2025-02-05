<?php

function _getRealIpAddr()
{
    $ip = "127.0.0.1";
    $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            foreach ($ips as $ipCandidate) {
                $ipCandidate = trim($ipCandidate); // Just to be safe
                if (filter_var($ipCandidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $ipCandidate; // Return the first valid IPv4 we find
                } elseif ($header === 'REMOTE_ADDR' && filter_var($ipCandidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $ip = $ipCandidate; // In case no IPv4 is found, set the first IPv6 found from REMOTE_ADDR
                }
            }
        }
    }
    return $ip;
}

function getClientIdentifier()
{
    return md5($_SERVER['HTTP_USER_AGENT'] . _getRealIpAddr());
}

function getTmpFilePath($liveKey)
{
    $clientIdentifier = getClientIdentifier();
    $tmpDir = sys_get_temp_dir();
    return "{$tmpDir}/{$clientIdentifier}_{$liveKey}_v1.tmp";
}

// Get client information and the requested key file
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$requested_key = $_GET['key'] ?? '';

// Implement your authorization logic
$authorized = false; // Set this based on your logic

$uri = $_SERVER["HTTP_X_ORIGINAL_URI"];

// Define a regular expression to capture the key and token parts
$pattern = '#/live/([^/]+)/[0-9]+\.key(\?token=([^&]+))?#i';
$token = '';
// Match the pattern with the URI
if (preg_match($pattern, $uri, $matches)) {
    // $matches[1] contains the key
    $liveKey = $matches[1];
    if(!empty($matches[2])){
        // $matches[2] contains the token
        $token = str_replace('?token=', '', $matches[2]);
    }else{
        error_log("Token not found ".json_encode(array($uri, $_SERVER)));
    }
}
$isCached = false;
if (!empty($liveKey)) {
    $tmpFilePath = getTmpFilePath($liveKey);
}

if (!empty($tmpFilePath) && file_exists($tmpFilePath)) {
    $tolerance = 300; // 5 min
    $content = (file_get_contents($tmpFilePath));
    $time = intval($content);
    $now = time();

    $diff = ($time + $tolerance) - $now;

    if ($diff < 0) {
        $isCached = false;
        if (!empty($_REQUEST['debug'])) {
            error_log("Process download protection cache expired time=$content keyFile=$liveKeyFile $tmpFilePath ");
        }
    } else {
        if (!empty($_REQUEST['debug'])) {
            error_log("Process download protection cache still valid diff={$diff} keyFile=$liveKeyFile $tmpFilePath ");
        }
        $isCached = true;
    }
}

if ($isCached) {
    $msg = 'authorizeKeyAccess: cached Authorized key=' . $liveKey;
    //error_log($msg);
    echo $msg;
} else {
    $doNotConnectDatabaseIncludeConfig = 1;
    $doNotStartSessionIncludeConfig = 1;
    require_once dirname(__FILE__) . '/../../videos/configuration.php';
    $obj = AVideoPlugin::getDataObjectIfEnabled('VideoHLS');
    if (class_exists('VideoHLS')) {
        global $verifyTokenReturnFalseReason;
        $verifyTokenReturnFalseReason = '';
        if (!isAVideoUserAgent() && (empty($_SERVER['HTTP_REFERER']) || !isSameDomain($_SERVER['HTTP_REFERER'], $global['webSiteRootURL']))) {
            $verifyTokenReturnFalseReason = "HTTP_REFERER={$_SERVER['HTTP_REFERER']}, webSiteRootURL={$global['webSiteRootURL']} IP=".getRealIpAddr().' HTTP_USER_AGENT='.$_SERVER['HTTP_USER_AGENT'];
            $authorized = false;
        }else if (isAVideoUserAgent() || empty($obj->downloadProtection) || VideoHLS::verifyToken($token)) {
            $authorized = true;
        }
        if (!$authorized) {
            http_response_code(403);
            $msg = 'authorizeKeyAccess: Access denied ['.$verifyTokenReturnFalseReason.'] '.getRealIpAddr();
            error_log($msg.' '.@$_SERVER['HTTP_REFERER']);
            echo $msg;
        } else {
            if (!empty($tmpFilePath)) {
                $bytes = file_put_contents($tmpFilePath, time());
            }
            $msg = 'authorizeKeyAccess: Authorized key=' . $liveKey . ' uri=' . $uri;
            error_log($msg.' '.@$_SERVER['HTTP_REFERER']);
            echo $msg;
        }
    } else {
        $bytes = file_put_contents($tmpFilePath, time());
        $msg = 'authorizeKeyAccess: VideoHLS is not present ';
        error_log($msg);
        echo $msg;
    }
}
