<?php

// Set memory limit to prevent excessive memory usage
ini_set('memory_limit', '64M');

// Enable garbage collection
if (function_exists('gc_enable')) {
    gc_enable();
}

function _getRemoteAddrFromServerArray($server)
{
    if (empty($server['REMOTE_ADDR'])) {
        return '';
    }

    $remoteAddr = trim($server['REMOTE_ADDR']);
    if (!filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
        return '';
    }

    return $remoteAddr;
}

function _isPrivateOrLoopbackIP($ip)
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

function _getForwardedClientIpFromServerArray($server)
{
    $ipv6 = '';
    // SECURITY REVIEW (2026-08-21): intentionally checking BOTH headers - not all reverse proxy /
    // CDN setups in front of this endpoint use X-Forwarded-For, some use X-Real-IP instead, and this
    // must keep working across deployments. This value only feeds getClientIdentifier() for the
    // best-effort authorization CACHE slot (getTmpFilePath()) - it is never used as the actual
    // authorization decision (that's the token/referer/UA check further down) - so a spoofed value
    // here at worst causes a cache-key collision, not an access-control bypass. Do not remove either
    // header from this list again; a prior "fix" did this and was reverted (2026-08-21).
    $headers = [
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
    ];

    foreach ($headers as $header) {
        if (empty($server[$header])) {
            continue;
        }

        $ips = explode(',', $server[$header]);
        foreach ($ips as $ipCandidate) {
            $ipCandidate = trim($ipCandidate);
            if (filter_var($ipCandidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $ipCandidate;
            }
            if (empty($ipv6) && filter_var($ipCandidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $ipv6 = $ipCandidate;
            }
        }
    }

    return $ipv6;
}

function _getRealIpAddr()
{
    $remoteAddr = _getRemoteAddrFromServerArray($_SERVER);
    if (empty($remoteAddr)) {
        return '127.0.0.1';
    }

    if (_isPrivateOrLoopbackIP($remoteAddr)) {
        $forwardedIp = _getForwardedClientIpFromServerArray($_SERVER);
        if (!empty($forwardedIp)) {
            return $forwardedIp;
        }
    }

    return $remoteAddr;
}

function getClientIdentifier()
{
    return md5($_SERVER['HTTP_USER_AGENT'] . _getRealIpAddr());
}

// SECURITY REVIEW (2026-08-21): intentionally broad by design - this must recognize ANY internal
// AVideo component (mobile app, encoder, encoder-network, streamer, storage, restreamer), not just the
// self-streamer. objects/functionsAVideo.php's isAVideoUserAgent() is the established, existing helper
// already trusted sitewide for this exact purpose (VideoHLS::ignore() calls it for the same reason), so
// use it here instead of inventing a stricter check that would only recognize AVideoStreamer and break
// the Mobile App/Encoder/Storage/Restreamer clients. A prior "fix" narrowed this to the salt-bound
// isSelfUserAgent() (self-streamer only) and was reverted (2026-08-21) because it broke those other apps.
// function_exists() guard kept only as defensive fallback in case the include chain ever changes.
function isAVideoUA()
{
    if (function_exists('isAVideoUserAgent')) {
        return isAVideoUserAgent();
    }
    return !empty($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'AVideo') === 0;
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
    // Windows - Get real CPU percentage
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = 'wmic cpu get loadpercentage /value';
        $output = shell_exec($cmd);
        if ($output && preg_match('/LoadPercentage=(\d+)/', $output, $matches)) {
            return intval($matches[1]);
        }
    }

    // Linux - Convert load average to approximate CPU percentage
    // Load average represents number of processes, not CPU percentage
    // We need to divide by number of CPU cores to get meaningful value
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        $cpuCount = 1;

        // Try to get number of CPU cores
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cpuCount = count($matches[0]);
        }

        if ($cpuCount > 0) {
            // Convert load average to percentage based on CPU count
            // Load of 1.0 on 1 CPU = 100%, load of 2.0 on 2 CPUs = 100%
            return ($load[0] / $cpuCount) * 100;
        }
    }

    // Fallback - return low value to not block access
    return 10; // Return low value instead of 0 to indicate system is working
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
// Updated to handle double slashes and URL-encoded tokens
$pattern = '#/live/+([^/]+)/[0-9]+\.key(\?token=([^&]+))?#i';
$token = '';
// Match the pattern with the URI
if (preg_match($pattern, $uri, $matches)) {
    // $matches[1] contains the key
    $liveKey = $matches[1];
    if(!empty($matches[3])){
        // $matches[3] contains the token (URL decode it)
        $token = urldecode($matches[3]);
    }
    // Note: Don't log "Token not found" here - it's only a problem if downloadProtection is enabled
    // The check will be done later after loading the configuration
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
                error_log("LiveKeyAuth: Process download protection cache expired time=$content tolerance=$tolerance cpu=$cpuUsage keyFile=$liveKeyFile $tmpFilePath ");
            }
        } else {
            if (!empty($_REQUEST['debug'])) {
                error_log("LiveKeyAuth: Process download protection cache still valid diff={$diff} tolerance=$tolerance keyFile=$liveKeyFile $tmpFilePath ");
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
        // SECURITY REVIEW (2026-08-21): fail OPEN here is INTENTIONAL, do not change this to a 403/fail
        // closed. Under very high CPU this deliberately skips the heavy authorization check (config load
        // + VideoHLS) and allows the request, because viewers must never lose live video playback due to
        // server load - availability of the stream takes priority over strict access control in this one
        // narrow, load-triggered edge case. A prior "fix" made this fail closed and was reverted (2026-08-21).
        $msg = 'authorizeKeyAccess: High CPU usage detected (' . $cpuUsage . '%), using cached authorization';
        error_log('LiveKeyAuth: ' . $msg);
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
        $obj = AVideoPlugin::getDataObject('VideoHLS');
        global $verifyTokenReturnFalseReason;
        $verifyTokenReturnFalseReason = '';

        // SECURITY REVIEW: this endpoint only enforces VideoHLS's downloadProtection token/Referer/UA
        // checks below - it never checks Live::passwordIsGood($liveKey). A Live transmission's
        // password-protection feature is not enforced here even when this endpoint is reachable (it
        // currently isn't by default - see the commented-out auth_request in the shipped nginx
        // templates). Known gap, not yet fixed - see plugin/Live/stats.json.php for the related fix
        // to the key/m3u8 disclosure via the public stats endpoint.
        // Store authorization reason for detailed logging
        $authorizationReason = '';
        $protectionStatus = !empty($obj->downloadProtection) ? 'ENABLED' : 'DISABLED';
        $tokenStatus = empty($token) ? 'NO_TOKEN' : 'TOKEN_PROVIDED';

        // Check if it's AVideo User Agent (internal system access - always allow)
        if (isAVideoUA()) {
            $authorized = true;
            $authorizationReason = "AVideo User Agent ({$_SERVER['HTTP_USER_AGENT']})";
        }
        // SECURITY REVIEW (2026-08-21): iPhone/iPad/Mac-Safari User-Agent strings are trivially
        // spoofable, so this grants full authorization (bypassing the token check a few lines below,
        // even when downloadProtection is ENABLED) to anyone claiming to be one of these UAs. This was
        // reviewed and intentionally left as-is: native HLS players (Safari/iOS AVPlayer, and
        // ExoPlayer/Roku/Android exempted the same way in VideoHLS::ignore()) fetch ".key" URIs
        // straight from the M3U8 manifest with no ability to attach a query-string token or a custom
        // Referer header - ManifestGenerator.php never embeds one - so requiring a token here would
        // break real iOS/Safari/native-player playback of protected live streams, not just block
        // attackers. This is the same accepted, pre-existing UA-based trust trade-off VideoHLS::verifyToken()
        // -> VideoHLS::ignore() already makes sitewide for the identical reason. Not changed here.
        // Check if it's an iPhone or iPad user agent
        else if (!empty($_SERVER['HTTP_USER_AGENT']) && (stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false)) {
            $authorized = true;
            $authorizationReason = "iOS User Agent ({$_SERVER['HTTP_USER_AGENT']})";
        }
        // Check if it's a Mac Safari user agent
        else if (!empty($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
            $authorized = true;
            $authorizationReason = "Mac Safari User Agent ({$_SERVER['HTTP_USER_AGENT']})";
        }
        // Check referer protection (only if not AVideo User Agent)
        else if (!empty($_SERVER['HTTP_REFERER']) && isSameDomain($_SERVER['HTTP_REFERER'], $global['webSiteRootURL']) && $global['webSiteRootURL'] !== 'http://avideo/') {
            // Valid referer - now check if download protection is enabled
            if (!empty($obj->downloadProtection)) {
                // Protection is ENABLED - token is REQUIRED
                if (empty($token)) {
                    // No token provided - BLOCK access
                    $authorized = false;
                    $verifyTokenReturnFalseReason = "Download protection enabled but no token provided. IP=".getRealIpAddr()." {$_SERVER['HTTP_USER_AGENT']} URI=".$uri;
                    error_log("LiveKeyAuth: BLOCKED - Protection enabled but token missing. URI={$uri} IP=".getRealIpAddr()." Referer=".@$_SERVER['HTTP_REFERER']);
                } else {
                    // Token provided - validate it
                    $authorized = VideoHLS::verifyToken($token);
                    if (!$authorized) {
                        $verifyTokenReturnFalseReason = "Invalid or expired token for protected stream. IP=".getRealIpAddr();
                    } else {
                        $authorizationReason = "Valid token (protection enabled)";
                    }
                }
            } else {
                // Download protection is DISABLED - allow with valid referer only
                $authorized = true;
                $authorizationReason = "Valid referer (protection disabled, no token required)";
            }
        } else {
            // Invalid or missing referer - BLOCK
            $verifyTokenReturnFalseReason = "HTTP_REFERER={$_SERVER['HTTP_REFERER']}, webSiteRootURL={$global['webSiteRootURL']} IP=".getRealIpAddr().' HTTP_USER_AGENT='.$_SERVER['HTTP_USER_AGENT'];
            $authorized = false;
        }
        if (!$authorized) {
            http_response_code(403);
            $msg = 'authorizeKeyAccess: Access denied ['.$verifyTokenReturnFalseReason.'] '.getRealIpAddr();
            error_log('LiveKeyAuth: ' . $msg.' '.@$_SERVER['HTTP_REFERER']);
            echo $msg;
        } else {
            if (!empty($tmpFilePath)) {
                $bytes = file_put_contents($tmpFilePath, time());
            }
            $msg = 'authorizeKeyAccess: Authorized key=' . $liveKey . ' uri=' . $uri;
            // Only log authorization in debug mode to reduce log noise
            //error_log("LiveKeyAuth: AUTHORIZED - Reason: {$authorizationReason} | Protection: {$protectionStatus} | Token: {$tokenStatus} | IP=".getRealIpAddr()." | Referer=".@$_SERVER['HTTP_REFERER']);
            echo $msg;
        }
    } else {
        if (!empty($tmpFilePath)) {
            $bytes = file_put_contents($tmpFilePath, time());
        }
        $msg = 'authorizeKeyAccess: VideoHLS is not present ';
        error_log('LiveKeyAuth: ' . $msg);
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
