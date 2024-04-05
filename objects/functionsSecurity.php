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
            _error_log('isUntrustedRequest: ' . json_encode($logMsg), AVideoLog::$SECURITY);
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
 * @param string $allowedExtensions
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
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return $returnTrueIfNoUserAgent;
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
