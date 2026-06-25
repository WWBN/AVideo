<?php
require_once __DIR__ . '/../vendor/erusev/parsedown/Parsedown.php';
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

/**
 * Check if saltV2 exists, if not create it and add to configuration.php
 * This is a security upgrade for existing installations
 */
function checkAndCreateSaltV2() {
    global $global;

    // If saltV2 already exists, nothing to do
    if (!empty($global['saltV2'])) {
        return true;
    }

    // Generate a cryptographically secure saltV2
    $newSaltV2 = bin2hex(random_bytes(16));
    $global['saltV2'] = $newSaltV2;

    // Add saltV2 to configuration.php
    $configFile = $global['systemRootPath'] . 'videos/configuration.php';

    if (!file_exists($configFile)) {
        _error_log("checkAndCreateSaltV2: configuration.php not found at {$configFile}");
        return false;
    }

    $configContent = file_get_contents($configFile);

    // Check if saltV2 line already exists in file (but wasn't loaded)
    if (strpos($configContent, "\$global['saltV2']") !== false) {
        _error_log("checkAndCreateSaltV2: saltV2 already exists in configuration.php but wasn't loaded");
        return true;
    }

    // Find the position after the salt line to insert saltV2
    $saltPattern = "/\$global\['salt'\]\s*=\s*'[^']*';/";
    if (preg_match($saltPattern, $configContent, $matches, PREG_OFFSET_CAPTURE)) {
        $insertPosition = $matches[0][1] + strlen($matches[0][0]);
        $newLine = "\n\$global['saltV2'] = '{$newSaltV2}';";
        $configContent = substr_replace($configContent, $newLine, $insertPosition, 0);

        // Write back to configuration.php
        if (file_put_contents($configFile, $configContent)) {
            _error_log("checkAndCreateSaltV2: Successfully added saltV2 to configuration.php");
            return true;
        } else {
            _error_log("checkAndCreateSaltV2: Failed to write saltV2 to configuration.php");
            return false;
        }
    } else {
        _error_log("checkAndCreateSaltV2: Could not find salt line in configuration.php");
        return false;
    }
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
    $remoteAddr = getRemoteAddrFromServerArray($_SERVER);
    if (!empty($remoteAddr) && isLoopbackIP($remoteAddr) && isLoopbackIP(getRealIpAddr())) {
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
function cleanDirectory($dir, $allowedExtensions = ['key', 'm3u8', 'ts', 'vtt', 'jpg', 'gif', 'mp3', 'mp4', 'webm', 'webp'])
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
        'Expanse',
        'externalagent', // meta-externalagent (Facebook/Meta crawler)
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

class ParsedownSafeWithLinks extends Parsedown
{
    // Allow only <a> and <img> — all other raw HTML is escaped by the parent.
    // safeMode/markupEscaped must stay OFF so our overrides can run.

    private static function sanitizeATag($rawAttrs)
    {
        $href = '';
        $target = '';
        if (preg_match('/\bhref\s*=\s*"([^"]*)"|\bhref\s*=\s*\'([^\']*)\'/i', $rawAttrs, $m)) {
            $url = !empty($m[2]) ? $m[2] : $m[1];
            if (preg_match('/^(https?:\/\/|mailto:|\/|#)/i', $url)) {
                $href = ' href="' . htmlspecialchars($url, ENT_QUOTES) . '"';
            }
        }
        if (preg_match('/\btarget\s*=\s*"([^"]*)"|\btarget\s*=\s*\'([^\']*)\'/i', $rawAttrs, $m)) {
            $val = !empty($m[2]) ? $m[2] : $m[1];
            if (in_array($val, ['_blank', '_self', '_parent', '_top'], true)) {
                $target = ' target="' . $val . '"';
            }
        }
        return '<a' . $href . $target . '>';
    }

    private static function sanitizeImgTag($rawAttrs)
    {
        $src = '';
        $class = 'img img-responsive';
        $style = '';
        if (preg_match('/\bsrc\s*=\s*"([^"]*)"|\bsrc\s*=\s*\'([^\']*)\'/i', $rawAttrs, $m)) {
            $url = !empty($m[2]) ? $m[2] : $m[1];
            if (!preg_match('/^(javascript:|vbscript:|data:)/i', $url)) {
                $src = htmlspecialchars($url, ENT_QUOTES);
            }
        }
        if (empty($src)) {
            return null;
        }
        if (preg_match('/\bclass\s*=\s*"([^"]*)"|\bclass\s*=\s*\'([^\']*)\'/i', $rawAttrs, $m)) {
            $cv = preg_replace('/\s+/', ' ', trim(!empty($m[2]) ? $m[2] : $m[1]));
            $class = htmlspecialchars($cv, ENT_QUOTES);
        }
        if (preg_match('/\bstyle\s*=\s*"([^"]*)"|\bstyle\s*=\s*\'([^\']*)\'/i', $rawAttrs, $m)) {
            $sv = preg_replace('/\s+/', ' ', trim(!empty($m[2]) ? $m[2] : $m[1]));
            $sv = preg_replace('/expression\s*\(|javascript:/i', '', $sv);
            $style = ' style="' . htmlspecialchars($sv, ENT_QUOTES) . '"';
        }
        return '<img src="' . $src . '" class="' . $class . '"' . $style . '>';
    }

    protected function blockMarkup($Line)
    {
        $tag = '';
        if (preg_match('/^<(\w[\w-]*)(\s[^>]*)?>/', $Line['text'], $m)) {
            $tag = strtolower($m[1]);
        }
        if ($tag !== 'a' && $tag !== 'img') {
            // Escape everything else — mimic safeMode behaviour
            return null;
        }
        return parent::blockMarkup($Line);
    }

    protected function inlineLink($Excerpt)
    {
        $Link = parent::inlineLink($Excerpt);

        if ($Link === null) {
            return null;
        }

        $href = isset($Link['element']['attributes']['href']) ? $Link['element']['attributes']['href'] : '';

        // Apply the same whitelist as sanitizeATag: http(s), mailto, relative paths, page anchors.
        // Anything else (javascript:, vbscript:, data:, ...) is stripped.
        if ($href !== '' && !preg_match('/^(https?:\/\/|mailto:|\/|#)/i', $href)) {
            $Link['element']['attributes']['href'] = '';
        }

        return $Link;
    }

    protected function inlineUrlTag($Excerpt)
    {
        $Link = parent::inlineUrlTag($Excerpt);

        if ($Link === null) {
            return null;
        }

        $href = isset($Link['element']['attributes']['href']) ? $Link['element']['attributes']['href'] : '';

        // Auto-link syntax <url> — apply the same protocol whitelist.
        // The base regex requires scheme:// so javascript:// is the realistic bypass vector.
        if ($href !== '' && !preg_match('/^https?:\/\//i', $href)) {
            $Link['element']['attributes']['href'] = '';
        }

        return $Link;
    }

    protected function inlineMarkup($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') === false) {
            return null;
        }

        // Closing </a>
        if (preg_match('/^<\/a[ ]*>/i', $Excerpt['text'], $m)) {
            return ['element' => ['rawHtml' => '</a>'], 'extent' => strlen($m[0])];
        }

        // <a ...>
        if (preg_match('/^<a(\s[^>]*)>/i', $Excerpt['text'], $m)) {
            return ['element' => ['rawHtml' => self::sanitizeATag($m[1])], 'extent' => strlen($m[0])];
        }

        // <img ...>
        if (preg_match('/^<img(\s[^>]*)\s*\/?>/i', $Excerpt['text'], $m)) {
            $tag = self::sanitizeImgTag($m[1]);
            if ($tag === null) {
                return null;
            }
            return ['element' => ['rawHtml' => $tag], 'extent' => strlen($m[0])];
        }

        // All other inline HTML: escape it
        return null;
    }
}

function markDownToHTML($text) {
    $parsedown = new ParsedownSafeWithLinks();
    // safeMode OFF so our overrides in blockMarkup/inlineMarkup control what passes
    $parsedown->setSafeMode(false);
    $parsedown->setMarkupEscaped(false);

    // Convert Markdown to HTML; <a> and <img> are sanitized, everything else is escaped
    $html = $parsedown->text($text);

    // Convert new lines to <br> tags
    $html = nl2br($html);

    // Convert bare URLs to clickable links with target="_blank"
    $html = preg_replace_callback(
        '/\b[^"\'\s<]https?:\/\/[^\s<"\']+/i',
        function ($matches) {
            $url = $matches[0];
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            return '<a href="' . $escapedUrl . '" target="_blank" rel="noopener noreferrer">' . $escapedUrl . '</a>';
        },
        $html
    );

    // Add classes to images produced by markdown image syntax ![alt](url)
    // Only add class to <img> tags that do not already have a class attribute
    $html = preg_replace(
        '/<img(?![^>]*\bclass\s*=)([^>]+)>/i',
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
            // Sanitize user input to match the same sanitization used when storing the password
            $sanitizedInput = preg_replace('/[^0-9a-z]/i', '', $_REQUEST['unlockPassword']);
            if ($sanitizedInput == $unlockPassword) {
                _session_start();
                if (!isset($_SESSION['user']['forbiddenPage'])) {
                    $_SESSION['user']['forbiddenPage'] = [];
                }
                $_SESSION['user']['forbiddenPage'][$namespace] = $sanitizedInput;
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
        _session_write_close();
    }
    sleep($timeoutReal);

    //with strict mode, penalize "attacker" with sleep() above, log and then die
    if ($global['strictDDOSprotection'] && $timeoutReal > 0) {
        $str = "bruteForceBlock: maxCon: $maxCon => secondTimeout: $secondTimeout | IP: " . getRealIpAddr() . " | count:" . count($_SESSION['bruteForceBlock']);
        _error_log($str);
        die($str);
    }

    if ($timeoutReal) {
        _session_start();
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

/**
 * Enforce a rate limit for the current endpoint.
 *
 * Kills the request with HTTP 429 + JSON body if the caller has exceeded
 * the allowed number of attempts within the time window.
 *
 * Usage examples:
 *   enforceRateLimit();                        // 20 req / 5 min, key = script + IP
 *   enforceRateLimit('login', 5, 60);          // 5 req / 60 s, explicit name
 *   enforceRateLimit('encryptPass', 20, 300);  // explicit name + window
 *
 * @param string $operation  Logical name for the operation. Defaults to the
 *                           basename of the current script (auto-derived).
 * @param int    $maxAttempts Maximum requests allowed within $timeWindow.
 * @param int    $timeWindow  Window duration in seconds.
 */
function enforceRateLimit(string $operation = '', int $maxAttempts = 20, int $timeWindow = 300): void
{
    if (isCommandLineInterface()) {
        return;
    }
    if ($operation === '') {
        $operation = basename($_SERVER['SCRIPT_FILENAME'] ?? 'unknown');
    }
    $key      = 'ratelimit_' . $operation . '_' . getRealIpAddr();
    $attempts = intval(ObjectYPT::getCacheGlobal($key, $timeWindow));
    if ($attempts >= $maxAttempts) {
        _error_log("enforceRateLimit blocked operation={$operation} ip=" . getRealIpAddr() . " attempts={$attempts} window={$timeWindow}", AVideoLog::$SECURITY);
        http_response_code(429);
        header('Content-Type: application/json');
        $obj        = new stdClass();
        $obj->error = true;
        $obj->msg   = __('Too many requests. Try again later.');
        die(json_encode($obj));
    }
    ObjectYPT::setCacheGlobal($key, $attempts + 1);
}

/**
 * Automatic CSRF guard invoked once by include_config.php for every POST
 * to a *.json.php endpoint.
 *
 * Bypass options (pick the one that fits your use-case):
 *
 *  1. $global['bypassSameDomainCheck'] = 1  — existing flag; disables all
 *     same-domain checks globally (encoder-to-encoder calls, etc.).
 *     Must be set BEFORE require configuration.php.
 *
 *  2. $global['skipAutoCSRFCheck'] = true  — disables only this auto-guard
 *     for the current request.  Must be set BEFORE require configuration.php.
 *
 *  3. $global['csrfBypassFiles'][] = 'myfile.json.php'  — persistent per-file
 *     opt-out by exact basename; add in videos/configuration.php.
 *
 *  4. $global['csrfBypassPatterns'][] = 'myprefix*.json.php'  — fnmatch-style
 *     pattern opt-out; add in videos/configuration.php.
 *
 * Mobile apps:  automatically pass through because isAVideoUserAgent() detects
 * the AVideoMobileApp User-Agent inside forbidIfIsUntrustedRequest().  No
 * whitelist entry is needed for mobile-facing endpoints.
 *
 * @param string $baseName   basename of the currently executing script
 * @param string $scriptPath full path of the currently executing script
 */
function autoCSRFGuard($baseName, $scriptPath = '')
{
    global $global;

    // Respect existing full bypass (encoder callbacks, CLI, etc.)
    if (!empty($global['bypassSameDomainCheck']) || isCommandLineInterface()) {
        return;
    }

    // Per-request opt-out — must be set before configuration.php loads
    if (!empty($global['skipAutoCSRFCheck'])) {
        return;
    }

    // ── Pattern-based built-in bypasses ──────────────────────────────────────
    //
    // ipn*.json.php   — payment-processor IPN callbacks (Mercado Pago, etc.)
    // webhook*.json.php — payment/service webhooks (PayPal, Stripe, etc.)
    //   These are called by external servers; no browser Origin is present.
    //
    // plugin/API/*    — the REST API plugin is designed for external callers;
    //   get.json.php / set.json.php already set bypassSameDomainCheck before
    //   require, so they never reach here.  The remaining *.ffmpeg.json.php
    //   files are also part of the external API surface.
    if (
        fnmatch('ipn*.json.php',     $baseName) ||
        fnmatch('webhook*.json.php', $baseName) ||
        (
            $scriptPath !== '' &&
            strpos(strtolower(str_replace('\\', '/', $scriptPath)), '/plugin/api/') !== false
        )
    ) {
        return;
    }

    // ── Exact-name built-in bypass list ──────────────────────────────────────
    // Groups:
    //   auth/signup  — accept calls from mobile apps & external clients
    //   public reads — use POST params for filtering, but mutate nothing
    //   public write — like/view/subscribe actions open to all users
    //   encoder      — authenticated via video-hash token, not session
    static $builtinBypass = [
        // Auth & account management
        'login.json.php',
        'userCreate.json.php',
        'userRecoverPassSave.json.php',
        // Public write actions
        'sendEmail.json.php',
        'subscribe.json.php',
        'subscribeNotify.json.php',
        'like.json.php',
        'videoAddViewCount.json.php',
        // Read-only endpoints that accept POST params
        'categories.json.php',
        'comments.json.php',
        'users.json.php',
        'videos.json.php',
        'videosAndroid.json.php',
        'plugins.json.php',
        'playlistsPublic.json.php',
        'playlistsVideos.json.php',
        'playlistsFromUserVideos.json.php',
        'mention.json.php',
        'notifications.json.php',
        'listFiles.json.php',
        // Encoder upload callbacks (auth via video-hash, not session)
        'aVideoEncoder.json.php',
        'aVideoEncoderLog.json.php',
        'aVideoEncoderNotifyIsDone.json.php',
        'aVideoEncoderReceiveImage.json.php',
        'aVideoQueueEncoder.json.php',
        // Live recording callbacks (cross-origin from recorder agent)
        'recordStart.json.php',
        'recordStop.json.php',
        // Bulk import tool — intentional cross-origin / CLI use
        'import_spreadsheet_videos.json.php',
    ];

    if (in_array($baseName, $builtinBypass, true)) {
        return;
    }

    // ── Operator-defined bypass lists ─────────────────────────────────────────
    // Exact basenames:  $global['csrfBypassFiles'] = ['myWebhook.json.php'];
    if (
        !empty($global['csrfBypassFiles']) &&
        is_array($global['csrfBypassFiles']) &&
        in_array($baseName, $global['csrfBypassFiles'], true)
    ) {
        return;
    }

    // fnmatch patterns:  $global['csrfBypassPatterns'] = ['payment*.json.php'];
    if (!empty($global['csrfBypassPatterns']) && is_array($global['csrfBypassPatterns'])) {
        foreach ($global['csrfBypassPatterns'] as $pattern) {
            if (fnmatch($pattern, $baseName)) {
                return;
            }
        }
    }

    forbidIfIsUntrustedRequest("autoCSRF::{$baseName}");
}
