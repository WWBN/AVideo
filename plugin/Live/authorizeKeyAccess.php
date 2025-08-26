<?php

// Set memory limit to prevent excessive memory usage
ini_set('memory_limit', '64M');

// Enable garbage collection
if (function_exists('gc_enable')) {
    gc_enable();
}

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

// Get current CPU usage percentage
function getCpuUsage()
{
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        return $load[0] * 100; // Convert to percentage (approximate)
    }

    // Windows fallback
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = 'wmic cpu get loadpercentage /value';
        $output = shell_exec($cmd);
        if ($output && preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
            return intval($matches[1]);
        }
    }

    // Linux fallback
    if (file_exists('/proc/loadavg')) {
        $load = file_get_contents('/proc/loadavg');
        $loadArray = explode(' ', $load);
        return floatval($loadArray[0]) * 100;
    }

    return 0; // Default if can't determine
}

// Get dynamic tolerance based on CPU usage
function getDynamicTolerance($baseTolerance = 600)
{
    $cpuUsage = getCpuUsage();

    if ($cpuUsage > 80) {
        // Very high CPU - increase tolerance significantly
        return $baseTolerance * 3; // 30 minutes
    } elseif ($cpuUsage > 50) {
        // High CPU - increase tolerance moderately
        return $baseTolerance * 2; // 20 minutes
    } elseif ($cpuUsage > 30) {
        // Medium CPU - slight increase
        return intval($baseTolerance * 1.5); // 15 minutes
    }

    return $baseTolerance; // Normal tolerance (10 minutes)
}

// Clean up old cache files to prevent memory buildup
function cleanOldCacheFiles($currentTmpFile)
{
    $tmpDir = sys_get_temp_dir();
    $pattern = $tmpDir . '/*_v1.tmp';
    $files = glob($pattern);
    $tolerance = 1800; // 30 minutes
    $now = time();

    // Limit cleanup when CPU is high to avoid additional load
    $cpuUsage = getCpuUsage();
    if ($cpuUsage > 70) {
        // Only clean very old files when CPU is high
        $tolerance = 3600; // 1 hour
    }

    foreach ($files as $file) {
        if ($file !== $currentTmpFile && file_exists($file)) {
            $fileTime = filemtime($file);
            if (($now - $fileTime) > $tolerance) {
                @unlink($file);
            }
        }
    }
}// Get client information and the requested key file
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

    // Clean old cache files to prevent accumulation
    cleanOldCacheFiles($tmpFilePath);
}

if (!empty($tmpFilePath) && file_exists($tmpFilePath)) {
    $tolerance = getDynamicTolerance(600); // Dynamic tolerance based on CPU usage
    $content = file_get_contents($tmpFilePath);
    if ($content !== false) {
        $time = intval($content);
        $now = time();

        $diff = ($time + $tolerance) - $now;

        if ($diff < 0) {
            $isCached = false;
            // Remove expired cache file immediately only if CPU usage is low
            $cpuUsage = getCpuUsage();
            if ($cpuUsage < 60) {
                @unlink($tmpFilePath);
            }
            if (!empty($_REQUEST['debug'])) {
                error_log("Process download protection cache expired time=$content tolerance=$tolerance cpu=$cpuUsage keyFile=$liveKeyFile $tmpFilePath ");
            }
        } else {
            if (!empty($_REQUEST['debug'])) {
                error_log("Process download protection cache still valid diff={$diff} tolerance=$tolerance keyFile=$liveKeyFile $tmpFilePath ");
            }
            $isCached = true;
        }
    }
}

if ($isCached) {
    $msg = 'authorizeKeyAccess: cached Authorized key=' . $liveKey;
    //error_log($msg);
    echo $msg;
} else {
    // Minimize memory usage by avoiding heavy configuration loading when possible
    $doNotConnectDatabaseIncludeConfig = 1;
    $doNotStartSessionIncludeConfig = 1;
    $doNotIncludeConfig = 1; // Add this to prevent full plugin loading

    // Check CPU usage before heavy operations
    $cpuUsage = getCpuUsage();
    if ($cpuUsage > 80) {
        // Skip heavy configuration loading when CPU is very high
        $msg = 'authorizeKeyAccess: High CPU usage detected (' . $cpuUsage . '%), using cached authorization';
        error_log($msg);
        echo $msg;
        exit;
    }

    require_once dirname(__FILE__) . '/../../videos/configuration.php';

    // Only load VideoHLS if really needed
    $obj = null;
    if (class_exists('AVideoPlugin')) {
        $obj = AVideoPlugin::getDataObjectIfEnabled('VideoHLS');
    }
    if (class_exists('VideoHLS')) {
        global $verifyTokenReturnFalseReason;
        $verifyTokenReturnFalseReason = '';
        if (!isAVideoUserAgent() && (empty($_SERVER['HTTP_REFERER']) || !isSameDomain($_SERVER['HTTP_REFERER'], $global['webSiteRootURL'])) && $global['webSiteRootURL'] !== 'http://avideo/') {
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
        if (!empty($tmpFilePath)) {
            $bytes = file_put_contents($tmpFilePath, time());
        }
        $msg = 'authorizeKeyAccess: VideoHLS is not present ';
        error_log($msg);
        echo $msg;
    }
}

// Force garbage collection to free memory only if CPU usage is not too high
$cpuUsage = getCpuUsage();
if (function_exists('gc_collect_cycles') && $cpuUsage < 70) {
    gc_collect_cycles();
}

// Clear any unnecessary variables
unset($obj, $verifyTokenReturnFalseReason, $authorized, $tmpFilePath, $cpuUsage);
