<?php
/*
secure salt in PHP using standard characters and numbers.
This code will generate a 10 to 32-character string
*/
function _uniqid() {
    // Generate 16 bytes of random data
    $randomBytes = random_bytes(16);

    // Convert the binary data to a hexadecimal string
    $hex = bin2hex($randomBytes);

    // If you want a variable length output, you can truncate the MD5 hash
    // For example, to get a random length between 10 and 32 characters:
    $randomLength = rand(10, 32);
    $randomString = substr($hex, 0, $randomLength);

    return $randomString;
}


function adminSecurityCheck($force = false)
{
    if (empty($force)) {
        if (!empty($_SESSION['adminSecurityCheck'])) {
            return false;
        }
        if (!User::isAdmin()) {
            return false;
        }
    }
    global $global;
    $videosHtaccessFile = getVideosDir() . '.htaccess';
    $originalHtaccessFile = "{$global['systemRootPath']}objects/htaccess_for_videos.conf";
    $videosHtaccessFileVersion = getHtaccessForVideoVersion($videosHtaccessFile);
    $originalHtaccessFileVersion = getHtaccessForVideoVersion($originalHtaccessFile);
    //_error_log("adminSecurityCheck: videos.htaccess new version = {$originalHtaccessFileVersion} old version = {$videosHtaccessFileVersion}");
    if (version_compare($videosHtaccessFileVersion, $originalHtaccessFileVersion, '<')) {
        unlink($videosHtaccessFile);
        _error_log("adminSecurityCheck: file deleted new version = {$originalHtaccessFileVersion} old version = {$videosHtaccessFileVersion}");
    }
    if (!file_exists($videosHtaccessFile)) {
        $bytes = copy($originalHtaccessFile, $videosHtaccessFile);
        _error_log("adminSecurityCheck: file created {$videosHtaccessFile} {$bytes} bytes");
    }
    _session_start();
    $_SESSION['adminSecurityCheck'] = time();
    return true;
}


function forbiddenPageIfCannotEmbed($videos_id)
{
    global $customizedAdvanced, $advancedCustomUser, $global;
    if (empty($customizedAdvanced)) {
        $customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeAdvanced');
    }
    if (empty($advancedCustomUser)) {
        $advancedCustomUser = AVideoPlugin::getObjectDataIfEnabled('CustomizeUser');
    }
    if (!isAVideoMobileApp()) {
        if (!isSameDomain(@$_SERVER['HTTP_REFERER'], $global['webSiteRootURL'])) {
            if (!empty($advancedCustomUser->blockEmbedFromSharedVideos) && !CustomizeUser::canShareVideosFromVideo($videos_id)) {
                $reason = [];
                if (!empty($advancedCustomUser->blockEmbedFromSharedVideos)) {
                    error_log("forbiddenPageIfCannotEmbed: Embed is forbidden: \$advancedCustomUser->blockEmbedFromSharedVideos");
                    $reason[] = __('Admin block video sharing');
                }
                if (!CustomizeUser::canShareVideosFromVideo($videos_id)) {
                    error_log("forbiddenPageIfCannotEmbed: Embed is forbidden: !CustomizeUser::canShareVideosFromVideo({$videos_id})");
                    $reason[] = __('User block video sharing');
                }
                forbiddenPage("Embed is forbidden " . implode('<br>', $reason));
            }
        }

        $objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
        if (!empty($objSecure)) {
            $objSecure->verifyEmbedSecurity();
        }
    }
}

function forbidIfIsUntrustedRequest($logMsg = '', $approveAVideoUserAgent = true)
{
    global $global;
    if (isUntrustedRequest($logMsg, $approveAVideoUserAgent)) {
        forbiddenPage('Invalid Request ' . getRealIpAddr(), true);
    }
}

function isUntrustedRequest($logMsg = '', $approveAVideoUserAgent = true)
{
    global $global;
    if (!empty($global['bypassSameDomainCheck']) || isCommandLineInterface()) {
        return false;
    }
    if (!requestComesFromSameDomainAsMyAVideo()) {
        if ($approveAVideoUserAgent && isAVideoUserAgent()) {
            return false;
        } else {
            _error_log('isUntrustedRequest: ' . json_encode($logMsg). ' add $global[\'bypassSameDomainCheck\'] = 1 to by pass it' , AVideoLog::$SECURITY);
            return true;
        }
    }
    return false;
}

function forbidIfItIsNotMyUsersId($users_id, $logMsg = '')
{
    if (itIsNotMyUsersId($users_id)) {
        _error_log("forbidIfItIsNotMyUsersId: [{$users_id}]!=[" . User::getId() . "] {$logMsg}");
        forbiddenPage('It is not your user ' . getRealIpAddr(), true);
    }
}

function itIsNotMyUsersId($users_id)
{
    $users_id = intval($users_id);
    if (empty($users_id)) {
        return false;
    }
    if (!User::isLogged()) {
        return true;
    }
    return User::getId() != $users_id;
}

function requestComesFromSafePlace()
{
    return (requestComesFromSameDomainAsMyAVideo() || isAVideo());
}

/**
 * for security clean all non secure files from directory
 * @param string $dir
 * @param array $allowedExtensions
 * @return string
 */
function cleanDirectory($dir, $allowedExtensions = ['key', 'm3u8', 'ts', 'vtt', 'jpg', 'gif', 'mp3', 'webm', 'webp'])
{
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1) {
        return;
    }

    foreach ($ffs as $ff) {
        $current = $dir . '/' . $ff;
        if (is_dir($current)) {
            cleanDirectory($current, $allowedExtensions);
        }
        $path_parts = pathinfo($current);
        if (!empty($path_parts['extension']) && !in_array($path_parts['extension'], $allowedExtensions)) {
            _error_log("cleanDirectory($current) unlink line=".__LINE__);
            unlink($current);
        }
    }
}

if (!function_exists('xss_esc')) {

    function xss_esc($text)
    {
        if (empty($text)) {
            return "";
        }
        if (!is_string($text)) {
            if (is_array($text)) {
                foreach ($text as $key => $value) {
                    $text[$key] = xss_esc($value);
                }
            }
            return $text;
        }
        $result = @htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if (empty($result)) {
            $result = str_replace(['"', "'", "\\", "document.", "cookie"], ["", "", "", '', ''], strip_tags($text));
        }
        $result = str_ireplace(['&amp;amp;'], ['&amp;'], $result);
        return $result;
    }
}

function xss_esc_back($text)
{
    if (!isset($text)) {
        return '';
    }
    $text = htmlspecialchars_decode($text, ENT_QUOTES);
    $text = str_replace(['&amp;', '&#039;', "#039;"], [" ", "`", "`"], $text);
    return $text;
}

function is_ssl_certificate_valid($port = 443, $domain = '127.0.0.1', $timeout = 5)
{
    // Create a stream context with SSL options
    $stream_context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
            'capture_peer_cert' => true,
        ],
    ]);

    // Attempt to establish an SSL/TLS connection to the specified domain and port
    $socket = @stream_socket_client(
        "ssl://{$domain}:{$port}",
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $stream_context
    );

    // If the socket connection was successful, the SSL certificate is valid
    if ($socket) {
        fclose($socket);
        return true;
    }

    _error_log("is_ssl_certificate_valid($domain, $port) error ");
    // If the socket connection failed, the SSL certificate is not valid
    return false;
}

//detect search engine bots
function isBot($returnTrueIfNoUserAgent=true)
{
    global $_isBot;
    if (isCommandLineInterface()) {
        return false;
    }
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return $returnTrueIfNoUserAgent;
    }
    // Google IMA
    if (preg_match('/GoogleInteractiveMediaAds/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/imasdk/i', $_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }
    if (isAVideoEncoder()) {
        return false;
    }
    if (isset($_isBot)) {
        return $_isBot;
    }
    $_isBot = false;
    // User lowercase string for comparison.
    $user_agent = mb_strtolower($_SERVER['HTTP_USER_AGENT']);
    // A list of some common words used only for bots and crawlers.
    $bot_identifiers = [
        'bot',
        'yahoo',      // Yahoo bot has "yahoo" in user agent
        'baidu',
        'duckduckgo',
        'slurp',
        'crawler',
        'spider',
        'curl',       // Curl, although commonly used for many benign purposes, is often not a real user
        'facebo',    // Facebook bot
        'embedly',    // Embedly bot
        'quora',
        'outbrain',
        'pinterest',
        'vkshare',    // VK Share button user agent
        'w3c_validator',
        'whatsapp',   // Whatsapp link preview
        'fetch',
        'loader',
        'lighthouse', // Google Lighthouse
        'pingdom',    // Pingdom bot
        'gtmetrix',   // GTMetrix bot
        'dmbrowser',
        'dareboost',
        'http-client',
        'hello',
        'google',
        'Expanse'
    ];

    // See if one of the identifiers is in the UA string.
    foreach ($bot_identifiers as $identifier) {
        if (stripos($user_agent, $identifier) !== false) {
            $_isBot = true;
            break;
        }
    }
    return $_isBot;
}

function markDownToHTML($text) {
    $parsedown = new Parsedown();

    // Convert Markdown to HTML
    $html = $parsedown->text($text);

    // Convert new lines to <br> tags
    $html = nl2br($html);

    // Convert URLs to clickable links with target="_blank"
    $html = preg_replace(
        '/\b[^"\']https?:\/\/[^\s<]+/i',
        '<a href="$0" target="_blank" rel="noopener noreferrer">$0</a>',
        $html
    );

    // Add classes to images
    $html = preg_replace(
        '/<img([^>]+)>/i',
        '<img$1 class="img img-responsive">',
        $html
    );

    return $html;
}

function linkifyTimestamps($text) {
    // Regular expression to match timestamps (HH:MM:SS or MM:SS)
    $pattern = '/\b(?:\d{1,2}:)?\d{1,2}:\d{2}\b/';

    // Callback function to replace the timestamp with a clickable link
    $callback = function ($matches) {
        $timestamp = $matches[0];

        // Convert timestamp to seconds for Video.js
        $parts = array_reverse(explode(':', $timestamp));
        $seconds = 0;
        foreach ($parts as $index => $part) {
            $seconds += (int)$part * pow(60, $index);
        }

        // Return the clickable link
        return "<a href='javascript:void(0)' onclick=\"console.log('objects-functionsSecurity.php player.currentTime');player.currentTime($seconds);\">$timestamp</a>";
    };

    // Replace timestamps with links
    return preg_replace_callback($pattern, $callback, $text);
}

function getAToken()
{
    if (!empty($_SERVER['HTTP_ATOKEN'])) {
        return $_SERVER['HTTP_ATOKEN'];
    }
    if (!empty($_REQUEST['atoken'])) {
        return $_REQUEST['atoken'];
    }
    if (!empty($_REQUEST['token'])) {
        $string = decryptString($_REQUEST['token']);
        if (!empty($string)) {
            $obj = _json_decode($string);
            if (!empty($obj->atoken)) {
                return $obj->atoken;
            }
        }
    }
    if (preg_match('/atoken=\(([a-z0-9=]+)\)/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
        if (!empty($matches[1])) {
            return $matches[1];
        }
    }
    return '';
}


function forbiddenPage($message = '', $logMessage = false, $unlockPassword = '', $namespace = '', $pageCode = '403 Forbidden')
{
    global $global;
    if (!empty($unlockPassword)) {
        if (empty($namespace)) {
            $namespace = $_SERVER["SCRIPT_FILENAME"];
        }
        if (!empty($_REQUEST['unlockPassword'])) {
            if ($_REQUEST['unlockPassword'] == $unlockPassword) {
                _session_start();
                if (!isset($_SESSION['user']['forbiddenPage'])) {
                    $_SESSION['user']['forbiddenPage'] = [];
                }
                $_SESSION['user']['forbiddenPage'][$namespace] = $_REQUEST['unlockPassword'];
            }
        }
        if (!empty($_SESSION['user']['forbiddenPage'][$namespace]) && $unlockPassword === $_SESSION['user']['forbiddenPage'][$namespace]) {
            return true;
        }
    }
    $_REQUEST['403ErrorMsg'] = $message;
    if ($logMessage) {
        _error_log($message);
    }

    header('HTTP/1.0 ' . $pageCode);
    if (empty($unlockPassword) && isContentTypeJson()) {
        header("Content-Type: application/json");
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = $message;
        $obj->forbiddenPage = true;
        die(json_encode($obj));
    } else {
        if (empty($unlockPassword) && !User::isLogged()) {
            $message .= ', ' . __('please login');
            gotToLoginAndComeBackHere($message);
        } else {
            header("Content-Type: text/html");
            include $global['systemRootPath'] . 'view/forbiddenPage.php';
        }
    }
    exit;
}

function videoNotFound($message, $logMessage = false)
{
    //var_dump(debug_backtrace());exit;
    global $global;
    $_REQUEST['404ErrorMsg'] = $message;
    if ($logMessage) {
        _error_log($message);
    }
    include $global['systemRootPath'] . 'view/videoNotFound.php';
    exit;
}

function isForbidden()
{
    global $global;
    if (!empty($global['isForbidden'])) {
        return true;
    }
    return false;
}

function includeSecurityChecks() {
    global $isStandAlone;
    if(!empty($isStandAlone)){
        return false;
    }
    $directory = __DIR__.'/../plugin/';
    // Ensure the directory exists
    if (!is_dir($directory)) {
        die("Directory does not exist.");
    }

    // Scan the plugins directory
    $subdirs = scandir($directory);

    // Loop through each item in the plugins directory
    foreach ($subdirs as $subdir) {
        // Skip . and .. entries
        if ($subdir === '.' || $subdir === '..') {
            continue;
        }

        // Create the full path for each subdirectory
        $subdirPath = $directory . DIRECTORY_SEPARATOR . $subdir;

        // Check if it is a directory
        if (is_dir($subdirPath)) {
            // Path to securityCheck.php in the current subdirectory
            $securityCheckFile = $subdirPath . DIRECTORY_SEPARATOR . 'securityCheck.php';

            // Check if securityCheck.php exists in the subdirectory
            if (file_exists($securityCheckFile)) {
                // Include the securityCheck.php file
                include $securityCheckFile;
            }
        }
    }
}


/**
 * You can now configure it on the configuration.php
 * @return boolean
 */
function ddosProtection()
{
    global $global;
    $maxCon = empty($global['ddosMaxConnections']) ? 40 : $global['ddosMaxConnections'];
    $secondTimeout = empty($global['ddosSecondTimeout']) ? 5 : $global['ddosSecondTimeout'];
    $whitelistedFiles = [
        'playlists.json.php',
        'playlistsFromUserVideos.json.php',
        'image404.php',
        'downloadProtection.php',
    ];

    if (in_array(basename($_SERVER["SCRIPT_FILENAME"]), $whitelistedFiles)) {
        return true;
    }

    $time = time();
    if (!isset($_SESSION['bruteForceBlock']) || empty($_SESSION['bruteForceBlock'])) {
        $_SESSION['bruteForceBlock'] = [];
        $_SESSION['bruteForceBlock'][] = $time;
        return true;
    }

    $_SESSION['bruteForceBlock'][] = $time;

    //remove requests that are older than secondTimeout
    foreach ($_SESSION['bruteForceBlock'] as $key => $request_time) {
        if ($request_time < $time - $secondTimeout) {
            unset($_SESSION['bruteForceBlock'][$key]);
        }
    }

    //progressive timeout-> more requests, longer timeout
    $active_connections = count($_SESSION['bruteForceBlock']);
    $timeoutReal = ($active_connections / $maxCon) < 1 ? 0 : ($active_connections / $maxCon) * $secondTimeout;
    if ($timeoutReal) {
        _error_log("ddosProtection:: progressive timeout timeoutReal = ($timeoutReal) active_connections = ($active_connections) maxCon = ($maxCon) ", AVideoLog::$SECURITY);
    }
    sleep($timeoutReal);

    //with strict mode, penalize "attacker" with sleep() above, log and then die
    if ($global['strictDDOSprotection'] && $timeoutReal > 0) {
        $str = "bruteForceBlock: maxCon: $maxCon => secondTimeout: $secondTimeout | IP: " . getRealIpAddr() . " | count:" . count($_SESSION['bruteForceBlock']);
        _error_log($str);
        die($str);
    }

    return true;
}

function escapeshellcmdURL(string $command)
{
    return str_replace('\?', '?', escapeshellcmd($command));
}

function recreateCache(){
    return (!empty($_REQUEST['recreate']) && !isBot());
}

function getBearerToken()
{
    $headers = [];

    // 1. Try apache_request_headers() if available
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    }

    // 2. If still empty, try getallheaders()
    if (empty($headers) && function_exists('getallheaders')) {
        $headers = getallheaders();
    }

    // 3. If still empty, manually build headers from $_SERVER
    if (empty($headers)) {
        foreach ($_SERVER as $key => $value) {
            if (stripos($key, 'HTTP_') === 0) {
                // Convert HTTP_HEADER_NAME to Header-Name
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
    }

    // 4. Normalize and extract Authorization header
    foreach ($headers as $name => $value) {
        if (strcasecmp($name, 'Authorization') === 0 && preg_match('/Bearer\s(\S+)/', $value, $matches)) {
            return $matches[1]; // Return the token
        }
    }

    // 5. Final fallback: check $_SERVER directly
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        return $matches[1];
    }

    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
        return $matches[1];
    }

    return null; // Token not found
}


