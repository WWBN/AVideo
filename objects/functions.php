<?php
$mysql_connect_was_closed = 1;
$mysql_connect_is_persistent = false;

if (!isset($global) || !is_array($global)) {
    $global = [];
}

/**
 * str_starts_with wasn't introduced until PHP8. Polyfill provided in order to
 * maintain compatibility between AVideo and older PHP versions.
 * @link https://www.php.net/str_starts_with
 */
if (!function_exists('str_starts_with')) {

    function str_starts_with(string $Haystack, string $Needle): bool
    {
        return substr($Haystack, 0, strlen($Needle)) === $Needle;
    }
}


// Make sure SecureVideosDirectory will be the first
function cmpPlugin($a, $b)
{
    $topOrder = ['SecureVideosDirectory', 'GoogleAds_IMA', 'Gift', 'Subscription', 'PayPerView', 'FansSubscriptions'];
    $bottomOrder = ['PlayerSkins'];

    $aTopIndex = array_search($a['name'], $topOrder);
    $bTopIndex = array_search($b['name'], $topOrder);

    $aBottomIndex = array_search($a['name'], $bottomOrder);
    $bBottomIndex = array_search($b['name'], $bottomOrder);

    // Both items in the top order array
    if ($aTopIndex !== false && $bTopIndex !== false) {
        return $aTopIndex - $bTopIndex;
    }

    // One of the items is in the top order array
    if ($aTopIndex !== false) {
        return -1;
    }
    if ($bTopIndex !== false) {
        return 1;
    }

    // Both items in the bottom order array
    if ($aBottomIndex !== false && $bBottomIndex !== false) {
        return $aBottomIndex - $bBottomIndex;
    }

    // One of the items is in the bottom order array
    if ($aBottomIndex !== false) {
        return 1;
    }
    if ($bBottomIndex !== false) {
        return -1;
    }

    // Neither item in any order array
    return 0;
}

function isApache()
{
    return (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false);
}

function isPHP($version = "'7.3.0'")
{
    return (version_compare(PHP_VERSION, $version) >= 0);
}

function modEnabled($mod_name)
{
    if (!function_exists('apache_get_modules')) {
        _ob_start();
        phpinfo(INFO_MODULES);
        $contents = ob_get_contents();
        _ob_end_clean();
        return (strpos($contents, 'mod_' . $mod_name) !== false);
    }
    return in_array('mod_' . $mod_name, apache_get_modules());
}

function modRewriteEnabled()
{
    return modEnabled("rewrite");
}

function modAliasEnabled()
{
    return modEnabled("alias");
}

function isFFMPEG()
{
    return trim(shell_exec('which ffmpeg'));
}

function isUnzip()
{
    return trim(shell_exec('which unzip'));
}

function isExifToo()
{
    return trim(shell_exec('which exiftool'));
}

function isAPPInstalled($appName)
{
    $appName = preg_replace('/[^a-z0-9_-]/i', '', $appName);
    return trim(shell_exec("which {$appName}"));
}

function getPathToApplication()
{
    return str_replace(['install/index.php', 'view/configurations.php'], '', $_SERVER['SCRIPT_FILENAME']);
}

function getURLToApplication()
{
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = explode("install/index.php", $url);
    return $url[0];
}

//max_execution_time = 7200
function check_max_execution_time()
{
    $max_size = ini_get('max_execution_time');
    $recomended_size = 7200;
    return ($recomended_size <= $max_size);
}

//post_max_size = 100M
function check_post_max_size()
{
    $max_size = parse_size(ini_get('post_max_size'));
    $recomended_size = parse_size('100M');
    return ($recomended_size <= $max_size);
}

//upload_max_filesize = 100M
function check_upload_max_filesize()
{
    $max_size = parse_size(ini_get('upload_max_filesize'));
    $recomended_size = parse_size('100M');
    return ($recomended_size <= $max_size);
}

//memory_limit = 100M
function check_memory_limit()
{
    $max_size = parse_size(ini_get('memory_limit'));
    $recomended_size = parse_size('512M');
    return ($recomended_size <= $max_size);
}

function getRealIpAddr()
{
    $ip = "127.0.0.1";

    if (isCommandLineInterface()) {
        return $ip;
    }

    $headers = [
        'HTTP_X_REAL_IP',
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

function cleanString($text)
{
    if (empty($text)) {
        return '';
    }
    if (!is_string($text)) {
        return $text;
    }
    $utf8 = [
        '/[áaâaaäą]/u' => 'a',
        '/[ÁAÂAÄĄ]/u' => 'A',
        '/[ÍIÎI]/u' => 'I',
        '/[íiîi]/u' => 'i',
        '/[éeeëę]/u' => 'e',
        '/[ÉEEËĘ]/u' => 'E',
        '/[óoôooö]/u' => 'o',
        '/[ÓOÔOÖ]/u' => 'O',
        '/[úuuü]/u' => 'u',
        '/[ÚUUÜ]/u' => 'U',
        '/[çć]/u' => 'c',
        '/[ÇĆ]/u' => 'C',
        '/[nń]/u' => 'n',
        '/[NŃ]/u' => 'N',
        '/[żź]/u' => 'z',
        '/[ŻŹ]/u' => 'Z',
        '/ł/' => 'l',
        '/Ł/' => 'L',
        '/ś/' => 's',
        '/Ś/' => 'S',
        '/–/' => '-', // UTF-8 hyphen to 'normal' hyphen
        '/[’‘‹›‚]/u' => ' ', // Literally a single quote
        '/[“”«»„]/u' => ' ', // Double quote
        '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        '/Є/' => 'YE',
        '/І/' => 'I',
        '/Ѓ/' => 'G',
        '/і/' => 'i',
        '/№/' => '#',
        '/є/' => 'ye',
        '/ѓ/' => 'g',
        '/А/' => 'A',
        '/Б/' => 'B',
        '/В/' => 'V',
        '/Г/' => 'G',
        '/Д/' => 'D',
        '/Е/' => 'E',
        '/Ё/' => 'YO',
        '/Ж/' => 'ZH',
        '/З/' => 'Z',
        '/И/' => 'I',
        '/Й/' => 'J',
        '/К/' => 'K',
        '/Л/' => 'L',
        '/М/' => 'M',
        '/Н/' => 'N',
        '/О/' => 'O',
        '/П/' => 'P',
        '/Р/' => 'R',
        '/С/' => 'S',
        '/Т/' => 'T',
        '/У/' => 'U',
        '/Ф/' => 'F',
        '/Х/' => 'H',
        '/Ц/' => 'C',
        '/Ч/' => 'CH',
        '/Ш/' => 'SH',
        '/Щ/' => 'SHH',
        '/Ъ/' => '',
        '/Ы/' => 'Y',
        '/Ь/' => '',
        '/Э/' => 'E',
        '/Ю/' => 'YU',
        '/Я/' => 'YA',
        '/а/' => 'a',
        '/б/' => 'b',
        '/в/' => 'v',
        '/г/' => 'g',
        '/д/' => 'd',
        '/е/' => 'e',
        '/ё/' => 'yo',
        '/ж/' => 'zh',
        '/з/' => 'z',
        '/и/' => 'i',
        '/й/' => 'j',
        '/к/' => 'k',
        '/л/' => 'l',
        '/м/' => 'm',
        '/н/' => 'n',
        '/о/' => 'o',
        '/п/' => 'p',
        '/р/' => 'r',
        '/с/' => 's',
        '/т/' => 't',
        '/у/' => 'u',
        '/ф/' => 'f',
        '/х/' => 'h',
        '/ц/' => 'c',
        '/ч/' => 'ch',
        '/ш/' => 'sh',
        '/щ/' => 'shh',
        '/ъ/' => '',
        '/ы/' => 'y',
        '/ь/' => '',
        '/э/' => 'e',
        '/ю/' => 'yu',
        '/я/' => 'ya',
        '/—/' => '-',
        '/«/' => '',
        '/»/' => '',
        '/…/' => '',
    ];
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

/**
 * Sanitizes a string by removing HTML tags and special characters.
 *
 * @param string $text The text to sanitize.
 * @param bool $strict (optional) Whether to apply strict sanitization. Defaults to false.
 * @return string The sanitized string.
 */
function safeString($text, $strict = false, $try = 0)
{
    if (empty($text)) {
        return '';
    }

    $originalText = $text;
    $text = strip_tags($text);
    $text = str_replace(['&amp;', '&lt;', '&gt;', '&zwnj;'], ['', '', '', ''], $text);
    $text = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '', $text);
    $text = preg_replace('/(&#x*[0-9A-F]+);*/iu', '', $text);
    $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');

    if ($strict) {
        $text = filter_var($text, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        //$text = cleanURLName($text);
    }

    $text = trim($text);

    if (empty($try) && empty($text) && function_exists('mb_convert_encoding')) {
        $originalText2 = preg_replace('/[^\PC\s]/u', '', $originalText);
        if (empty($originalText2)) {
            $originalText2 = mb_convert_encoding($originalText, 'UTF-8', 'auto');
            $originalText2 = preg_replace('/[^\PC\s]/u', '', $originalText2);
        }
        if (!empty($originalText2)) {
            $originalText = $originalText2;
        }
        // Remove leading and trailing whitespace
        $originalText = trim($originalText);
        return safeString(mb_convert_encoding($originalText, 'UTF-8'), $strict, 1);
    }

    return $text;
}

function cleanURLName($name, $replaceChar = '-')
{
    if (!is_string($name)) {
        return $name;
    }
    $name = preg_replace('/[!#$&\'()*+,\\/:;=?@[\\]%"\/\\\\ ]+/', $replaceChar, trim(mb_strtolower(cleanString($name))));
    return trim(preg_replace('/[\x00-\x1F\x7F\xD7\xE0]/u', $replaceChar, $name), $replaceChar);
}

/**
 * @brief return true if running in CLI, false otherwise
 * if is set $_GET['ignoreCommandLineInterface'] will return false
 * @return boolean
 */
function isCommandLineInterface()
{
    return (empty($_GET['ignoreCommandLineInterface']) && php_sapi_name() === 'cli');
}

/**
 * @brief show status message as text (CLI) or JSON-encoded array (web)
 *
 * @param array $statusarray associative array with type/message pairs
 * @return string
 */
function status($statusarray)
{
    if (isCommandLineInterface()) {
        foreach ($statusarray as $status => $message) {
            echo $status . ":" . $message . "\n";
        }
    } else {
        echo json_encode(array_map(function ($text) {
            return nl2br($text);
        }, $statusarray));
    }
}

/**
 * @brief show status message and die
 *
 * @param array $statusarray associative array with type/message pairs
 */
function croak($statusarray)
{
    status($statusarray);
    die;
}

function getSecondsTotalVideosLength()
{
    $configFile = dirname(__FILE__) . '/../videos/configuration.php';
    require_once $configFile;
    global $global;

    if (!User::isLogged()) {
        return 0;
    }
    $sql = "SELECT * FROM videos v ";
    $formats = '';
    $values = [];
    if (!User::isAdmin()) {
        $id = User::getId();
        $sql .= " WHERE users_id = ? ";
        $formats = "i";
        $values = [$id];
    }

    $res = sqlDAL::readSql($sql, $formats, $values);
    $fullData = sqlDAL::fetchAllAssoc($res);
    sqlDAL::close($res);
    $seconds = 0;
    foreach ($fullData as $row) {
        $seconds += parseDurationToSeconds($row['duration']);
    }
    return $seconds;
}

function getMinutesTotalVideosLength()
{
    $seconds = getSecondsTotalVideosLength();
    return floor($seconds / 60);
}

/**
 * Converts a duration in seconds to a formatted time string (hh:mm:ss).
 *
 * @param int|float|string $seconds The duration in seconds to convert.
 * @return string The formatted time string.
 */
function secondsToVideoTime($seconds)
{
    if (!is_numeric($seconds)) {
        return (string) $seconds;
    }

    $seconds = round($seconds);
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}

function parseSecondsToDuration($seconds)
{
    return secondsToVideoTime($seconds);
}

/**
 * Converts a duration string to the corresponding number of seconds.
 *
 * @param int|string $str The duration string to parse, in the format "HH:MM:SS".
 * @return int The duration in seconds.
 */
function parseDurationToSeconds($str)
{
    if ($str == "00:00:00") {
        return 0;
    }
    if (is_numeric($str)) {
        return intval($str);
    }
    if (empty($str)) {
        return 0;
    }
    $durationParts = explode(":", $str);
    if (empty($durationParts[1]) || $durationParts[0] == "EE") {
        return 0;
    }
    if (empty($durationParts[2])) {
        $durationParts[2] = 0;
    }
    $minutes = (intval($durationParts[0]) * 60) + intval($durationParts[1]);
    return intval($durationParts[2]) + ($minutes * 60);
}

function durationToSeconds($str)
{
    return parseDurationToSeconds($str);
}

function secondsToDuration($seconds)
{
    return parseSecondsToDuration($seconds);
}

/**
 * Returns an array with the unique values from the input array, ignoring case differences.
 *
 * @param array $array The input array.
 * @return array The array with unique values.
 */
function array_iunique(array $array): array
{
    return array_intersect_key($array, array_unique(array_map('mb_strtolower', $array)));
}

function fixURL($url)
{
    return str_replace(array('&amp%3B', '&amp;'), array('&', '&'), $url);
}

// Helper function to construct the final URL with base parameters
function appendParams($url, $baseParams)
{
    return $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . $baseParams;
}

function parseVideos($videoString = null, $autoplay = 0, $loop = 0, $mute = 0, $showinfo = 0, $controls = 1, $time = 0, $objectFit = "")
{
    global $global;
    if (!empty($videoString)) {
        $videoString = fixURL($videoString);
    }

    // Define the base parameters to be appended to the URL
    $baseParams = "modestbranding=1&showinfo={$showinfo}&autoplay={$autoplay}&controls={$controls}&loop={$loop}&mute={$mute}&t={$time}&objectFit={$objectFit}";

    // Process YouTube embedded URL
    if (strpos($videoString, 'youtube.com/embed') !== false) {
        return appendParams($videoString, $baseParams);
    }

    // Extract the video URL from the iframe if necessary
    if (strpos($videoString, 'iframe') !== false) {
        $anchorRegex = '/src="(.*)?"/isU';
        $results = [];
        if (preg_match($anchorRegex, $videoString, $results)) {
            $link = trim($results[1]);
        }
    } else {
        $link = $videoString;
    }

    // Process YouTube links
    if (stripos($link, 'embed') !== false || strpos($link, 'youtube.com') !== false || strpos($link, 'youtu.be') !== false) {
        preg_match('/(?:youtube\.com\/.*v=|youtu\.be\/|youtube\.com\/embed\/)([^&?\/]+)/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return appendParams('//www.youtube.com/embed/' . $id, $baseParams);
        }
    }

    // Process Vimeo links
    if (strpos($link, 'vimeo.com') !== false) {
        preg_match('/vimeo\.com\/(?:channels\/[a-z0-9-]+\/|video\/|)(\d+)/i', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//player.vimeo.com/video/' . $id;
        }
    }

    // Process Dailymotion links
    if (strpos($link, 'dailymotion.com') !== false) {
        preg_match('/dailymotion.com\/video\/([a-zA-Z0-9_]+)/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//www.dailymotion.com/embed/video/' . $id;
        }
    }

    // Process Metacafe links
    if (strpos($link, 'metacafe.com') !== false) {
        preg_match('/metacafe.com\/watch\/([a-zA-Z0-9_\/-]+)/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//www.metacafe.com/embed/' . $id;
        }
    }

    // Process Vid.me links
    if (strpos($link, 'vid.me') !== false) {
        preg_match('/vid.me\/([a-zA-Z0-9_-]+)$/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//vid.me/e/' . $id;
        }
    }

    // Process Rutube links
    if (strpos($link, 'rutube.ru') !== false) {
        preg_match('/rutube.ru\/video\/([a-zA-Z0-9_-]+)\//', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//rutube.ru/play/embed/' . $id;
        }
    }

    // Process OK.ru links
    if (strpos($link, 'ok.ru') !== false) {
        preg_match('/ok.ru\/video\/([a-zA-Z0-9_-]+)$/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//ok.ru/videoembed/' . $id;
        }
    }

    // Process Streamable links
    if (strpos($link, 'streamable.com') !== false) {
        preg_match('/streamable.com\/([a-zA-Z0-9_-]+)$/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return '//streamable.com/s/' . $id;
        }
    }

    // Process Twitch.tv links
    if (strpos($link, 'twitch.tv') !== false) {
        preg_match('/twitch.tv\/videos\/([a-zA-Z0-9_-]+)|twitch.tv\/[a-zA-Z0-9_-]+\/v\/([a-zA-Z0-9_-]+)|twitch.tv\/([a-zA-Z0-9_-]+)$/', $link, $matches);
        if (!empty($matches[1])) {
            return '//player.twitch.tv/?video=' . $matches[1] . '&parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        } elseif (!empty($matches[2])) {
            return '//player.twitch.tv/?video=' . $matches[2] . '&parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        } elseif (!empty($matches[3])) {
            return '//player.twitch.tv/?channel=' . $matches[3] . '&parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        }
    }

    // Process Bitchute links
    if (strpos($link, 'bitchute.com/video') !== false) {
        preg_match('/bitchute.com\/video\/([^\/]+)/', $link, $matches);
        if (!empty($matches[1])) {
            $id = $matches[1];
            return 'https://www.bitchute.com/embed/' . $id . '/?parent=' . parse_url($global['webSiteRootURL'], PHP_URL_HOST);
        }
    }

    // Process AVideo links
    if (strpos($link, '/evideo/') !== false) {
        preg_match('/(http.+)\/evideo\/([a-zA-Z0-9_-]+)/i', $link, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
            $site = $matches[1];
            $id = $matches[2];
            return $site . '/evideoEmbed/' . $id . "?autoplay={$autoplay}&controls={$controls}&loop={$loop}&mute={$mute}&t={$time}";
        }
    }

    if (strpos($link, '/video/') !== false) {
        preg_match('/(http.+)\/video\/([a-zA-Z0-9_-]+)/i', $link, $matches);
        if (!empty($matches[1]) && !empty($matches[2])) {
            $site = $matches[1];
            $id = $matches[2];
            return $site . '/videoEmbed/' . $id . "?autoplay={$autoplay}&controls={$controls}&loop={$loop}&mute={$mute}&t={$time}";
        }
    }

    // Process Facebook Watch links
    if (strpos($link, '/fb.watch/') !== false) {
        preg_match('/fb.watch\/([^\/]+)/', $link, $matches);
        if (!empty($matches[1])) {
            $url = 'https://www.facebook.com/plugins/video.php';
            $url = addQueryStringParameter($url, 'href', $link);
            $url = addQueryStringParameter($url, 'show_text', $showinfo ? 'true' : 'false');
            $url = addQueryStringParameter($url, 't', $time);
            return $url;
        }
    }

    // Process Voe.sx links
    if (strpos($link, 'voe.sx') !== false) {
        preg_match('/voe.sx\/(?:e\/)?([a-zA-Z0-9]+)/', $link, $matches);
        if (!empty($matches[1])) {
            return 'https://voe.sx/e/' . $matches[1];
        }
    }

    // Process Streamvid.net links
    if (strpos($link, 'streamvid.net') !== false) {
        preg_match('/streamvid.net\/(?:embed-)?([a-zA-Z0-9]+)/', $link, $matches);
        if (!empty($matches[1])) {
            return 'https://streamvid.net/embed-' . $matches[1];
        }
    }

    // Process Streamtape.to links
    if (strpos($link, 'streamtape.to') !== false) {
        preg_match('/streamtape.to\/(?:v|e)\/([a-zA-Z0-9]+)/', $link, $matches);
        if (!empty($matches[1])) {
            return 'https://streamtape.com/e/' . $matches[1];
        }
    }

    // Process Vid-guard.com links
    if (strpos($link, 'vid-guard.com') !== false) {
        preg_match('/vembed.net\/(?:e\/)?([a-zA-Z0-9]+)/', $link, $matches);
        if (!empty($matches[1])) {
            return 'https://vembed.net/e/' . $matches[1];
        }
    }

    // If no known video platform is matched, process the URL query parameters
    $url_parsed = parse_url($videoString);
    if (empty($url_parsed['query'])) {
        return $videoString;
    }
    parse_str($url_parsed['query'], $new_qs_parsed);
    parse_str($baseParams, $other_qs_parsed);
    $final_query_string_array = array_merge($new_qs_parsed, $other_qs_parsed);
    $final_query_string = http_build_query($final_query_string_array);
    $scheme = empty($url_parsed['scheme']) ? '' : "{$url_parsed['scheme']}:";
    $new_url = $scheme . '//' . $url_parsed['host'] . $url_parsed['path'] . '?' . $final_query_string;

    return $new_url;
}


$canUseCDN = [];

function canUseCDN($videos_id)
{
    if (empty($videos_id)) {
        return false;
    }
    global $global, $canUseCDN;
    if (!isset($canUseCDN[$videos_id])) {
        $canUseCDN[$videos_id] = true;
        $pvr360 = AVideoPlugin::isEnabledByName('VR360');
        // if the VR360 is enabled you can not use the CDN, it fail to load the GL
        if ($pvr360) {
            $isVR360Enabled = VideosVR360::isVR360Enabled($videos_id);
            if ($isVR360Enabled) {
                $canUseCDN[$videos_id] = false;
            }
        }
    }
    return $canUseCDN[$videos_id];
}

function clearVideosURL($fileName = "")
{
    global $global;
    $path = getCacheDir() . "getVideosURL/";
    if (empty($path)) {
        rrmdir($path);
    } else {
        $cacheFilename = "{$path}{$fileName}.cache";
        @unlink($cacheFilename);
    }
}

function maxLifetime()
{
    global $maxLifetime;
    if (!isset($maxLifetime)) {
        $aws_s3 = AVideoPlugin::getObjectDataIfEnabled('AWS_S3');
        $bb_b2 = AVideoPlugin::getObjectDataIfEnabled('Blackblaze_B2');
        $secure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
        $maxLifetime = 0;
        if (!empty($aws_s3) && empty($aws_s3->makeMyFilesPublicRead) && !empty($aws_s3->presignedRequestSecondsTimeout) && (empty($maxLifetime) || $aws_s3->presignedRequestSecondsTimeout < $maxLifetime)) {
            $maxLifetime = $aws_s3->presignedRequestSecondsTimeout;
            //_error_log("maxLifetime: AWS_S3 = {$maxLifetime}");
        }
        if (!empty($bb_b2) && empty($bb_b2->usePublicBucket) && !empty($bb_b2->presignedRequestSecondsTimeout) && (empty($maxLifetime) || $bb_b2->presignedRequestSecondsTimeout < $maxLifetime)) {
            $maxLifetime = $bb_b2->presignedRequestSecondsTimeout;
            //_error_log("maxLifetime: B2 = {$maxLifetime}");
        }
        if (!empty($secure) && !empty($secure->tokenTimeOut) && (empty($maxLifetime) || $secure->tokenTimeOut < $maxLifetime)) {
            $maxLifetime = $secure->tokenTimeOut;
            //_error_log("maxLifetime: Secure = {$maxLifetime}");
        }
    }
    return $maxLifetime;
}

$cacheExpirationTime = false;

function cacheExpirationTime()
{
    if (isBot()) {
        return 604800; // 1 week
    }
    global $cacheExpirationTime;
    if (empty($cacheExpirationTime)) {
        $obj = AVideoPlugin::getObjectDataIfEnabled('Cache');
        $cacheExpirationTime = @$obj->cacheTimeInSeconds;
    }
    return intval($cacheExpirationTime);
}

function getVideosURLPDF($fileName)
{
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $source = Video::getSourceFile($fileName, ".pdf");
    $file = $source['path'];
    $files["pdf"] = [
        'filename' => "{$fileName}.pdf",
        'path' => $file,
        'url' => $source['url'],
        'type' => 'pdf',
    ];
    $files = array_merge($files, array('jpg' => ImagesPlaceHolders::getPdfLandscape(ImagesPlaceHolders::$RETURN_ARRAY)));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLIMAGE($fileName)
{
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $types = ['png', 'gif', 'webp', 'jpg'];

    foreach ($types as $value) {
        $source = Video::getSourceFile($fileName, ".{$value}");
        $file = $source['path'];
        $files["image"] = [
            'filename' => "{$fileName}.{$value}",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'image',
        ];
        if (file_exists($file)) {
            $files = array_merge($files, array('jpg' => $files["image"]));
            break;
        }
    }
    if (empty($files["jpg"])) {
        $files = array_merge($files, array('jpg' => ImagesPlaceHolders::getImageLandscape(ImagesPlaceHolders::$RETURN_ARRAY)));
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLZIP($fileName)
{
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $types = ['zip'];

    foreach ($types as $value) {
        $source = Video::getSourceFile($fileName, ".{$value}");
        $file = $source['path'];
        $files["zip"] = [
            'filename' => "{$fileName}.zip",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'zip',
        ];
        if (file_exists($file)) {
            break;
        }
    }

    $files = array_merge($files, array('jpg' => ImagesPlaceHolders::getZipLandscape(ImagesPlaceHolders::$RETURN_ARRAY)));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLArticle($fileName)
{
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    $files = array('jpg' => ImagesPlaceHolders::getArticlesLandscape(ImagesPlaceHolders::$RETURN_ARRAY));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLAudio($fileName, $fileNameisThePath = false)
{
    global $global;
    if (empty($fileName)) {
        return [];
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    if ($fileNameisThePath) {
        $filename = basename($fileName);
        $path = Video::getPathToFile($filename);
        if (filesize($path) < 20) {
            $objCDNS = AVideoPlugin::getObjectDataIfEnabled('CDN');
            if (!empty($objCDNS) && $objCDNS->enable_storage) {
                $url = CDNStorage::getURL("{$filename}");
            }
        }
        if (empty($url)) {
            $url = Video::getURLToFile($filename);
        }

        $files["mp3"] = [
            'filename' => $filename,
            'path' => $path,
            'url' => $url,
            'url_noCDN' => $url,
            'type' => 'audio',
            'format' => 'mp3',
        ];
    } else {
        $source = Video::getSourceFile($fileName, ".mp3");
        $file = $source['path'];
        $files["mp3"] = [
            'filename' => "{$fileName}.mp3",
            'path' => $file,
            'url' => $source['url'],
            'url_noCDN' => @$source['url_noCDN'],
            'type' => 'audio',
            'format' => 'mp3',
        ];
    }

    $files = array_merge($files, array('jpg' => ImagesPlaceHolders::getAudioLandscape(ImagesPlaceHolders::$RETURN_ARRAY)));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLAudio generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURL($fileName, $recreateCache = false)
{
    return getVideosURL_V2($fileName, $recreateCache); // disable this function soon
}

function getVideosURLMP4Only($fileName)
{
    $allFiles = getVideosURL_V2($fileName);
    if (is_array($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['format'] !== 'mp4') {
                unset($allFiles[$key]);
            }
        }
        return $allFiles;
    }
    _error_log("getVideosURLMP4Only does not return an ARRAY from getVideosURL_V2($fileName) " . json_encode($allFiles));
    return [];
}

function getVideosURLMP3Only($fileName)
{
    $allFiles = getVideosURL_V2($fileName);
    if (is_array($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['format'] !== 'mp3') {
                unset($allFiles[$key]);
            }
        }
        return $allFiles;
    }
    _error_log("getVideosURLMP4Only does not return an ARRAY from getVideosURL_V2($fileName) " . json_encode($allFiles));
    return [];
}

function getVideosURLWEBMOnly($fileName)
{
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    if (is_array($allFiles)) {
        foreach ($allFiles as $key => $value) {
            if ($value['format'] !== 'webm') {
                unset($allFiles[$key]);
            }
        }
        return $allFiles;
    }
    _error_log("getVideosURLMP4Only does not return an ARRAY from getVideosURL_V2($fileName) " . json_encode($allFiles));
    return [];
}

function getVideosURLMP4WEBMOnly($fileName)
{
    return array_merge(getVideosURLMP4Only($fileName), getVideosURLWEBMOnly($fileName));
}

function getVideosURLMP4WEBMMP3Only($fileName)
{
    return array_merge(getVideosURLMP4Only($fileName), getVideosURLWEBMOnly($fileName), getVideosURLMP3Only($fileName));
}

function getVideosURLOnly($fileName, $includeOffline = true)
{
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    foreach ($allFiles as $key => $value) {
        if ($value['type'] !== 'video' || (!$includeOffline && preg_match('/offline/i', $key)) || preg_match('/.lock/i', $key)) {
            unset($allFiles[$key]);
        }
    }
    return $allFiles;
}

function getAudioURLOnly($fileName)
{
    $allFiles = getVideosURL_V2($fileName); // disable this function soon
    foreach ($allFiles as $key => $value) {
        if ($value['type'] !== 'audio') {
            unset($allFiles[$key]);
        }
    }
    return $allFiles;
}

function getAudioOrVideoURLOnly($fileName, $recreateCache = false)
{
    $allFiles = getVideosURL_V2($fileName, $recreateCache); // disable this function soon
    if ($recreateCache) {
        _error_log("getAudioOrVideoURLOnly($fileName) " . json_encode($allFiles));
    }
    foreach ($allFiles as $key => $value) {
        if (
            ($value['type'] !== 'video' && $value['type'] !== 'audio') ||
            (preg_match('/offline/i', $key) || preg_match('/.lock/i', $key))
        ) {
            unset($allFiles[$key]);
        }
    }
    return $allFiles;
}

function getVideos_IdFromFilename($fileName)
{
    $cleanfilename = Video::getCleanFilenameFromFile($fileName);
    $video = Video::getVideoFromFileNameLight($cleanfilename);
    return $video['id'];
}

$getVideosURL_V2Array = [];

function getVideosURL_V2($fileName, $recreateCache = false, $checkFiles = true)
{
    global $global, $getVideosURL_V2Array;
    if (empty($fileName)) {
        return [];
    }
    //$recreateCache = true;
    $cleanfilename = Video::getCleanFilenameFromFile($fileName);

    if (empty($recreateCache) && !empty($getVideosURL_V2Array[$cleanfilename])) {
        return $getVideosURL_V2Array[$cleanfilename];
    }

    $cacheSuffix = 'getVideosURL_V2';
    $paths = Video::getPaths($cleanfilename);
    $videoCache = new VideoCacheHandler($fileName);
    $videoCache->setSuffix($cacheSuffix);
    //$cacheName = "getVideosURL_V2$fileName";
    if (empty($recreateCache)) {
        $lifetime = maxLifetime();

        $TimeLog1 = "getVideosURL_V2($fileName) empty recreateCache";
        TimeLogStart($TimeLog1);
        //var_dump($cacheName, $lifetime);exit;
        $cache = $videoCache->getCache($cacheSuffix, $lifetime);
        //$cache = ObjectYPT::getCacheGlobal($cacheName, $lifetime, true);
        $files = object_to_array($cache);
        if (is_array($files)) {
            //_error_log("getVideosURL_V2: do NOT recreate lifetime = {$lifetime}");
            $preg_match_url = addcslashes(getCDN(), "/") . "videos";
            foreach ($files as $value) {
                // check if is a dummy file and the URL still wrong
                $pathFilesize = 0;
                if (!isValidURL($value['path']) && file_exists($value['path'])) {
                    $pathFilesize = filesize($value['path']);
                }
                if (
                    $value['type'] === 'video' && // is a video
                    preg_match("/^{$preg_match_url}/", $value['url']) && // the URL is the same as the main domain
                    $pathFilesize < 20
                ) { // file size is small
                    _error_log("getVideosURL_V2:: dummy file found, fix cache " . json_encode(["/^{$preg_match_url}/", $value['url'], preg_match("/^{$preg_match_url}video/", $value['url']), $pathFilesize, $value]));
                    unset($files);
                    clearCache();
                    //$video = Video::getVideoFromFileName($fileName, true, true);
                    //Video::clearCache($video['id']);
                    break;
                } else {
                    //_error_log("getVideosURL_V2:: NOT dummy file ". json_encode(array("/^{$preg_match_url}video/", $value['url'], preg_match("/^{$preg_match_url}video/", $value['url']),filesize($value['path']),$value)));
                }
            }
            //_error_log("getVideosURL_V2:: cachestill good ". json_encode($files));
        } else {
            //_error_log("getVideosURL_V2:: cache not found ". json_encode($files));
            $files = array();
        }

        TimeLogEnd($TimeLog1, __LINE__);
    } else {
        _error_log("getVideosURL_V2($fileName) Recreate cache requested " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    }
    if (empty($files)) {
        $files = [];
        $plugin = AVideoPlugin::loadPlugin("VideoHLS");
        if (!empty($plugin)) {
            $timeName = "getVideosURL_V2::VideoHLS::getSourceFile($fileName)";
            TimeLogStart($timeName);
            $files = VideoHLS::getSourceFile($fileName, true);
            if (!is_array($files)) {
                $files = [];
            }
            TimeLogEnd($timeName, __LINE__);
        }
        $video = ['webm', 'mp4'];
        $audio = ['mp3', 'ogg'];
        $image = ['jpg', 'gif', 'webp'];

        $formats = array_merge($video, $audio, $image);

        //$globQuery = getVideosDir()."{$cleanfilename}*.{" . implode(",", $formats) . "}";
        //$filesInDir = glob($globQuery, GLOB_BRACE);
        $timeName = "getVideosURL_V2::globVideosDir($cleanfilename)";
        TimeLogStart($timeName);
        $filesInDir = globVideosDir($cleanfilename, true, $recreateCache);
        TimeLogEnd($timeName, __LINE__);

        $timeName = "getVideosURL_V2::foreach";
        TimeLogStart($timeName);
        $isAVideo = false;
        foreach ($filesInDir as $file) {
            $parts = pathinfo($file);
            //_error_log("getVideosURL_V2($fileName) {$file}");
            if ($parts['extension'] == 'log') {
                continue;
            }
            if ($parts['filename'] == 'index') {
                $parts['filename'] = str_replace(Video::getPathToFile($parts['dirname']), '', $parts['dirname']);
                $parts['filename'] = str_replace(getVideosDir(), '', $parts['filename']);
            }
            //$timeName2 = "getVideosURL_V2::Video::getSourceFile({$parts['filename']}, .{$parts['extension']})";
            //TimeLogStart($timeName2);
            $source = Video::getSourceFile($parts['filename'], ".{$parts['extension']}");

            /*
            if(empty($recreateCache) && $fileName == "video_230816233020_vb81e"){
                var_dump($fileName, $source);exit;
            }
            */
            //TimeLogEnd($timeName2, __LINE__);
            if (empty($source)) {
                continue;
            }
            if (in_array($parts['extension'], $image) && filesize($file) < 1000 && !preg_match("/Dummy File/i", file_get_contents($file))) {
                continue;
            }
            if (preg_match("/{$cleanfilename}(_.+)[.]{$parts['extension']}$/", $file, $matches)) {
                $resolution = $matches[1];
            } else {
                preg_match('/_([^_]{0,4}).' . $parts['extension'] . '$/', $file, $matches);
                $resolution = @$matches[1];
            }
            if (empty($resolution)) {
                $resolution = '';
            }
            $type = 'video';

            if (in_array($parts['extension'], $video)) {
                $isAVideo = true;
                $type = 'video';
            } elseif (in_array($parts['extension'], $audio)) {
                $type = 'audio';
            } elseif (in_array($parts['extension'], $image) || preg_match('/^(gif|jpg|webp|png|jpeg)/i', $parts['extension'])) {
                $type = 'image';
                if (!preg_match('/(thumb|roku)/', $resolution)) {
                    if (preg_match("/{$cleanfilename}_([0-9]+).jpg/", $source['url'], $matches)) {
                        $resolution = '_' . intval($matches[1]);
                    } else {
                        $resolution = '';
                    }
                }
            }

            $_filename = "{$parts['filename']}.{$parts['extension']}";
            if ($parts['extension'] == 'm3u8') {
                $_filename = "index.m3u8";
            }
            if ($parts['basename'] == 'index.mp4') {
                $_filename = "index.mp4";
                $source['url'] = str_replace("{$parts['filename']}.mp4", 'index.mp4', $source['url']);
                $source['url_noCDN'] = str_replace("{$parts['filename']}.mp4", 'index.mp4', $source['url_noCDN']);
            }
            if ($parts['basename'] == 'index.mp3') {
                $_filename = "index.mp3";
                $source['url'] = str_replace("{$parts['filename']}.mp3", 'index.mp3', $source['url']);
                $source['url_noCDN'] = str_replace("{$parts['filename']}.mp3", 'index.mp3', $source['url_noCDN']);
            }

            $_file = [
                'filename' => $_filename,
                'path' => $file,
                'url' => $source['url'],
                'url_noCDN' => @$source['url_noCDN'],
                'type' => $type,
                'format' => mb_strtolower($parts['extension']),
            ];

            $files["{$parts['extension']}{$resolution}"] = $_file;
        }
        foreach ($files as $key => $_file) {
            $files[$key] = AVideoPlugin::modifyURL($_file);
        }
        TimeLogEnd($timeName, __LINE__);

        $pdf = $paths['path'] . "{$cleanfilename}.pdf";
        $mp3 = $paths['path'] . "{$cleanfilename}.mp3";

        $extraFiles = [];
        if (file_exists($pdf)) {
            $extraFilesPDF = getVideosURLPDF($fileName);
            if ($isAVideo) {
                unset($extraFilesPDF['jpg']);
                unset($extraFilesPDF['pjpg']);
            }
            $extraFiles = array_merge($extraFiles, $extraFilesPDF);
        }
        if (file_exists($mp3)) {
            $extraFilesMP3 = getVideosURLAudio($mp3, true);
            if ($isAVideo) {
                unset($extraFilesMP3['jpg']);
                unset($extraFilesMP3['pjpg']);
            }
            $extraFiles = array_merge($extraFiles, $extraFilesMP3);
        }
        $files = array_merge($extraFiles, $files);

        $videoCache->setCache($files);
    }
    /*
    if(empty($recreateCache) && $fileName == "v_230810144748_v424f"){
        var_dump($fileName, $files, debug_backtrace());exit;
    }
    */
    if (empty($files) || empty($files['jpg'])) {
        // sort by resolution
        $files['jpg'] = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_ARRAY);
    } else
    if (is_array($files)) {
        // sort by resolution
        uasort($files, "sortVideosURL");
    }
    $getVideosURL_V2Array[$cleanfilename] = $files;
    return $getVideosURL_V2Array[$cleanfilename];
}

function checkIfFilesAreValid($files)
{
    foreach ($files as $value) {
        if (($value['type'] == 'video' || $value['type'] == 'audio') && @filesize($value['path']) < 20) {
            $video = Video::getVideoFromFileNameLight($value['filename']);
            Video::clearCache($video['id']);
        }
    }
}

//Returns < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
function sortVideosURL($a, $b)
{
    if ($a['type'] === 'video' && $b['type'] === 'video') {
        $aRes = getResolutionFromFilename($a['filename']);
        $bRes = getResolutionFromFilename($b['filename']);
        return $aRes - $bRes;
    }
    if ($a['type'] === 'video') {
        return -1;
    } elseif ($b['type'] === 'video') {
        return 1;
    }

    return 0;
}

function getResolutionFromFilename($filename, $downloadIfNeed = true)
{
    global $getResolutionFromFilenameArray;

    if (!isset($getResolutionFromFilenameArray)) {
        $getResolutionFromFilenameArray = [];
    }

    if (!empty($getResolutionFromFilenameArray[$filename])) {
        return $getResolutionFromFilenameArray[$filename];
    }

    if (empty($filename)) {
        return 0;
    }
    if (!preg_match('/^http/i', $filename) && !file_exists($filename)) {
        return 0;
    }
    $res = Video::getResolutionFromFilename($filename, $downloadIfNeed);
    if (empty($res)) {
        if (preg_match('/[_\/]hd[.\/]/i', $filename)) {
            $res = 720;
        } elseif (preg_match('/[_\/]sd[.\/]/i', $filename)) {
            $res = 480;
        } elseif (preg_match('/[_\/]low[.\/]/i', $filename)) {
            $res = 240;
        } else {
            $res = 0;
        }
    }
    $getResolutionFromFilenameArray[$filename] = $res;
    return $res;
}

function getSources($fileName, $returnArray = false, $try = 0)
{
    if ($returnArray) {
        $videoSources = $audioTracks = $subtitleTracks = $captionsTracks = [];
    } else {
        $videoSources = $audioTracks = $subtitleTracks = $captionsTracks = '';
    }

    $video = Video::getVideoFromFileNameLight($fileName);

    if ($video['type'] !== 'audio' && function_exists('getVRSSources')) {
        $videoSources = getVRSSources($fileName, $returnArray);
    } else {
        $files = getVideosURL_V2($fileName, !empty($try));
        $sources = '';
        $sourcesArray = [];
        foreach ($files as $key => $value) {
            $path_parts = pathinfo($value['path']);
            if (Video::forceAudio() && $path_parts['extension'] !== "mp3") {
                continue;
            }
            if ($path_parts['extension'] == "webm" || $path_parts['extension'] == "mp4" || $path_parts['extension'] == "m3u8" || $path_parts['extension'] == "mp3" || $path_parts['extension'] == "ogg") {
                $obj = new stdClass();
                $obj->type = mime_content_type_per_filename($value['path']);
                $sources .= "<source src=\"{$value['url']}\" type=\"{$obj->type}\">";
                $obj->src = $value['url'];
                $sourcesArray[] = $obj;
            }
        }
        $videoSources = $returnArray ? $sourcesArray : $sources;
    }
    if (function_exists('getVTTTracks')) {
        $subtitleTracks = getVTTTracks($fileName, $returnArray);
    }
    if (function_exists('getVTTChapterTracks')) {
        $captionsTracks = getVTTChapterTracks($fileName, $returnArray);
    }
    //var_dump($subtitleTracks,  $captionsTracks);exit;
    if ($returnArray) {
        $return = array_merge($videoSources, $audioTracks, $subtitleTracks,  $captionsTracks);
    } else {
        // remove index.mp4
        $videoSources = preg_replace('/<source src=".*index.mp4.*" type="video\/mp4" label="Low" res="360">/', '<!-- index.mp4 removed -->', $videoSources);
        //var_dump($videoSources);exit;
        $return = $videoSources . $audioTracks  . PHP_EOL . $subtitleTracks  . PHP_EOL . $captionsTracks;
    }

    $obj = new stdClass();
    $obj->result = $return;
    if (empty($videoSources) && empty($audioTracks) && !empty($video['id']) && $video['type'] == 'video') {
        if (empty($try)) {
            //sleep(1);
            $sources = getSources($fileName, $returnArray, $try + 1);
            if (!empty($sources)) {
                Video::updateFilesize($video['id']);
            }
            Video::clearCache($video['id']);
            return $sources;
        } else {
            _error_log("getSources($fileName) File not found " . json_encode($video));
            if (empty($sources)) {
                $sources = [];
            }
            $obj = new stdClass();
            $obj->type = "video/mp4";
            $obj->src = "Video not found";
            $obj->label = "Video not found";
            $obj->res = 0;
            $sourcesArray["mp4"] = $obj;
            $sources["mp4"] = "<source src=\"\" type=\"{$obj->type}\" label=\"{$obj->label}\" res=\"{$obj->res}\">";
            $return = $returnArray ? $sourcesArray : PHP_EOL . implode(PHP_EOL, $sources) . PHP_EOL;
        }
    }
    return $return;
}

function getSourceFromURL($url)
{
    $url = AVideoPlugin::modifyURL($url);
    $type = mime_content_type_per_filename($url);
    return "<source src=\"{$url}\" type=\"{$type}\">";
}

function decideMoveUploadedToVideos($tmp_name, $filename, $type = "video")
{
    if ($filename == '.zip') {
        return false;
    }
    global $global;
    $obj = new stdClass();
    $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
    $paths = Video::getPaths($filename, true);
    $destinationFile = "{$paths['path']}{$filename}";
    //$destinationFile = getVideosDir() . "{$filename}";
    _error_log("decideMoveUploadedToVideos: {$filename}");
    $path_info = pathinfo($filename);
    if ($type !== "zip" && $path_info['extension'] === 'zip') {
        _error_log("decideMoveUploadedToVideos: ZIp file {$filename}");
        $paths = Video::getPaths($path_info['filename']);
        $dir = $paths['path'];
        unzipDirectory($tmp_name, $dir); // unzip it
        cleanDirectory($dir);
        if (!empty($aws_s3)) {
            //$aws_s3->move_uploaded_file($tmp_name, $filename);
        } elseif (!empty($bb_b2)) {
            $bb_b2->move_uploaded_directory($dir);
        } elseif (!empty($ftp)) {
            //$ftp->move_uploaded_file($tmp_name, $filename);
        }
    } else {
        _error_log("decideMoveUploadedToVideos: NOT ZIp file {$filename}");
        if (!empty($aws_s3)) {
            _error_log("decideMoveUploadedToVideos: S3 {$filename}");
            $aws_s3->move_uploaded_file($tmp_name, $filename);
        } elseif (!empty($bb_b2)) {
            _error_log("decideMoveUploadedToVideos: B2 {$filename}");
            $bb_b2->move_uploaded_file($tmp_name, $filename);
        } elseif (!empty($ftp)) {
            _error_log("decideMoveUploadedToVideos: FTP {$filename}");
            $ftp->move_uploaded_file($tmp_name, $filename);
        } else {
            _error_log("decideMoveUploadedToVideos: Local {$filename}");
            if (!move_uploaded_file($tmp_name, $destinationFile)) {
                if (!rename($tmp_name, $destinationFile)) {
                    if (!copy($tmp_name, $destinationFile)) {
                        $obj->msg = "Error on decideMoveUploadedToVideos({$tmp_name}, $destinationFile)";
                        die(json_encode($obj));
                    }
                }
            }
            if (file_exists($destinationFile)) {
                _error_log("decideMoveUploadedToVideos: SUCCESS Local {$destinationFile}");
            } else {
                _error_log("decideMoveUploadedToVideos: ERROR Local {$destinationFile}");
            }
            chmod($destinationFile, 0644);
        }
    }
    sleep(1);
    $fsize = @filesize($destinationFile);
    _error_log("decideMoveUploadedToVideos: destinationFile {$destinationFile} filesize=" . ($fsize) . " (" . humanFileSize($fsize) . ")");
    Video::clearCacheFromFilename($filename);
    return $destinationFile;
}

function isAnyStorageEnabled()
{
    if ($yptStorage = AVideoPlugin::loadPluginIfEnabled("YPTStorage")) {
        return true;
    } elseif ($aws_s3 = AVideoPlugin::loadPluginIfEnabled("AWS_S3")) {
        return true;
    } elseif ($bb_b2 = AVideoPlugin::loadPluginIfEnabled("Blackblaze_B2")) {
        return true;
    } elseif ($ftp = AVideoPlugin::loadPluginIfEnabled("FTP_Storage")) {
        return true;
    }
    return false;
}

function fontAwesomeClassName($filename)
{
    $mime_type = mime_content_type_per_filename($filename);
    // List of official MIME Types: http://www.iana.org/assignments/media-types/media-types.xhtml
    $icon_classes = [
        // Media
        'image' => 'fas fa-file-image',
        'audio' => 'fas fa-file-audio',
        'video' => 'fas fa-file-video',
        // Documents
        'application/pdf' => 'fas fa-file-pdf',
        'application/msword' => 'fas fa-file-word',
        'application/vnd.ms-word' => 'fas fa-file-word',
        'application/vnd.oasis.opendocument.text' => 'fas fa-file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml' => 'fas fa-file-word',
        'application/vnd.ms-excel' => 'fas fa-file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml' => 'fas fa-file-excel',
        'application/vnd.oasis.opendocument.spreadsheet' => 'fas fa-file-excel',
        'application/vnd.ms-powerpoint' => 'fas fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml' => 'fas fa-file-powerpoint',
        'application/vnd.oasis.opendocument.presentation' => 'fas fa-file-powerpoint',
        'text/plain' => 'far fa-file-alt',
        'text/html' => 'fas fa-code',
        'application/json' => 'fas fa-code',
        // Archives
        'application/gzip' => 'far fa-file-archive',
        'application/zip' => 'far fa-file-archive',
    ];
    foreach ($icon_classes as $text => $icon) {
        if (strpos($mime_type, $text) === 0) {
            return $icon;
        }
    }
    return 'fas fa-file';
}

function combineFiles($filesArray, $extension = "js")
{
    global $global, $advancedCustom;

    if ($extension == 'js' && isBot()) {
        return getCDN() . 'view/js/empty.js';
    }

    $relativeDir = 'videos/cache/' . $extension . '/';
    $cacheDir = $global['systemRootPath'] . $relativeDir;
    $str = '';
    $fileName = '';
    foreach ($filesArray as $value) {
        $fileName .= $value . filectime($global['systemRootPath'] . $value) . filemtime($global['systemRootPath'] . $value);
    }
    if ($advancedCustom !== false) {
        $minifyEnabled = $advancedCustom->EnableMinifyJS;
    } else {
        $minifyEnabled = false;
    }
    // temporary disable minify
    $minifyEnabled = false;

    $md5FileName = md5($fileName) . ".{$extension}";
    if (!file_exists($cacheDir . $md5FileName)) {
        foreach ($filesArray as $value) {
            if (file_exists($global['systemRootPath'] . $value)) {
                $str .= "\n/*{$value} created local with systemRootPath */\n" . local_get_contents($global['systemRootPath'] . $value);
            } elseif (file_exists($value)) {
                $str .= "\n/*{$value} created local with full-path given */\n" . local_get_contents($value);
            } else {
                $allowed = '';
                if (ini_get('allow_url_fopen')) {
                    $allowed .= "allow_url_fopen is on and ";
                }
                if (function_exists('curl_init')) {
                    $allowed .= "curl is on";
                } else {
                    $allowed .= "curl is off";
                }

                $content = url_get_contents($value);
                if (empty($content)) {
                    $allowed .= " - web-fallback 1 (add webSiteRootURL)";
                    $content = url_get_contents($global['webSiteRootURL'] . $value);
                }
                $str .= "\n/*{$value} created via web with own url ({$allowed}) */\n" . $content;
            }
        }
        //if ((($extension == "js" || $extension == "css") && ($minifyEnabled))) {
        if ($extension == "css" && ($minifyEnabled)) {
            require_once $global['systemRootPath'] . 'objects/jshrink.php';
            $str = \JShrink\Minifier::minify($str, ['flaggedComments' => false]);
        }
        if (!is_dir($cacheDir)) {
            make_path($cacheDir);
        }
        $bytes = _file_put_contents($cacheDir . $md5FileName, $str);
        if (empty($bytes)) {
            _error_log('combineFiles: error on save strlen=' . strlen($str) . ' ' . $cacheDir . $md5FileName . ' cacheDir=' . $cacheDir);
            return false;
        }
    }

    return getURL($relativeDir . $md5FileName);
}

function combineFilesHTML($filesArray, $extension = "js", $doNotCombine = false)
{
    if (empty($doNotCombine)) {
        $jsURL = combineFiles($filesArray, $extension);
    }
    if ($extension == "js") {
        if (empty($jsURL)) {
            $str = '';
            foreach ($filesArray as $value) {
                $jsURL = getURL($value);
                $str .= '<script src="' . $jsURL . '" type="text/javascript"></script>';
            }
            return $str;
        } else {
            return '<script src="' . $jsURL . '" type="text/javascript"></script>';
        }
    } else {
        if (empty($jsURL)) {
            $str = '';
            foreach ($filesArray as $value) {
                $jsURL = getURL($value);
                $str .= '<link href="' . $jsURL . '" rel="stylesheet" type="text/css"/>';
            }
            return $str;
        } else {
            return '<link href="' . $jsURL . '" rel="stylesheet" type="text/css"/>';
        }
    }
}

function getTagIfExists($relativePath)
{
    global $global;
    $relativePath = str_replace('\\', '/', $relativePath);
    $file = "{$global['systemRootPath']}{$relativePath}";
    if (file_exists($file)) {
        $url = getURL($relativePath);
    } elseif (isValidURL($file)) {
        $url = $file;
    } else {
        return '';
    }
    $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
    if ($ext === 'js') {
        return '<script src="' . $url . '" type="text/javascript"></script>';
    } elseif ($ext === 'css') {
        return '<link href="' . $url . '" rel="stylesheet" type="text/css"/>';
    } else {
        return getImageTagIfExists($relativePath);
    }
}

function getRelativePath($path)
{
    global $global;
    $relativePath = '';
    $parts = explode('view/img/', $path);

    if (!empty($parts[1])) {
        $relativePath = 'view/img/' . $parts[1];
    }
    if (empty($relativePath)) {
        $parts = explode('videos/', $path);
        if (!empty($parts[1])) {
            $relativePath = 'videos/' . $parts[1];
        }
    }

    if (empty($relativePath)) {
        $relativePath = $path;
    }
    $parts2 = explode('?', $relativePath);
    $relativePath = str_replace('\\', '/', $relativePath);
    //var_dump($path, $relativePath, $parts);
    return $parts2[0];
}

function isValidM3U8Link($url, $skipFileNameCheck = false, $timeout = 3)
{
    if (!isValidURL($url)) {
        return false;
    }
    if (preg_match('/.m3u8$/i', $url)) {
        if (empty($skipFileNameCheck)) {
            return true;
        }
    }
    // Check the content length without downloading the file
    $headers = get_headers($url, 1);
    $contentLength = isset($headers['Content-Length']) ? intval($headers['Content-Length']) : 0;

    // If the content size is greater than 2MB, return false
    if ($contentLength > 2 * 1024 * 1024) {
        return false;
    }

    // Fetch the first few KB of the content
    $content = url_get_contents($url, '', $timeout);

    if (!empty($content)) {
        if (preg_match('/<html/i', $content)) {
            return false;
        }
        // Use a regular expression to check if the content is a valid M3U8 link
        if (preg_match('/#EXTM3U/i', $content)) {
            return true;
        }
    }

    return false;
}

function copy_remotefile_if_local_is_smaller($url, $destination)
{
    if (file_exists($destination)) {
        $size = filesize($destination);
        $remote_size = getUsageFromURL($url);
        if ($size >= $remote_size) {
            _error_log('copy_remotefile_if_local_is_smaller same size ' . $url);
            return $remote_size;
        }
    }
    $content = url_get_contents($url);
    _error_log('copy_remotefile_if_local_is_smaller url_get_contents = ' . humanFileSize(strlen($content)));
    return file_put_contents($destination, $content);
}

function url_get_contents_with_cache($url, $lifeTime = 60, $ctx = "", $timeout = 0, $debug = false, $mantainSession = false)
{
    $url = removeQueryStringParameter($url, 'pass');
    $cacheName = str_replace('/', '-', $url);
    $cache = ObjectYPT::getCacheGlobal($cacheName, $lifeTime); // 24 hours
    if (!empty($cache)) {
        //_error_log('url_get_contents_with_cache cache');
        return $cache;
    }
    _error_log("url_get_contents_with_cache no cache [$url] " . json_encode(debug_backtrace()));
    $return = url_get_contents($url, $ctx, $timeout, $debug, $mantainSession);
    $response = ObjectYPT::setCacheGlobal($cacheName, $return);
    _error_log("url_get_contents_with_cache setCache {$url} " . json_encode($response));
    return $return;
}

function url_get_response($url)
{
    $responseObj = new stdClass();
    $responseObj->error = true;
    $responseObj->code = 0;
    $responseObj->msg = '';
    $responseObj->response = '';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HEADER, true); // Include the header in the output
    curl_setopt($ch, CURLOPT_NOBODY, true); // Exclude the body from the output

    $responseObj->response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseObj->code = $httpCode;

    // Map of HTTP status codes to messages
    $httpMessages = [
        200 => "Success",
        400 => "Bad request. Please check the parameters.",
        401 => "Unauthorized. Please check your credentials.",
        403 => "Forbidden. You don't have permission to access this resource.",
        404 => "Not found. The stream key does not exist.",
        500 => "Internal server error. Please try again later.",
        502 => "Bad gateway. There might be an issue with the server.",
        503 => "Service unavailable. The server is currently unable to handle the request.",
    ];

    if (array_key_exists($httpCode, $httpMessages)) {
        $responseObj->msg = $httpMessages[$httpCode];
        if ($httpCode == 200) {
            $responseObj->error = false;
        }
    } else {
        $responseObj->msg = "Unexpected error occurred.";
    }

    return $responseObj;
}


function url_get_contents($url, $ctx = "", $timeout = 0, $debug = false, $mantainSession = false)
{
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
    if (!isValidURLOrPath($url)) {
        _error_log('url_get_contents Cannot download ' . $url);
        return false;
    }
    if ($debug) {
        _error_log("url_get_contents: Start $url, $ctx, $timeout " . getSelfURI() . " " . getRealIpAddr() . " " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    }

    $response = try_get_contents_from_local($url);
    if (!empty($response)) {
        return $response;
    }

    $agent = getSelfUserAgent();

    if (isSameDomainAsMyAVideo($url) || $mantainSession) {
        $session_cookie = session_name() . '=' . session_id();
        _session_write_close();
    }
    if (empty($ctx)) {
        $opts = [
            'http' => ['header' => "User-Agent: {$agent}\r\n"],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        if (!empty($timeout)) {
            ini_set('default_socket_timeout', $timeout);
            $opts['http']['timeout'] = $timeout;
        }
        if (!empty($session_cookie)) {
            $opts['http']['header'] .= "Cookie: {$session_cookie}\r\n";
        }
        $context = stream_context_create($opts);
    } else {
        $context = $ctx;
    }
    if (ini_get('allow_url_fopen')) {
        if ($debug) {
            _error_log("url_get_contents: allow_url_fopen {$url}");
        }
        try {
            if ($debug) {
                $tmp = file_get_contents($url, false, $context);
            } else {
                $tmp = @file_get_contents($url, false, $context);
            }
            if ($tmp !== false) {
                $response = remove_utf8_bom($tmp);
                if ($debug) {
                    //_error_log("url_get_contents: SUCCESS file_get_contents($url) {$response}");
                    _error_log("url_get_contents: SUCCESS file_get_contents($url)");
                }
                return $response;
            }
            if ($debug) {
                $error = error_get_last();
                _error_log("url_get_contents: ERROR file_get_contents($url) " . json_encode($error));
            }
        } catch (ErrorException $e) {
            if ($debug) {
                _error_log("url_get_contents: allow_url_fopen ERROR " . $e->getMessage() . "  {$url}");
            }
            return "url_get_contents: " . $e->getMessage();
        }
    }
    if (function_exists('curl_init')) {
        if ($debug) {
            _error_log("url_get_contents: CURL  {$url} ");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if (!empty($session_cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $session_cookie);
        }
        if (!empty($timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout + 10);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        if ($debug) {
            _error_log("url_get_contents: CURL SUCCESS {$url}");
        }
        return remove_utf8_bom($output);
    }
    if ($debug) {
        _error_log("url_get_contents: Nothing yet  {$url}");
    }

    // try wget
    $filename = getTmpDir("YPTurl_get_contents") . md5($url);
    if ($debug) {
        _error_log("url_get_contents: try wget $filename {$url}");
    }
    if (wget($url, $filename, $debug)) {
        if ($debug) {
            _error_log("url_get_contents: wget success {$url} ");
        }
        $result = file_get_contents($filename);
        unlink($filename);
        if (!empty($result)) {
            return remove_utf8_bom($result);
        }
    } elseif ($debug) {
        _error_log("url_get_contents: try wget fail {$url}");
    }

    return false;
}

function getUpdatesFilesArray()
{
    global $config, $global;
    if (!class_exists('User') || !User::isAdmin()) {
        return [];
    }
    $files1 = scandir($global['systemRootPath'] . "updatedb");
    $updateFiles = [];
    foreach ($files1 as $value) {
        preg_match("/updateDb.v([0-9.]*).sql/", $value, $match);
        if (!empty($match)) {
            if ($config->currentVersionLowerThen($match[1])) {
                $updateFiles[] = ['filename' => $match[0], 'version' => $match[1]];
            }
        }
    }
    usort($updateFiles, function ($a, $b) {
        return version_compare($a['version'], $b['version']);
    });
    return $updateFiles;
}

function thereIsAnyUpdate()
{
    if (!User::isAdmin()) {
        return false;
    }
    $name = 'thereIsAnyUpdate';
    if (!isset($_SESSION['sessionCache'][$name])) {
        $files = getUpdatesFilesArray();
        if (!empty($files)) {
            _session_start();
            $_SESSION['sessionCache'][$name] = $files;
        }
    }
    return @$_SESSION['sessionCache'][$name];
}

function thereIsAnyRemoteUpdate()
{
    if (!User::isAdmin()) {
        return false;
    }
    global $config;

    $cacheName = '_thereIsAnyRemoteUpdate';
    $cache = ObjectYPT::getCacheGlobal($cacheName, 86400); // 24 hours
    if (!empty($cache)) {
        return $cache;
    }

    $version = _json_decode(url_get_contents("https://tutorials.wwbn.net/version"));
    //$version = _json_decode(url_get_contents("https://tutorialsavideo.b-cdn.net/version", "", 4));
    if (empty($version)) {
        return false;
    }
    $name = 'thereIsAnyRemoteUpdate';
    if (!isset($_SESSION['sessionCache'][$name])) {
        if (!empty($version)) {
            _session_start();
            if (version_compare($config->getVersion(), $version->version) === -1) {
                $_SESSION['sessionCache'][$name] = $version;
            } else {
                $_SESSION['sessionCache'][$name] = false;
            }
        }
    }
    ObjectYPT::setCacheGlobal($cacheName, $_SESSION['sessionCache'][$name]);
    return $_SESSION['sessionCache'][$name];
}

function UTF8encode($data)
{
    if (emptyHTML($data)) {
        return $data;
    }

    global $advancedCustom;

    if (function_exists('mb_convert_encoding')) {
        if (!empty($advancedCustom->utf8Encode)) {
            return mb_convert_encoding($data, 'UTF-8', mb_detect_encoding($data));
        }

        if (!empty($advancedCustom->utf8Decode)) {
            return mb_convert_encoding($data, mb_detect_encoding($data), 'UTF-8');
        }
    } else {
        _error_log('UTF8encode: mbstring extension is not installed');
    }

    return $data;
}

function encryptPassword($password, $noSalt = false)
{
    global $advancedCustom, $global, $advancedCustomUser;
    if (!empty($advancedCustomUser->encryptPasswordsWithSalt) && !empty($global['salt']) && empty($noSalt)) {
        $password .= $global['salt'];
    }

    return md5(hash("whirlpool", sha1($password)));
}

function encryptPasswordVerify($password, $hash, $encodedPass = false)
{
    global $advancedCustom, $global;
    if (!$encodedPass || $encodedPass === 'false') {
        //_error_log("encryptPasswordVerify: encrypt");
        $passwordSalted = encryptPassword($password);
        // in case you enable the salt later
        $passwordUnSalted = encryptPassword($password, true);
    } else {
        //_error_log("encryptPasswordVerify: do not encrypt");
        $passwordSalted = $password;
        // in case you enable the salt later
        $passwordUnSalted = $password;
    }
    //_error_log("passwordSalted = $passwordSalted,  hash=$hash, passwordUnSalted=$passwordUnSalted");
    $isValid = $passwordSalted === $hash || $passwordUnSalted === $hash;

    if (!$isValid) {
        $passwordFromHash = User::getPasswordFromUserHashIfTheItIsValid($password);
        $isValid = $passwordFromHash === $hash;
    }

    if (!$isValid) {
        if ($password === $hash) {
            _error_log('encryptPasswordVerify: this is a deprecated password, this will stop to work soon ' . json_encode(debug_backtrace()), AVideoLog::$SECURITY);
            return true;
        }
    }
    return $isValid;
}

function isMobile($userAgent = null, $httpHeaders = null)
{
    if (empty($userAgent) && empty($_SERVER["HTTP_USER_AGENT"])) {
        return false;
    }
    global $global;
    require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
    $detect = new Mobile_Detect();

    return $detect->isMobile($userAgent, $httpHeaders);
}

function isAndroid()
{
    global $global;
    require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
    $detect = new Mobile_Detect();


    $androidTV = getDeviceName();

    return $detect->is('AndroidOS') || preg_match('/android/i', $androidTV);
}

function isChannelPage()
{
    return strpos($_SERVER["SCRIPT_NAME"], 'view/channel.php') !== false;
}

function getRefferOrOrigin()
{
    $url = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $url = $_SERVER['HTTP_REFERER'];
    } elseif (!empty($_SERVER['HTTP_ORIGIN'])) {
        $url = $_SERVER['HTTP_ORIGIN'];
    }
    return $url;
}

function addGlobalTokenIfSameDomain($url)
{
    if (!filter_var($url, FILTER_VALIDATE_URL) || (empty($_GET['livelink']) || !preg_match("/^http.*/i", $_GET['livelink']))) {
        return $url;
    }
    if (!isSameDomainAsMyAVideo($url)) {
        return $url;
    }
    return addQueryStringParameter($url, 'globalToken', getToken(60));
}

function isGlobalTokenValid()
{
    if (empty($_REQUEST['globalToken'])) {
        return false;
    }
    return verifyToken($_REQUEST['globalToken']);
}

/**
 * Remove a query string parameter from an URL.
 *
 * @param string $url
 * @param string $varname
 *
 * @return string
 */
function removeQueryStringParameter($url, $varname)
{
    $parsedUrl = parse_url($url);
    if (empty($parsedUrl) || empty($parsedUrl['host'])) {
        return $url;
    }
    $query = [];

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
        unset($query[$varname]);
    }

    $path = $parsedUrl['path'] ?? '';
    $query = !empty($query) ? '?' . http_build_query($query) : '';

    if (empty($parsedUrl['scheme'])) {
        $scheme = '';
    } else {
        $scheme = "{$parsedUrl['scheme']}:";
    }
    $port = '';
    if (!empty($parsedUrl['port']) && $parsedUrl['port'] != '80' && $parsedUrl['port'] != '443') {
        $port = ":{$parsedUrl['port']}";
    }
    $query = fixURLQuery($query);
    return $scheme . '//' . $parsedUrl['host'] . $port . $path . $query;
}

function isParamInUrl($url, $paramName)
{
    // Parse the URL and return its components
    $urlComponents = parse_url($url);

    // Check if the query part of the URL is set
    if (!isset($urlComponents['query'])) {
        return false;
    }

    // Parse the query string into an associative array
    parse_str($urlComponents['query'], $queryParams);

    // Check if the parameter is present in the query array
    return array_key_exists($paramName, $queryParams);
}

/**
 * Add a query string parameter from an URL.
 *
 * @param string $url
 * @param string $varname
 *
 * @return string
 */
function addQueryStringParameter($url, $varname, $value)
{
    if ($value === null || $value === '') {
        return removeQueryStringParameter($url, $varname);
    }

    $parsedUrl = parse_url($url);
    if (empty($parsedUrl['host'])) {
        return "";
    }
    $query = [];

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
    }
    $query[$varname] = $value;

    // Ensure 'current' is the last parameter
    $currentValue = null;
    if (isset($query['current'])) {
        $currentValue = $query['current'];
        unset($query['current']);
    }

    $path = $parsedUrl['path'] ?? '';
    $queryString = http_build_query($query);

    // Append 'current' at the end, if it exists
    if ($currentValue !== null) {
        $queryString = (!empty($queryString) ? $queryString . '&' : '') . 'current=' . intval($currentValue);
    }
    $query = !empty($queryString) ? '?' . $queryString : '';

    $port = '';
    if (!empty($parsedUrl['port']) && $parsedUrl['port'] != '80' && $parsedUrl['port'] != '443') {
        $port = ":{$parsedUrl['port']}";
    }

    if (empty($parsedUrl['scheme'])) {
        $scheme = '';
    } else {
        $scheme = "{$parsedUrl['scheme']}:";
    }

    $query = fixURLQuery($query);

    return $scheme . '//' . $parsedUrl['host'] . $port . $path . $query;
}

function fixURLQuery($query)
{
    return str_replace(array('%5B', '%5D'), array('[', ']'), $query);
}

function isSameDomain($url1, $url2)
{
    if (empty($url1) || empty($url2)) {
        return false;
    }
    return (get_domain($url1) === get_domain($url2));
}

function get_domain($url, $ifEmptyReturnSameString = false)
{
    $pieces = parse_url($url);
    $domain = $pieces['host'] ?? '';
    if (empty($domain)) {
        return $ifEmptyReturnSameString ? $url : false;
    }
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    } else {
        $isIp = (bool) ip2long($pieces['host']);
        if ($isIp) {
            return $pieces['host'];
        }
    }
    return false;
}

function verify($url)
{
    global $global;
    ini_set('default_socket_timeout', 5);
    $cacheFile = sys_get_temp_dir() . '/' . md5($url) . "_verify.log";
    $lifetime = 86400; //24 hours
    _error_log("Verification Start {$url} cacheFile={$cacheFile}");
    $verifyURL = "https://search.ypt.me/verify.php";
    $verifyURL = addQueryStringParameter($verifyURL, 'url', $url);
    $verifyURL = addQueryStringParameter($verifyURL, 'screenshot', 1);

    if (file_exists($cacheFile) && (time() < (filemtime($cacheFile) + $lifetime))) {
        $result = file_get_contents($cacheFile);
    }

    if (empty($result)) {
        _error_log("Verification Creating the Cache {$url}");
        $result = url_get_contents($verifyURL, '', 5);
        if ($result !== 'Invalid URL') {
            file_put_contents($cacheFile, $result);
        }
    } else {
        _error_log("Verification GetFrom Cache $cacheFile");
        if ($result === 'Invalid URL') {
            _error_log("Verification Invalid URL unlink ($cacheFile)");
            unlink($cacheFile);
        }
    }
    _error_log("Verification Response ($verifyURL): result={$result}");
    return json_decode($result);
}

function getPorts()
{
    $ports = array();
    $ports[80] = 'Apache http';
    $ports[443] = 'Apache https';
    if (AVideoPlugin::isEnabledByName('Live')) {
        $ports[8080] = 'NGINX http';
        $ports[8443] = 'NGINX https';
        $ports[1935] = 'RTMP';
    }
    if ($obj = AVideoPlugin::getDataObjectIfEnabled('WebRTC')) {
        $ports[$obj->port] = 'WebRTC';
    }

    if ($obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket')) {
        $ports[$obj->port] = 'Socket';
    }
    return $ports;
}

function isVerified($url)
{
    $resultV = verify($url);
    if (!empty($resultV) && !$resultV->verified) {
        error_log("Error on Login not verified");
        return false;
    }
    return true;
}

function siteMap()
{
    _error_log("siteMap: start");
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 0);
    @_session_write_close();
    global $global, $advancedCustom;

    $totalCategories = 0;
    $totalChannels = 0;
    $totalVideos = 0;

    $global['disableVideoTags'] = 1;
    $date = date('Y-m-d\TH:i:s') . "+00:00";
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd
        http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
        <!-- Main Page -->
        <url>
            <loc>' . $global['webSiteRootURL'] . '</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>always</changefreq>
            <priority>1.00</priority>
        </url>

        <url>
            <loc>' . $global['webSiteRootURL'] . 'help</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>
        <url>
            <loc>' . $global['webSiteRootURL'] . 'about</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>
        <url>
            <loc>' . $global['webSiteRootURL'] . 'contact</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>

        <!-- Channels -->
        <url>
            <loc>' . $global['webSiteRootURL'] . 'channels</loc>
            <lastmod>' . $date . '</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.80</priority>
        </url>
        ';
    if (empty($_REQUEST['catName'])) {
        setRowCount($advancedCustom->siteMapRowsLimit);
        _error_log("siteMap: rowCount {$_REQUEST['rowCount']} ");
        $_POST['sort']['modified'] = "DESC";
        TimeLogStart("siteMap getAllUsersThatHasVideos");
        $users = User::getAllUsersThatHasVideos(true);
        _error_log("siteMap: getAllUsers " . count($users));
        foreach ($users as $value) {
            $totalChannels++;
            $xml .= '
            <url>
                <loc>' . User::getChannelLink($value['id']) . '</loc>
                <lastmod>' . $date . '</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.90</priority>
            </url>
            ';
        }
        $xml .= PHP_EOL . '<!-- Channels END total=' . $totalChannels . ' -->' . PHP_EOL;
        TimeLogEnd("siteMap getAllUsersThatHasVideos", __LINE__, 0.5);
        TimeLogStart("siteMap getAllCategories");
        $xml .= PHP_EOL . '<!-- Categories -->' . PHP_EOL;
        setRowCount($advancedCustom->siteMapRowsLimit);
        $_POST['sort']['modified'] = "DESC";
        $rows = Category::getAllCategories();
        _error_log("siteMap: getAllCategories " . count($rows));
        foreach ($rows as $value) {
            $totalCategories++;
            $xml .= '
            <url>
                <loc>' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '</loc>
                <lastmod>' . $date . '</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.80</priority>
            </url>
            ';
        }
        $xml .= PHP_EOL . '<!-- Categories END total=' . $totalCategories . ' -->' . PHP_EOL;
        TimeLogEnd("siteMap getAllCategories", __LINE__, 0.5);
    }


    TimeLogStart("siteMap getAllVideos");
    $xml .= '<!-- Videos -->';
    setRowCount($advancedCustom->siteMapRowsLimit * 10);
    $_POST['sort']['created'] = "DESC";
    $rows = Video::getAllVideosLight(!empty($advancedCustom->showPrivateVideosOnSitemap) ? Video::SORT_TYPE_VIEWABLENOTUNLISTED : Video::SORT_TYPE_PUBLICONLY);
    if (empty($rows) || !is_array($rows)) {
        $rows = [];
    }
    $total = count($rows);
    _error_log("siteMap: getAllVideos total={$total}");

    $descriptionLimit = 2048;
    if ($total > 2000) {
        $descriptionLimit = 128;
    } else if ($total > 1000) {
        $descriptionLimit = 256;
    } else if ($total > 500) {
        $descriptionLimit = 512;
    } else if ($total > 200) {
        $descriptionLimit = 1024;
    }
    foreach ($rows as $video) {
        $totalVideos++;
        $videos_id = $video['id'];

        TimeLogStart("siteMap Video::getPoster $videos_id");
        $img = Video::getPoster($videos_id);
        TimeLogEnd("siteMap Video::getPoster $videos_id", __LINE__, 0.5);

        if (empty($advancedCustom->disableSiteMapVideoDescription)) {
            $description = str_ireplace(['"', "\n", "\r", '&nbsp;'], ['', ' ', ' ', ' '], empty(trim($video['description'])) ? $video['title'] : $video['description']);
            $description = _substr(strip_tags(br2nl($description)), 0, $descriptionLimit);
        } else {
            $description = false;
        }

        $duration = parseDurationToSeconds($video['duration']);
        if ($duration > 28800) {
            // this is because this issue https://github.com/WWBN/AVideo/issues/3338 remove in the future if is not necessary anymore
            $duration = 28800;
        }

        TimeLogStart("siteMap Video::getLink $videos_id");
        //$loc = Video::getLink($video['id'], $video['clean_title']);
        $loc = Video::getLinkToVideo($video['id'], $video['clean_title'], false, Video::$urlTypeFriendly, [], true);

        TimeLogEnd("siteMap Video::getLink $videos_id", __LINE__, 0.5);
        $title = strip_tags($video['title']);
        TimeLogStart("siteMap Video::getLinkToVideo $videos_id");
        $player_loc = Video::getLinkToVideo($video['id'], $video['clean_title'], true, Video::$urlTypeShort);
        //$player_loc = $loc;
        TimeLogEnd("siteMap Video::getLinkToVideo $videos_id", __LINE__, 0.5);
        TimeLogStart("siteMap Video::isPublic $videos_id");
        $requires_subscription = Video::isPublic($video['id']) ? "no" : "yes";
        TimeLogEnd("siteMap Video::isPublic $videos_id", __LINE__, 0.5);
        TimeLogStart("siteMap Video::getChannelLink $videos_id");
        $uploader_info = User::getChannelLink($video['users_id']);
        TimeLogEnd("siteMap Video::getChannelLink $videos_id", __LINE__, 0.5);
        TimeLogStart("siteMap Video::getNameIdentificationById $videos_id");
        $uploader = htmlentities(User::getNameIdentificationById($video['users_id']));
        TimeLogEnd("siteMap Video::getNameIdentificationById $videos_id", __LINE__, 0.5);

        $xml .= '
            <url>
                <loc>' . $loc . '</loc>
                <video:video>
                    <video:thumbnail_loc>' . $img . '</video:thumbnail_loc>
                    <video:title><![CDATA[' . $title . ']]></video:title>
                    <video:description><![CDATA[' . $description . ']]></video:description>
                    <video:player_loc><![CDATA[' . $player_loc . ']]></video:player_loc>
                    <video:duration>' . $duration . '</video:duration>
                    <video:view_count>' . $video['views_count'] . '</video:view_count>
                    <video:publication_date>' . date("Y-m-d\TH:i:s", strtotime($video['created'])) . '+00:00</video:publication_date>
                    <video:family_friendly>yes</video:family_friendly>
                    <video:requires_subscription>' . $requires_subscription . '</video:requires_subscription>
                    <video:uploader info="' . $uploader_info . '"><![CDATA[' . $uploader . ']]></video:uploader>
                    <video:live>no</video:live>
                </video:video>
            </url>
            ';
    }
    TimeLogEnd("siteMap getAllVideos", __LINE__, 0.5);
    $xml .= PHP_EOL . '<!-- Videos END total=' . $totalVideos . ' -->' . PHP_EOL;
    $xml .= '</urlset> ';
    _error_log("siteMap: done ");
    $newXML1 = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xml);
    if (empty($newXML1)) {
        _error_log("siteMap: pregreplace1 fail ");
        $newXML1 = $xml;
    }
    if (!empty($advancedCustom->siteMapUTF8Fix)) {
        $newXML2 = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $newXML1);
        if (empty($newXML2)) {
            _error_log("siteMap: pregreplace2 fail ");
            $newXML2 = $newXML1;
        }
        $newXML3 = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $newXML2);
        if (empty($newXML3)) {
            _error_log("siteMap: pregreplace3 fail ");
            $newXML3 = $newXML2;
        }
        $newXML4 = preg_replace('/[\x00-\x1F\x7F]/', '', $newXML3);
        if (empty($newXML4)) {
            _error_log("siteMap: pregreplace4 fail ");
            $newXML4 = $newXML3;
        }
        $newXML5 = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $newXML4);
        if (empty($newXML5)) {
            _error_log("siteMap: pregreplace5 fail ");
            $newXML5 = $newXML4;
        }
    } else {
        $newXML5 = $newXML1;
    }
    return $newXML5;
}

function object_to_array($obj, $level = 0)
{
    //only process if it's an object or array being passed to the function
    if (is_object($obj) || is_array($obj)) {
        $ret = (array) $obj;
        foreach ($ret as &$item) {
            //recursively process EACH element regardless of type
            $item = object_to_array($item, $level + 1);
        }
        return $ret;
    }
    //otherwise (i.e. for scalar values) return without modification
    else {
        if (empty($level) && empty($obj)) {
            $obj = array();
        }
        return $obj;
    }
}

function allowOrigin()
{
    global $global;
    cleanUpAccessControlHeader();
    $HTTP_ORIGIN = empty($_SERVER['HTTP_ORIGIN']) ? @$_SERVER['HTTP_REFERER'] : $_SERVER['HTTP_ORIGIN'];
    if (empty($HTTP_ORIGIN)) {
        $server = parse_url($global['webSiteRootURL']);
        header('Access-Control-Allow-Origin: ' . $server["scheme"] . '://imasdk.googleapis.com');
    } else {
        header("Access-Control-Allow-Origin: " . $HTTP_ORIGIN);
    }
    header('Access-Control-Allow-Private-Network: true');
    header('Access-Control-Request-Private-Network: true');
    //header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
    header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
}

function cleanUpAccessControlHeader()
{
    if (!headers_sent()) {
        foreach (headers_list() as $header) {
            if (preg_match('/Access-Control-Allow-Origin/i', $header)) {
                $parts = explode(':', $header);
                header_remove($parts[0]);
            }
        }
    }
    header('Access-Control-Allow-Origin: ');  // This will essentially "remove" the header
}

function getAdsDebugTag($adCode)
{
    global $global;
    if (!empty($_REQUEST['AdsDebug']) && User::isAdmin()) {
        $function = debug_backtrace()[1]["function"];
        $function = str_replace('get', '', $function);
        $adCode = "<div class=\"AdsDebug\">{$function}<br>{$global['lastAdsCodeReason']}<br>$adCode</div>";
    }
    return  $adCode;
}

function getAdsLeaderBoardBigVideo()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('leaderBoardBigVideo');
    }
    return getAdsDebugTag($adCode);
}

function getAdsLeaderBoardTop()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('leaderBoardTop');
    }
    return  getAdsDebugTag($adCode);
}

function getAdsChannelLeaderBoardTop()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('channelLeaderBoardTop');
    }
    return  getAdsDebugTag($adCode);
}

function getAdsLeaderBoardTop2()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('leaderBoardTop2');
    }
    return  getAdsDebugTag($adCode);
}

function getAdsLeaderBoardMiddle()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('leaderBoardMiddle');
    }
    return  getAdsDebugTag($adCode);
}

function getAdsLeaderBoardFooter()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('leaderBoardFooter');
    }
    return  getAdsDebugTag($adCode);
}

function getAdsSideRectangle()
{
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    $adCode = '';
    if (!empty($ad)) {
        $adCode = ADs::getAdsCode('sideRectangle');
    }
    return  getAdsDebugTag($adCode);
}

function isToHidePrivateVideos()
{
    $obj = AVideoPlugin::getObjectDataIfEnabled("Gallery");
    if (!empty($obj)) {
        return $obj->hidePrivateVideos;
    }
    $obj = AVideoPlugin::getObjectDataIfEnabled("YouPHPFlix2");
    if (!empty($obj)) {
        return $obj->hidePrivateVideos;
    }
    $obj = AVideoPlugin::getObjectDataIfEnabled("YouTube");
    if (!empty($obj)) {
        return $obj->hidePrivateVideos;
    }
    return false;
}

function ogSite()
{
    global $global, $config, $advancedCustom;
    $videos_id = getVideos_id();
    include_once $global['systemRootPath'] . 'objects/functionsOpenGraph.php';
    if (empty($videos_id)) {
        $isLive = isLive(true);
        //var_dump($isLive);exit;
        if (!empty($isLive) && !empty($isLive['liveLink'])) {
            echo getOpenGraphLiveLink($isLive['liveLink']);
        } else if (!empty($isLive) && !empty($isLive['live_schedule'])) {
            echo getOpenGraphLiveSchedule($isLive['live_schedule']);
        } else if (!empty($isLive) && !empty($isLive['cleanKey'])) {
            echo getOpenGraphLive();
        } else if ($users_id = isChannel()) {
            echo getOpenGraphChannel($users_id);
        } else if (!empty($_REQUEST['catName'])) {
            $category = Category::getCategoryByName($_REQUEST['catName']);
            echo getOpenGraphCategory($category['id']);
        } else if (!empty($_REQUEST['tags_id']) && class_exists('Tags') && class_exists('VideoTags')) {
            echo getOpenGraphTag($_REQUEST['tags_id']);
        } else {
            echo getOpenGraphSite();
        }
    } else {
        echo getOpenGraphVideo($videos_id);
    }
}

function getOpenGraph($videos_id)
{
    global $global, $config, $advancedCustom;
    include_once $global['systemRootPath'] . 'objects/functionsOpenGraph.php';
    echo getOpenGraphVideo($videos_id);
}

function getLdJson($videos_id)
{
    $cache = ObjectYPT::getCacheGlobal("getLdJson{$videos_id}", 0);
    if (empty($cache)) {
        echo $cache;
    }
    global $global, $config;
    echo "<!-- ld+json -->";
    if (empty($videos_id)) {
        echo "<!-- ld+json no video id -->";
        if (!empty($_GET['videoName'])) {
            $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        }
    } else {
        echo "<!-- ld+json videos_id {$videos_id} -->";
        $video = Video::getVideoLight($videos_id);
    }
    if (empty($video)) {
        echo "<!-- ld+json no video -->";
        return false;
    }
    $videos_id = $video['id'];

    $img = Video::getPoster($videos_id);

    $description = getSEODescription(_empty($video['description']) ? $video['title'] : $video['description']);
    $duration = Video::getItemPropDuration($video['duration']);
    if ($duration == "PT0H0M0S") {
        $duration = "PT0H0M1S";
    }
    $data = array(
        "@context" => "http://schema.org/",
        "@type" => "VideoObject",
        "name" => getSEOTitle($video['title']),
        "description" => $description,
        "thumbnailUrl" => array($img),
        "uploadDate" => date("Y-m-d\Th:i:s", strtotime($video['created'])),
        "duration" => $duration,
        "contentUrl" => Video::getLinkToVideo($videos_id, '', false, false),
        "embedUrl" => Video::getLinkToVideo($videos_id, '', true, false),
        "interactionCount" => $video['views_count'],
        "@id" => Video::getPermaLink($videos_id),
        "datePublished" => date("Y-m-d", strtotime($video['created'])),
        "interactionStatistic" => array(
            array(
                "@type" => "InteractionCounter",
                "interactionService" => array(
                    "@type" => "WebSite",
                    "name" => str_replace('"', '', $config->getWebSiteTitle()),
                    "@id" => $global['webSiteRootURL']
                ),
                "interactionType" => "http://schema.org/LikeAction",
                "userInteractionCount" => $video['views_count']
            ),
            array(
                "@type" => "InteractionCounter",
                "interactionType" => "http://schema.org/WatchAction",
                "userInteractionCount" => $video['views_count']
            )
        )
    );
    if (AVideoPlugin::isEnabledByName('Bookmark')) {
        $chapters = Bookmark::generateChaptersJSONLD($videos_id);
        if (!empty($chapters)) {
            $data['videoChapter'] = $chapters;
        }
    }

    $output = '<script type="application/ld+json" id="application_ld_json">';
    $output .= json_encode($data, JSON_UNESCAPED_SLASHES);
    $output .= '</script>';

    ObjectYPT::setCacheGlobal("getLdJson{$videos_id}", $output);
    echo $output;
}

function getItemprop($videos_id)
{
    $cache = ObjectYPT::getCacheGlobal("getItemprop{$videos_id}", 0);
    if (empty($cache)) {
        echo $cache;
    }
    global $global, $config;
    echo "<!-- Itemprop -->";
    if (empty($videos_id)) {
        echo "<!-- Itemprop no video id -->";
        if (!empty($_GET['videoName'])) {
            $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        }
    } else {
        echo "<!-- Itemprop videos_id {$videos_id} -->";
        $video = Video::getVideoLight($videos_id);
    }
    if (empty($video)) {
        echo "<!-- Itemprop no video -->";
        return false;
    }
    $videos_id = $video['id'];
    $img = Video::getPoster($videos_id);

    $description = getSEODescription(emptyHTML($video['description']) ? $video['title'] : $video['description']);
    $duration = Video::getItemPropDuration($video['duration']);
    if ($duration == "PT0H0M0S") {
        $duration = "PT0H0M1S";
    }
    $output = '<span itemprop="name" content="' . getSEOTitle($video['title']) . '"></span>
    <span itemprop="description" content="' . $description . '"></span>
    <span itemprop="thumbnailUrl" content="' . $img . '"></span>
    <span itemprop="uploadDate" content="' . date("Y-m-d\Th:i:s", strtotime($video['created'])) . '"></span>
    <span itemprop="duration" content="' . $duration . '"></span>
    <span itemprop="contentUrl" content="' . Video::getLinkToVideo($videos_id) . '"></span>
    <span itemprop="embedUrl" content="' . parseVideos(Video::getLinkToVideo($videos_id)) . '"></span>
    <span itemprop="interactionCount" content="' . $video['views_count'] . '"></span>';

    ObjectYPT::setCacheGlobal("getItemprop{$videos_id}", $output);
    echo $output;
}

function parse_url_parameters($url)
{
    // Parse the URL and separate the query string
    $parsedUrl = parse_url($url);

    // Initialize the result array
    $result = [
        'base_url' => '',
        'parameters' => []
    ];

    // Extract the base URL
    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    if (isset($parsedUrl['port'])) {
        $baseUrl .= ':' . $parsedUrl['port'];
    }
    if (isset($parsedUrl['path'])) {
        $baseUrl .= $parsedUrl['path'];
    }
    $result['base_url'] = $baseUrl;

    // Extract the query string and parse parameters
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $parameters);
        $result['parameters'] = $parameters;
    }

    return $result;
}

function get_contents($url, $timeout = 0){
    if(strlen($url)>1000){
        $result = parse_url_parameters($url);
        return postVariables($result['base_url'], $result['parameters'], false, $timeout);
    }else{
        return url_get_contents($url, $timeout);
    }
}

function postVariables($url, $array, $httpcodeOnly = true, $timeout = 10)
{
    if (!$url || !is_string($url) || !preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url)) {
        return false;
    }
    $array = object_to_array($array);
    $ch = curl_init($url);
    if ($httpcodeOnly) {
        @curl_setopt($ch, CURLOPT_HEADER, true);  // we want headers
        @curl_setopt($ch, CURLOPT_NOBODY, true);  // we don't need body
    } else {
        curl_setopt($ch, CURLOPT_USERAGENT, getSelfUserAgent());
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout + 1); //The maximum number of seconds to allow cURL functions to execute.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    // execute!
    $response = curl_exec($ch);

    if (!$response) {
        $error_msg = curl_error($ch);
        $error_num = curl_errno($ch);
        _error_log("postVariables: {$url} [$error_num] - $error_msg");
    }
    if ($httpcodeOnly) {
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // close the connection, release resources used
        curl_close($ch);
        if ($httpcode == 200) {
            return true;
        }
        return $httpcode;
    } else {
        curl_close($ch);
        return $response;
    }
}

function debugMemmory($line)
{
    global $lastDebugMemory, $lastDebugMemoryLine, $global;
    if (empty($global['debugMemmory'])) {
        return false;
    }
    $memory = memory_get_usage();
    if (!isset($lastDebugMemory)) {
        $lastDebugMemory = $memory;
        $lastDebugMemoryLine = $line;
    } else {
        $increaseB = ($memory - $lastDebugMemory);
        $increase = humanFileSize($increaseB);
        $total = humanFileSize($memory);
        _error_log("debugMemmory increase: {$increase} from line $lastDebugMemoryLine to line $line total now {$total} [$increaseB]");
    }
}

/**
 * we will not regenerate the session on this page
 * this is necessary because of the signup from the iframe pages
 * @return boolean
 */
function blackListRegenerateSession()
{
    if (!requestComesFromSafePlace()) {
        return false;
    }
    $list = [
        'objects/getCaptcha.php',
        'objects/userCreate.json.php',
        'objects/videoAddViewCount.json.php',
    ];
    foreach ($list as $needle) {
        if (str_ends_with($_SERVER['SCRIPT_NAME'], $needle)) {
            return true;
        }
    }
    return false;
}

function remove_utf8_bom($text)
{
    if (strlen($text) > 1000000) {
        return $text;
    }

    $bom = pack('H*', 'EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

function getCacheDir()
{
    $p = AVideoPlugin::loadPlugin("Cache");
    if (empty($p)) {
        return addLastSlash(sys_get_temp_dir());
    }
    return $p->getCacheDir();
}

function clearCache($firstPageOnly = false)
{
    global $global;
    $lockFile = getVideosDir() . '.clearCache.lock';
    if (file_exists($lockFile) && filectime($lockFile) > strtotime('-5 minutes')) {
        _error_log('clearCache is in progress ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        return false;
    }
    $start = microtime(true);
    _error_log('clearCache starts ' . $firstPageOnly);
    file_put_contents($lockFile, time());

    $dir = getVideosDir() . "cache" . DIRECTORY_SEPARATOR;
    $tmpDir = ObjectYPT::getCacheDir('firstPage');
    $parts = explode('firstpage', $tmpDir);

    if ($firstPageOnly || !empty($_REQUEST['FirstPage'])) {
        $tmpDir = $parts[0] . 'firstpage' . DIRECTORY_SEPARATOR;
        //var_dump($tmpDir);exit;
        $dir .= "firstPage" . DIRECTORY_SEPARATOR;
    } else {
        $tmpDir = $parts[0];
    }

    //_error_log('clearCache 1: '.$dir);
    rrmdir($dir);
    rrmdir($tmpDir);

    $obj = AVideoPlugin::getDataObjectIfEnabled('Cache');
    if ($obj) {
        $tmpDir = $obj->cacheDir;
        rrmdir($tmpDir);
    }
    ObjectYPT::deleteCache("getEncoderURL");
    ObjectYPT::deleteAllSessionCache();
    if (class_exists('Live')) {
        Live::checkAllFromStats();
    }
    unlink($lockFile);
    $end = microtime(true) - $start;
    _error_log("clearCache end in {$end} seconds");
    return true;
}

function clearAllUsersSessionCache()
{
    _error_log("clearAllUsersSessionCache ".json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    sendSocketMessageToAll(time(), 'socketClearSessionCache');
}

function clearFirstPageCache()
{
    return clearCache(true);
}

function unsetSearch()
{
    unset($_GET['searchPhrase'], $_POST['searchPhrase'], $_GET['search'], $_GET['q']);
}

function encrypt_decrypt($string, $action, $useOldSalt = false)
{
    global $global;
    $output = false;
    if (empty($string)) {
        //_error_log("encrypt_decrypt: Empty input string.");
        return false;
    }
    //_error_log("encrypt_decrypt: input string: $string");
    $encrypt_method = "AES-256-CBC";
    $secret_iv = $global['systemRootPath'];
    while (strlen($secret_iv) < 16) {
        $secret_iv .= $global['systemRootPath'];
    }
    if (empty($secret_iv)) {
        $secret_iv = '1234567890abcdef';
    }

    if ($useOldSalt) {
        $saltType = 'old';
        $salt = $global['salt'];
    } else {
        $saltType = 'new';
        $salt = empty($global['saltV2']) ? $global['salt'] : $global['saltV2'];
    }

    // hash
    $key = hash('sha256', $salt);

    // iv - encrypt method AES-256-CBC expects 16 bytes
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        if ($output === false) {
            _error_log("encrypt_decrypt: Failed to encrypt. String: {$string}");
        }
        $output = base64_encode($output);
    } elseif ($action == 'decrypt') {
        $decoded_string = base64_decode($string);
        if ($decoded_string === false) {
            _error_log("encrypt_decrypt: Failed to base64 decode the string: {$string}");
            return false;
        }

        $output = openssl_decrypt($decoded_string, $encrypt_method, $key, 0, $iv);

        // Try with the old salt if the output is empty and not already using the old salt
        if (empty($output) && $useOldSalt === false) {
            _error_log("encrypt_decrypt: Failed to decrypt. Debug details:");
            _error_log("encrypt_decrypt: Encoded string: {$string}");
            _error_log("encrypt_decrypt: Base64 decoded string: {$decoded_string}");
            _error_log("encrypt_decrypt: Salt used ($saltType): {$salt}");
            _error_log("encrypt_decrypt: Encryption method: {$encrypt_method}");
            _error_log("encrypt_decrypt: Key: {$key}");
            _error_log("encrypt_decrypt: IV: {$iv}");
            _error_log("encrypt_decrypt: Retrying decryption with old salt.");
            return encrypt_decrypt($string, $action, true);
        }
    }

    return $output;
}


function compressString($string)
{
    if (function_exists("gzdeflate")) {
        $string = gzdeflate($string, 9);
    }
    return $string;
}

function decompressString($string)
{
    if (function_exists("gzinflate")) {
        $string = gzinflate($string);
    }
    return $string;
}

function encryptString($string)
{
    if (is_object($string) || is_array($string)) {
        $string = json_encode($string);
    }
    return encrypt_decrypt($string, 'encrypt');
}

function decryptString($string)
{
    return encrypt_decrypt($string, 'decrypt');
}

function getToken($timeout = 0, $salt = "", $videos_id = 0)
{
    global $global;
    $obj = new stdClass();
    $obj->salt = $global['salt'] . $salt;
    $obj->timezone = date_default_timezone_get();
    $obj->videos_id = $videos_id;

    if (!empty($timeout)) {
        $obj->time = time();
        $obj->timeout = $obj->time + $timeout;
    } else {
        $obj->time = strtotime("Today 00:00:00");
        $obj->timeout = strtotime("Today 23:59:59");
        $obj->timeout += cacheExpirationTime();
    }
    $strObj = json_encode($obj);
    //_error_log("Token created: {$strObj}");

    return encryptString($strObj);
}

function isTokenValid($token, $salt = "")
{
    return verifyToken($token, $salt);
}

function verifyToken($token, $salt = "")
{
    global $global;
    $obj = _json_decode(decryptString($token));
    if (empty($obj)) {
        _error_log("verifyToken invalid token");
        return false;
    }
    if ($obj->salt !== $global['salt'] . $salt) {
        _error_log("verifyToken salt fail");
        return false;
    }
    if (!empty($obj->videos_id) && $obj->videos_id != getVideos_id()) {
        _error_log("This is not to this videos ID");
        return false;
    }

    $old_timezone = date_default_timezone_get();
    date_default_timezone_set($obj->timezone);
    $time = time();
    date_default_timezone_set($old_timezone);
    if (!($time >= $obj->time && $time <= $obj->timeout)) {
        _error_log("verifyToken token timout time = $time; obj->time = $obj->time;  obj->timeout = $obj->timeout");
        return false;
    }
    return true;
}

class YPTvideoObject
{

    public $id;
    public $title;
    public $description;
    public $thumbnails;
    public $channelTitle;
    public $videoLink;

    public function __construct($id, $title, $description, $thumbnails, $channelTitle, $videoLink)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->thumbnails = $thumbnails;
        $this->channelTitle = $channelTitle;
        $this->videoLink = $videoLink;
    }
}

function isToShowDuration($type)
{
    $notShowTo = ['pdf', 'article', 'serie', 'zip', 'image', 'live', 'livelinks'];
    if (in_array($type, $notShowTo)) {
        return false;
    } else {
        return true;
    }
}

function isAVideoPlayer()
{
    global $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    if (isVideo() || isSerie()) {
        return true;
    }
    return false;
}

function isFirstPage()
{
    global $isFirstPage, $global;
    return !empty($isFirstPage) || getSelfURI() === "{$global['webSiteRootURL']}view/";
}

function isVideo()
{
    global $isModeYouTube, $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    return !empty($isModeYouTube) || isPlayList() || isEmbed() || isLive();
}

function isOffline()
{
    global $_isOffline;
    return !empty($_isOffline);
}

function isVideoTypeEmbed()
{
    global $isVideoTypeEmbed;

    if (isVideo() && !empty($isVideoTypeEmbed) && $videos_id = getVideos_id()) {
        return $videos_id;
    }

    return false;
}

function isAudio()
{
    global $isAudio;
    return !empty($isAudio) || Video::forceAudio();
}

function isSerie()
{
    return isPlayList();
}

function isPlayList()
{
    global $isPlayList, $isSerie;
    return !empty($isSerie) || !empty($isPlayList);
}

function isChannel()
{
    global $isChannel;
    if (!empty($isChannel) && !isVideo()) {
        $user_id = 0;
        if (empty($_GET['channelName'])) {
            if (!empty($_GET['channel_users_id'])) {
                $user_id = intval($_GET['channel_users_id']);
            } else if (User::isLogged()) {
                $user_id = User::getId();
            } else {
                return false;
            }
        } else {
            $_GET['channelName'] = xss_esc($_GET['channelName']);
            $user = User::getChannelOwner($_GET['channelName']);
            if (!empty($user)) {
                $user_id = $user['id'];
            } else {
                $user_id = $_GET['channelName'];
            }
        }
        return $user_id;
    }
    return false;
}

function isEmbed()
{
    global $isEmbed, $global;
    if (!empty($global['doNotLoadPlayer'])) {
        return false;
    }
    return !empty($isEmbed);
}

function isLive($forceGetInfo = false)
{
    global $isLive, $global;
    if (empty($forceGetInfo) && !empty($global['doNotLoadPlayer'])) {
        return false;
    }
    if (class_exists('LiveTransmition') && class_exists('Live')) {
        $livet = LiveTransmition::getFromRequest();
        if (!empty($livet)) {
            setLiveKey($livet['key'], Live::getLiveServersIdRequest(), @$_REQUEST['live_index']);
            $isLive = 1;
        }
    }
    if (!empty($isLive)) {
        $live = getLiveKey();
        if (empty($live)) {
            $live = ['key' => false, 'live_servers_id' => false, 'live_index' => false, 'live_schedule' => false, 'users_id' => false];
        }
        $live['liveLink'] = isLiveLink();
        return $live;
    } else {
        return false;
    }
}

function isLiveLink()
{
    global $isLiveLink;
    if (!empty($isLiveLink)) {
        return $isLiveLink;
    } else {
        return false;
    }
}

function getLiveKey()
{
    global $getLiveKey;
    if (empty($getLiveKey)) {
        return false;
    }
    return $getLiveKey;
}

function setLiveKey($key, $live_servers_id, $live_index = '')
{
    global $getLiveKey;
    $parameters = Live::getLiveParametersFromKey($key);
    $key = $parameters['key'];
    $cleanKey = $parameters['cleanKey'];
    if (empty($live_index)) {
        $live_index = $parameters['live_index'];
    }
    $key = Live::getLiveKeyFromRequest($key, $live_index, $parameters['playlists_id_live']);
    $lt = LiveTransmition::getFromKey($key);
    $live_schedule = 0;
    $users_id = 0;
    if (!empty($lt['live_schedule_id'])) {
        $live_schedule = $lt['live_schedule_id'];
        $live_servers_id = $lt['live_servers_id'];
        $users_id = $lt['users_id'];
    }
    $getLiveKey = ['key' => $key, 'live_servers_id' => intval($live_servers_id), 'live_index' => $live_index, 'cleanKey' => $cleanKey, 'live_schedule' => $live_schedule, 'users_id' => $users_id];
    return $getLiveKey;
}

function isVideoPlayerHasProgressBar()
{
    if (isLive()) {
        $obj = AVideoPlugin::getObjectData('Live');
        if (empty($obj->disableDVR)) {
            return true;
        }
    } elseif (isAVideoPlayer()) {
        return true;
    }
    return false;
}

function isHLS()
{
    global $video, $global;
    if (isLive()) {
        return true;
    } elseif (!empty($video) && is_array($video) && $video['type'] == 'video' && file_exists(Video::getPathToFile("{$video['filename']}/index.m3u8"))) {
        return true;
    }
    return false;
}

function getRedirectUri($returnThisIfRedirectUriIsNotSet = false)
{
    if (isValidURL(@$_GET['redirectUri'])) {
        return $_GET['redirectUri'];
    }
    if (isValidURL(@$_SESSION['redirectUri'])) {
        return $_SESSION['redirectUri'];
    }
    if (isValidURL(@$_REQUEST["redirectUri"])) {
        return $_REQUEST["redirectUri"];
    }
    if (isValidURL(@$_SERVER["HTTP_REFERER"])) {
        return $_SERVER["HTTP_REFERER"];
    }
    if (isValidURL($returnThisIfRedirectUriIsNotSet)) {
        return $returnThisIfRedirectUriIsNotSet;
    } else {
        return getRequestURI();
    }
}

function setRedirectUri($redirectUri)
{
    _session_start();
    $_SESSION['redirectUri'] = $redirectUri;
}

function redirectIfRedirectUriIsSet()
{
    $redirectUri = false;
    if (!empty($_GET['redirectUri'])) {
        if (isSameDomainAsMyAVideo($_GET['redirectUri'])) {
            $redirectUri = $_GET['redirectUri'];
        }
    }
    if (!empty($_SESSION['redirectUri'])) {
        if (isSameDomainAsMyAVideo($_SESSION['redirectUri'])) {
            $redirectUri = $_SESSION['redirectUri'];
        }
        _session_start();
        unset($_SESSION['redirectUri']);
    }

    if (!empty($redirectUri)) {
        header("Location: {$redirectUri}");
        exit;
    }
}

function getRedirectToVideo($videos_id)
{
    $redirectUri = getRedirectUri();
    $isEmbed = 0;
    if (stripos($redirectUri, "embed") !== false) {
        $isEmbed = 1;
    }
    $video = Video::getVideoLight($videos_id);
    if (empty($video)) {
        return false;
    }
    return Video::getLink($videos_id, $video['clean_title'], $isEmbed);
}

function getRequestURI()
{
    if (empty($_SERVER['REQUEST_URI'])) {
        return "";
    }
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function getSelfURI()
{
    if (empty($_SERVER['PHP_SELF']) || empty($_SERVER['HTTP_HOST'])) {
        return "";
    }
    global $global;
    $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    if (preg_match('/^https:/i', $global['webSiteRootURL'])) {
        $http = 'https';
    }

    $queryString = preg_replace("/error=[^&]*/", "", @$_SERVER['QUERY_STRING']);
    $queryString = preg_replace("/inMainIframe=[^&]*/", "", $queryString);
    $phpselfWithoutIndex = preg_replace("/index.php/", "", @$_SERVER['PHP_SELF']);
    $url = $http . "://$_SERVER[HTTP_HOST]$phpselfWithoutIndex?$queryString";
    $url = rtrim($url, '?');

    preg_match('/view\/modeYoutube.php\?v=([^&]+)/', $url, $matches);
    if (!empty($matches[1])) {
        $url = "{$global['webSiteRootURL']}video/{$matches[1]}";
    }
    return fixTestURL($url);
}

function isSameVideoAsSelfURI($url)
{
    return URLsAreSameVideo($url, getSelfURI());
}

function URLsAreSameVideo($url1, $url2)
{
    $videos_id1 = getVideoIDFromURL($url1);
    $videos_id2 = getVideoIDFromURL($url2);
    if (empty($videos_id1) || empty($videos_id2)) {
        return false;
    }
    return $videos_id1 === $videos_id2;
}

function getVideos_id($returnPlaylistVideosIDIfIsSerie = false)
{
    global $_getVideos_id, $global;
    if (!empty($global['isForbidden'])) {
        return 0;
    }
    $videos_id = false;
    if (isset($_getVideos_id) && is_int($_getVideos_id)) {
        $videos_id = $_getVideos_id;
    } else {
        if (isVideo()) {
            $videos_id = getVideoIDFromURL(getSelfURI());
            if (empty($videos_id) && !empty($_REQUEST['videoName'])) {
                $video = Video::getVideoFromCleanTitle($_REQUEST['videoName']);
                if (!empty($video)) {
                    $videos_id = $video['id'];
                }
            }
            setVideos_id($videos_id);
        }
        if (empty($videos_id) && !empty($_REQUEST['playlists_id'])) {
            AVideoPlugin::loadPlugin('PlayLists');
            $video = PlayLists::isPlayListASerie($_REQUEST['playlists_id']);
            if (!empty($video)) {
                $videos_id = $video['id'];
            }
        }

        if (empty($videos_id) && !empty($_REQUEST['v'])) {
            $videos_id = $_REQUEST['v'];
        }

        if (empty($videos_id) && !empty($_REQUEST['videos_id'])) {
            $videos_id = $_REQUEST['videos_id'];
        }

        if (empty($videos_id) && (!empty($_REQUEST['playlists_id']) || (!empty($_REQUEST['tags_id']) && isset($_REQUEST['playlist_index'])))) {
            AVideoPlugin::loadPlugin('PlayLists');
            $plp = new PlayListPlayer(@$_REQUEST['playlists_id'], @$_REQUEST['tags_id'], true);

            if (!$plp->canSee()) {
                forbiddenPage(_('You cannot see this playlist') . ' ' . basename(__FILE__) . ' ' . implode(', ', $plp->canNotSeeReason()));
            }
            $video = $plp->getCurrentVideo();
            if (!empty($video)) {
                $videos_id = $video['id'];
            }
        }

        $videos_id = videosHashToID($videos_id);
    }
    if ($returnPlaylistVideosIDIfIsSerie && empty($videos_id)) {
        $videos_id = getPlayListCurrentVideosId();
    }
    return $videos_id;
}

function getUsers_idOwnerFromRequest()
{
    global $isChannel;
    $videos_id = getVideos_id();

    if (!empty($videos_id)) {
        $video = new Video('', '', $videos_id);
        return $video->getUsers_id();
    }
    $live = isLive();
    if (!empty($live)) {
        if (!empty($live['users_id'])) {
            return $live['users_id'];
        }
        if (!empty($live['live_schedule'])) {
            return Live_schedule::getUsers_idOrCompany($live['live_schedule']);
        }
        if (!empty($live['key'])) {
            $row = LiveTransmition::keyExists($live['key']);
            return $row['users_id'];
        }
    }

    if (!empty($isChannel) && !isVideo()) {
        if (!empty($_GET['channelName'])) {
            $_GET['channelName'] = xss_esc($_GET['channelName']);
            $user = User::getChannelOwner($_GET['channelName']);
            if (!empty($user)) {
                $users_id = $user['id'];
            } else {
                $users_id = intval($_GET['channelName']);
            }
            return $users_id;
        }
    }

    return 0;
}

function getPlayListIndex()
{
    global $__playlistIndex;
    if (empty($__playlistIndex) && !empty($_REQUEST['playlist_index'])) {
        $__playlistIndex = intval($_REQUEST['playlist_index']);
    }
    return intval($__playlistIndex);
}

function getPlayListData()
{
    global $playListData;
    if (empty($playListData)) {
        $playListData = [];
    }
    return $playListData;
}

function getPlayListDataVideosId()
{
    $playListData_videos_id = [];
    foreach (getPlayListData() as $value) {
        $playListData_videos_id[] = $value->getVideos_id();
    }
    return $playListData_videos_id;
}

function getPlayListCurrentVideo($setVideos_id = true)
{
    $videos_id = getPlayListCurrentVideosId($setVideos_id);
    if (empty($videos_id)) {
        return false;
    }
    $video = Video::getVideo($videos_id);
    return $video;
}

function getPlayListCurrentVideosId($setVideos_id = true)
{
    global $getVideosIDFromPlaylistLightLastSQL;
    $playListData = getPlayListData();
    $playlist_index = getPlayListIndex();
    if (empty($playListData) && !empty($_REQUEST['playlist_id']) && class_exists('PlayList')) {
        $videosArrayId = PlayList::getVideosIdFromPlaylist($_REQUEST['playlist_id']);
        //_error_log('getPlayListCurrentVideosId line='.__LINE__." playlist_id={$_REQUEST['playlist_id']} playlist_index={$playlist_index} ".json_encode($getVideosIDFromPlaylistLightLastSQL));
        $videos_id = $videosArrayId[$playlist_index];
    } else {
        if (empty($playListData[$playlist_index])) {
            //var_dump($playlist_index, $playListData);
            return false;
        } else {
            //_error_log('getPlayListCurrentVideosId line='.__LINE__." playlist_id={$_REQUEST['playlist_id']} playlist_index={$playlist_index}");
            $videos_id = $playListData[$playlist_index]->getVideos_id();
        }
    }
    if ($setVideos_id) {
        setVideos_id($videos_id);
    }
    return $videos_id;
}

function setPlayListIndex($index)
{
    global $__playlistIndex;
    $__playlistIndex = intval($index);
}

function setVideos_id($videos_id)
{
    global $_getVideos_id;
    $_getVideos_id = $videos_id;
}

function getPlaylists_id()
{
    global $_isPlayList;
    if (!isset($_isPlayList)) {
        $_isPlayList = false;
        if (isPlayList()) {
            $_isPlayList = intval(@$_GET['playlists_id']);
            if (empty($_isPlayList)) {
                $videos_id = getVideos_id();
                if (empty($videos_id)) {
                    $_isPlayList = false;
                } else {
                    $v = Video::getVideoLight($videos_id);
                    if (empty($v) || empty($v['serie_playlists_id'])) {
                        $_isPlayList = false;
                    } else {
                        $_isPlayList = $v['serie_playlists_id'];
                    }
                }
            }
        }
    }
    return $_isPlayList;
}

function isVideoOrAudioNotEmbed()
{
    if (!isVideo()) {
        return false;
    }
    $videos_id = getVideos_id();
    if (empty($videos_id)) {
        return false;
    }
    $v = Video::getVideoLight($videos_id);
    if (empty($v)) {
        return false;
    }
    $types = ['audio', 'video'];
    if (in_array($v['type'], $types)) {
        return true;
    }
    return false;
}

function getVideoIDFromURL($url)
{
    if (preg_match("/v=([0-9]+)/", $url, $matches)) {
        return intval($matches[1]);
    }
    if (preg_match('/\/(video|videoEmbed|v|vEmbed|article|articleEmbed)\/([0-9]+)/', $url, $matches)) {
        if (is_numeric($matches[1])) {
            return intval($matches[1]);
        } elseif (is_numeric($matches[2])) {
            return intval($matches[2]);
        }
    }
    if (AVideoPlugin::isEnabledByName('PlayLists')) {
        if (preg_match('/player.php\?playlists_id=([0-9]+)/', $url, $matches)) {
            $serie_playlists_id = intval($matches[1]);
            $video = PlayLists::isPlayListASerie($serie_playlists_id);
            if ($video) {
                return $video['id'];
            }
        }
    }
    if (preg_match("/v=(\.[0-9a-zA-Z_-]+)/", $url, $matches)) {
        return hashToID($matches[1]);
    }
    if (preg_match('/\/(video|videoEmbed|v|vEmbed|article|articleEmbed)\/(\.[0-9a-zA-Z_-]+)/', $url, $matches)) {
        return hashToID($matches[2]);
    }
    return false;
}

function getBackURL()
{
    global $global;
    $backURL = getRedirectUri();
    if (empty($backURL)) {
        $backURL = getRequestURI();
    }
    if (isSameVideoAsSelfURI($backURL)) {
        $backURL = getHomeURL();
    }
    return $backURL;
}

function getHomeURL()
{
    global $global, $advancedCustomUser, $advancedCustom;
    if (isValidURL($advancedCustomUser->afterLoginGoToURL)) {
        return $advancedCustomUser->afterLoginGoToURL;
    } elseif (isValidURL($advancedCustom->logoMenuBarURL) && isSameDomainAsMyAVideo($advancedCustom->logoMenuBarURL)) {
        return $advancedCustom->logoMenuBarURL;
    }
    return $global['webSiteRootURL'];
}

function isValidURL($url)
{
    //var_dump(empty($url), !is_string($url), preg_match("/^http.*/", $url), filter_var($url, FILTER_VALIDATE_URL));
    if (empty($url) || !is_string($url)) {
        return false;
    }
    if (preg_match("/^http.*/", $url) && filter_var($url, FILTER_VALIDATE_URL)) {
        return true;
    }
    return false;
}

function isValidURLOrPath($str, $insideCacheOrTmpDirOnly = true)
{
    global $global;
    //var_dump(empty($url), !is_string($url), preg_match("/^http.*/", $url), filter_var($url, FILTER_VALIDATE_URL));
    if (empty($str) || !is_string($str)) {
        return false;
    }
    if (mb_strtolower(trim($str)) === 'php://input') {
        return true;
    }
    if (isValidURL($str)) {
        return true;
    }
    if (str_starts_with($str, '/') || str_starts_with($str, '../') || preg_match("/^[a-z]:.*/i", $str)) {
        if ($insideCacheOrTmpDirOnly) {
            $absolutePath = realpath($str);
            $absolutePathTmp = realpath(getTmpDir());
            $absolutePathCache = realpath(getCacheDir());
            $ext = mb_strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            if ($ext == 'php') {
                _error_log('isValidURLOrPath return false (is php file) ' . $str);
                return false;
            }

            $pathsToCheck = [$absolutePath, $str];

            foreach ($pathsToCheck as $value) {
                if (
                    str_starts_with($value, $absolutePathTmp) ||
                    str_starts_with($value, '/var/www/') ||
                    str_starts_with($value, $absolutePathCache) ||
                    str_starts_with($value, $global['systemRootPath']) ||
                    str_starts_with($value, getVideosDir())
                ) {
                    return true;
                }
            }
        } else {
            return true;
        }
        //_error_log('isValidURLOrPath return false not valid absolute path 1 ' . $absolutePath);
        //_error_log('isValidURLOrPath return false not valid absolute path 2 ' . $absolutePathTmp);
        //_error_log('isValidURLOrPath return false not valid absolute path 3 ' . $absolutePathCache);
    }
    //_error_log('isValidURLOrPath return false '.$str);
    return false;
}

function hasLastSlash($word)
{
    $word = trim($word);
    return substr($word, -1) === '/';
}

function addLastSlash($word)
{
    $word = trim($word);
    return $word . (hasLastSlash($word) ? "" : "/");
}

function URLHasLastSlash()
{
    return hasLastSlash($_SERVER["REQUEST_URI"]);
}

function ucname($str)
{
    $str = ucwords(mb_strtolower($str));

    foreach (['\'', '-'] as $delim) {
        if (strpos($str, $delim) !== false) {
            $str = implode($delim, array_map('ucfirst', explode($delim, $str)));
        }
    }
    return $str;
}

function sanitize_input($input)
{
    return htmlentities(strip_tags($input));
}

function sanitize_array_item(&$item, $key)
{
    $item = sanitize_input($item);
}

function getSEOComplement($parameters = [])
{
    global $config;

    $allowedTypes = $parameters["allowedTypes"] ?? null;
    $addAutoPrefix = $parameters["addAutoPrefix"] ?? true;
    $addCategory = $parameters["addCategory"] ?? true;

    $parts = [];

    if (!empty($_GET['error'])) {
        array_push($parts, __("Error"));
    }

    if ($addCategory && !empty($_REQUEST['catName'])) {
        array_push($parts, $_REQUEST['catName']);
    }

    if (!empty($_GET['channelName'])) {
        array_push($parts, $_GET['channelName']);
    }

    if (!empty($_GET['type'])) {
        $type = $_GET['type'];
        if (empty($allowedTypes) || in_array(mb_strtolower($type), $allowedTypes)) {
            array_push($parts, __(ucname($type)));
        }
    }

    if (!empty($_GET['showOnly'])) {
        array_push($parts, $_GET['showOnly']);
    }

    if (!empty($_GET['page'])) {
        $page = intval($_GET['page']);
        if ($page > 1) {
            array_push($parts, sprintf(__("Page %d"), $page));
        }
    }

    // Cleaning all entries in the $parts array
    array_walk($parts, 'sanitize_array_item');

    $txt = implode($config->getPageTitleSeparator(), $parts);
    $txt = (!empty($txt) && $addAutoPrefix ? $config->getPageTitleSeparator() : "") . $txt;

    return $txt;
}

function doNOTOrganizeHTMLIfIsPagination()
{
    global $global;
    $page = getCurrentPage();
    if ($page > 1) {
        $global['doNOTOrganizeHTML'] = 1;
    }
}

function getCurrentPage($forceURL = false)
{
    if ($forceURL) {
        resetCurrentPage();
    }
    global $lastCurrent;
    $current = 1;
    if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
        $current = intval($_GET['page']);
        if (!empty($_REQUEST['current']) && $_REQUEST['current'] > $current) {
            $current = intval($_REQUEST['current']);
        }
    } else if (!empty($_REQUEST['current'])) {
        $current = intval($_REQUEST['current']);
    } elseif (!empty($_POST['current'])) {
        $current = intval($_POST['current']);
    } elseif (!empty($_GET['current'])) {
        $current = intval($_GET['current']);
    } elseif (isset($_GET['start']) && isset($_GET['length'])) { // for the bootgrid
        $start = intval($_GET['start']);
        $length = intval($_GET['length']);
        if (!empty($start) && !empty($length)) {
            $current = floor($start / $length) + 1;
        }
    }
    if ($current > 1000 && !User::isLogged()) {
        _error_log("getCurrentPage current>1000 ERROR [{$current}] " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        _error_log("getCurrentPage current>1000 ERROR NOT LOGGED die [{$current}] " . getSelfURI() . ' ' . json_encode($_SERVER));
        exit;
    } else if ($current > 100 && isBot()) {
        //_error_log("getCurrentPage current>100 ERROR [{$current}] " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        //_error_log("getCurrentPage current>100 ERROR bot die [{$current}] " . getSelfURI() . ' ' . json_encode($_SERVER));
        _error_log("getCurrentPage current>100 ERROR bot die [{$current}] " . getSelfURI() . ' ' . $_SERVER['HTTP_USER_AGENT']);
        exit;
    }
    if (isset($_GET['isInfiniteScroll'])) {
        if ($current < $_GET['isInfiniteScroll']) {
            $current = intval($_GET['isInfiniteScroll']);
        }
    }
    $lastCurrent = $current;
    return $current;
}

function getTrendingLimit()
{
    global $advancedCustom;
    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
    }
    $daysLimit = intval($advancedCustom->trendingOnLastDays->value);
    return $daysLimit;
}

function getTrendingLimitDate()
{
    $daysLimit = getTrendingLimit();
    $dateDaysLimit = date('Y-m-d H:i:s', strtotime("-{$daysLimit} days"));
    return $dateDaysLimit;
}


function unsetCurrentPage()
{
    global $_currentPage;
    if (!isset($_currentPage)) {
        $_currentPage = getCurrentPage();
    }
    $_REQUEST['current'] = 1;
    $_POST['current'] = 1;
    $_GET['current'] = 1;
    $_GET['page'] = 1;
}

function resetCurrentPage()
{
    global $_currentPage;
    if (isset($_currentPage)) {
        $_REQUEST['current'] = $_currentPage;
        $_POST['current'] = $_currentPage;
        $_GET['current'] = $_currentPage;
        $_GET['page'] = $_currentPage;
    }
}

function setCurrentPage($current)
{
    $_REQUEST['current'] = intval($current);
}

function getRowCount($default = 1000)
{
    global $global;
    if (!empty($_REQUEST['rowCount'])) {
        $defaultN = intval($_REQUEST['rowCount']);
    } elseif (!empty($_POST['rowCount'])) {
        $defaultN = intval($_POST['rowCount']);
    } elseif (!empty($_GET['rowCount'])) {
        $defaultN = intval($_GET['rowCount']);
    } elseif (!empty($_REQUEST['length'])) {
        $defaultN = intval($_REQUEST['length']);
    } elseif (!empty($_POST['length'])) {
        $defaultN = intval($_POST['length']);
    } elseif (!empty($_GET['length'])) {
        $defaultN = intval($_GET['length']);
    } elseif (!empty($global['rowCount'])) {
        $defaultN = intval($global['rowCount']);
    }
    return (!empty($defaultN) && $defaultN > 0) ? $defaultN : $default;
}

function setRowCount($rowCount)
{
    global $global;
    $_REQUEST['rowCount'] = intval($rowCount);
    $global['rowCount'] = $_REQUEST['rowCount'];
}

function getSearchVar()
{
    $search = '';
    if (!empty($_REQUEST['search'])) {
        $search = $_REQUEST['search'];
    } elseif (!empty($_REQUEST['q'])) {
        $search = $_REQUEST['q'];
    } elseif (!empty($_REQUEST['searchPhrase'])) {
        $search = $_REQUEST['searchPhrase'];
    } elseif (!empty($_REQUEST['search']['value'])) {
        $search = $_REQUEST['search']['value'];
    }
    return mb_strtolower($search);
}

function isSearch()
{
    return !empty(getSearchVar());
}

$cleanSearchHistory = '';

function cleanSearchVar()
{
    global $cleanSearchHistory;
    $search = getSearchVar();
    if (!empty($search)) {
        $cleanSearchHistory = $search;
    }
    $searchIdex = ['q', 'searchPhrase', 'search'];
    foreach ($searchIdex as $value) {
        unset($_REQUEST[$value], $_POST[$value], $_GET[$value]);
    }
}

function reloadSearchVar()
{
    global $cleanSearchHistory;
    $_REQUEST['search'] = $cleanSearchHistory;
    if (empty($_GET['search'])) {
        $_GET['search'] = $cleanSearchHistory;
    }
    if (empty($_POST['search'])) {
        $_POST['search'] = $cleanSearchHistory;
    }
}

function html2plainText($html)
{
    if (!is_string($html)) {
        return '';
    }
    $text = strip_tags($html);
    $text = str_replace(['\\', "\n", "\r", '"'], ['', ' ', ' ', ''], trim($text));
    return $text;
}

function getInputPassword($id, $attributes = 'class="form-control"', $placeholder = '', $autofill = true)
{
    if (empty($placeholder)) {
        $placeholder = __("Password");
    }
    if (!$autofill) {
        $attributes .= ' autocomplete="new-password" autofill="off"  ';
        echo '<input type="password" name="fakepassword" id="fakepassword" style="display:none;">';
    }
?>
    <div class="input-group">
        <span class="input-group-addon"><i class="fas fa-lock"></i></span>
        <input id="<?php echo $id; ?>" name="<?php echo $id; ?>" type="password" placeholder="<?php echo $placeholder; ?>" <?php echo $attributes; ?>>
        <span class="input-group-addon" style="cursor: pointer;" id="toggle_<?php echo $id; ?>" data-toggle="tooltip" data-placement="left" title="<?php echo __('Show/Hide Password'); ?>"><i class="fas fa-eye-slash"></i></span>
    </div>
    <script>
        $(document).ready(function() {
            $('#toggle_<?php echo $id; ?>').click(function() {
                $(this).find('i').toggleClass("fa-eye fa-eye-slash");
                if ($(this).find('i').hasClass("fa-eye")) {
                    $("#<?php echo $id; ?>").attr("type", "text");
                } else {
                    $("#<?php echo $id; ?>").attr("type", "password");
                }
            })
        });
    </script>
<?php
}

function getInputCopyToClipboard($id, $value, $attributes = 'class="form-control" readonly="readonly"', $placeholder = '')
{
    if (strpos($value, '"') !== false) {
        $valueAttr = "value='{$value}'";
    } else {
        $valueAttr = 'value="' . $value . '"';
    }
?>
    <div class="input-group">
        <input id="<?php echo $id; ?>" type="text" placeholder="<?php echo $placeholder; ?>" <?php echo $attributes; ?> <?php echo $valueAttr; ?>>
        <span class="input-group-addon" style="cursor: pointer;" id="copyToClipboard_<?php echo $id; ?>" data-toggle="tooltip" data-placement="left" title="<?php echo __('Copy to Clipboard'); ?>"><i class="fas fa-clipboard"></i></span>
    </div>
    <script>
        var timeOutCopyToClipboard_<?php echo $id; ?>;
        $(document).ready(function() {
            $('#copyToClipboard_<?php echo $id; ?>').click(function() {
                clearTimeout(timeOutCopyToClipboard_<?php echo $id; ?>);
                $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("fa-clipboard");
                $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("text-success");
                $('#copyToClipboard_<?php echo $id; ?>').addClass('bg-success');
                $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("fa-clipboard-check");
                timeOutCopyToClipboard_<?php echo $id; ?> = setTimeout(function() {
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("fa-clipboard-check");
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("text-success");
                    $('#copyToClipboard_<?php echo $id; ?>').removeClass('bg-success');
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("fa-clipboard");
                }, 3000);
                copyToClipboard($('#<?php echo $id; ?>').val());
            });

            // Auto-select the input text when the input is focused or clicked
            $('#<?php echo $id; ?>').on('focus click', function() {
                $(this).select();
            });
        });
    </script>
<?php
}


function getButtontCopyToClipboard($elemToCopyId, $attributes = 'class="btn btn-default btn-sm btn-xs pull-right"', $label = "Copy to Clipboard")
{
    $id = "getButtontCopyToClipboard" . uniqid();
?>
    <button id="<?php echo $id; ?>" <?php echo $attributes; ?> data-toggle="tooltip" data-placement="left" title="<?php echo __($label); ?>"><i class="fas fa-clipboard"></i> <?php echo __($label); ?></button>
    <script>
        var timeOutCopyToClipboard_<?php echo $id; ?>;
        $(document).ready(function() {
            $('#<?php echo $id; ?>').click(function() {
                clearTimeout(timeOutCopyToClipboard_<?php echo $id; ?>);
                $('#<?php echo $id; ?>').find('i').removeClass("fa-clipboard");
                $('#<?php echo $id; ?>').find('i').addClass("text-success");
                $('#<?php echo $id; ?>').addClass('bg-success');
                $('#<?php echo $id; ?>').find('i').addClass("fa-clipboard-check");
                timeOutCopyToClipboard_<?php echo $id; ?> = setTimeout(function() {
                    $('#<?php echo $id; ?>').find('i').removeClass("fa-clipboard-check");
                    $('#<?php echo $id; ?>').find('i').removeClass("text-success");
                    $('#<?php echo $id; ?>').removeClass('bg-success');
                    $('#<?php echo $id; ?>').find('i').addClass("fa-clipboard");
                }, 3000);
                copyToClipboard($('#<?php echo $elemToCopyId; ?>').val());
            })
        });
    </script>
    <?php
    return $id;
}

function examineJSONError($object)
{
    $json = json_encode($object);
    if (json_last_error()) {
        echo "Error 1 Found: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }
    $object = object_to_array($object);
    $json = json_encode($object);
    if (json_last_error()) {
        echo "Error 1 Found after array conversion: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $json = json_encode($object, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        echo "Error 1 Found with JSON_UNESCAPED_UNICODE: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $objectEncoded = $object;

    array_walk_recursive($objectEncoded, function (&$item) {
        if (is_string($item)) {
            $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item, 'UTF-8, ISO-8859-1', true));
        }
    });
    $json = json_encode($objectEncoded);
    if (json_last_error()) {
        echo "Error 2 Found after array conversion: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $json = json_encode($objectEncoded, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        echo "Error 2 Found with JSON_UNESCAPED_UNICODE: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $objectDecoded = $object;

    array_walk_recursive($objectDecoded, function (&$item) {
        if (is_string($item)) {
            $item = mb_convert_encoding($item, mb_detect_encoding($item, 'UTF-8, ISO-8859-1', true), 'UTF-8');
        }
    });
    $json = json_encode($objectDecoded);
    if (json_last_error()) {
        echo "Error 2 Found after array conversion: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    $json = json_encode($objectDecoded, JSON_UNESCAPED_UNICODE);
    if (json_last_error()) {
        echo "Error 2 Found with JSON_UNESCAPED_UNICODE: " . json_last_error_msg() . "<br>" . PHP_EOL;
    } else {
        return __LINE__;
    }

    return false;
}

function getSEODescription($text, $maxChars = 320)
{
    $removeChars = ['|', '"'];
    $replaceChars = ['-', ''];
    $newText = trim(str_replace($removeChars, $replaceChars, html2plainText($text)));
    if (_strlen($newText) <= $maxChars) {
        return $newText;
    } else {
        return _substr($newText, 0, $maxChars - 3) . '...';
    }
}

function getSEOTitle($text, $maxChars = 120)
{
    $removeChars = ['|', '"'];
    $replaceChars = ['-', ''];
    $newText = trim(str_replace($removeChars, $replaceChars, safeString($text)));
    if (_strlen($newText) <= $maxChars) {
        return $newText;
    } else {
        return _substr($newText, 0, $maxChars - 3) . '...';
    }
}

function getShareMenu($title, $permaLink, $URLFriendly, $embedURL, $img, $class = "row bgWhite list-group-item menusDiv", $videoLengthInSeconds = 0, $bitLyLink = '')
{
    global $global, $advancedCustom;

    $varsArray = array(
        'title' => $title,
        'permaLink' => $permaLink,
        'URLFriendly' => $URLFriendly,
        'embedURL' => $embedURL,
        'img' => $img,
        'class' => $class,
        'videoLengthInSeconds' => $videoLengthInSeconds,
        'bitLyLink' => $bitLyLink,
    );
    return getIncludeFileContent($global['systemRootPath'] . 'objects/functiongetShareMenu.php', $varsArray);
    //include $global['systemRootPath'] . 'objects/functiongetShareMenu.php';
}

function getShareSocialIcons($title, $url)
{
    global $global;

    $varsArray = array(
        'title' => $title,
        'url' => $url,
    );
    return getIncludeFileContent($global['systemRootPath'] . 'view/include/social.php', $varsArray);
    //include $global['systemRootPath'] . 'objects/functiongetShareMenu.php';
}


function getCaptcha($uid = "", $forceCaptcha = false)
{
    global $global;
    if (empty($uid)) {
        $uid = "capcha_" . uniqid();
    }
    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functiongetCaptcha.php', ['uid' => $uid, 'forceCaptcha' => $forceCaptcha]);

    $result = [
        'style' => '',
        'html' => '',
        'script' => ''
    ];

    // Match the style block
    preg_match('/<style>(.*?)<\/style>/s', $contents, $styleMatch);
    if (!empty($styleMatch[1])) {
        $result['style'] = trim($styleMatch[1]);
    }

    // Match the HTML block
    preg_match('/<div.*?<\/div>/s', $contents, $htmlMatch);
    if (!empty($htmlMatch[0])) {
        $result['html'] = trim($htmlMatch[0]);
    }

    // Match the script block
    preg_match('/<script>(.*?)<\/script>/s', $contents, $scriptMatch);
    if (!empty($scriptMatch[1])) {
        $result['script'] = trim($scriptMatch[1]);
    }

    return [
        'content' => $contents,
        'btnReloadCapcha' => "$('#btnReload{$uid}').trigger('click');",
        'captchaText' => "$('#{$uid}Text').val()",
        'html' => $result['html'],
        'script' => $result['script'],
        'style' => $result['style']
    ];
}

function isShareEnabled()
{
    global $global, $advancedCustom;
    return empty($advancedCustom->disableShareOnly) && empty($advancedCustom->disableShareAndPlaylist);
}

function getSharePopupButton($videos_id, $url = '', $title = '', $class = '')
{
    global $global, $advancedCustom;
    if (!isShareEnabled()) {
        return false;
    }
    $video['id'] = $videos_id;
    include $global['systemRootPath'] . 'view/include/socialModal.php';
}

function getContentType()
{
    $contentType = '';
    $headers = headers_list(); // get list of headers
    foreach ($headers as $header) { // iterate over that list of headers
        if (stripos($header, 'Content-Type:') !== false) { // if the current header has the string "Content-Type" in it
            $headerParts = explode(':', $header); // split the string, getting an array
            $headerValue = trim($headerParts[1]); // take second part as value
            $contentType = $headerValue;
            break;
        }
    }
    return $contentType;
}

function isContentTypeJson()
{
    $contentType = getContentType();
    return preg_match('/json/i', $contentType);
}

function isContentTypeXML()
{
    $contentType = getContentType();
    return preg_match('/xml/i', $contentType);
}

function successJsonMessage($message)
{
    $obj = new stdClass();
    $obj->error = false;
    $obj->msg = $message;
    header('Content-Type: application/json');
    die(json_encode($obj));
}

function diskUsageBars()
{
    return ''; //TODO check why it is slowing down
    global $global;
    include $global['systemRootPath'] . 'objects/functiondiskUsageBars.php';
    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functiondiskUsageBars.php');
    return $contents;
}

function getDomain()
{
    global $global, $_getDomain;

    if (isset($_getDomain)) {
        return $_getDomain;
    }

    if (empty($_SERVER['HTTP_HOST'])) {
        $parse = parse_url($global['webSiteRootURL']);
        $domain = $parse['host'];
    } else {
        $domain = $_SERVER['HTTP_HOST'];
    }
    $domain = str_replace("www.", "", $domain);
    $domain = preg_match("/^\..+/", $domain) ? ltrim($domain, '.') : $domain;
    $domain = preg_replace('/:[0-9]+$/', '', $domain);
    $_getDomain = $domain;
    return $domain;
}

function getHostOnlyFromURL($url)
{
    $parse = parse_url($url);
    $domain = $parse['host'];
    $domain = str_replace("www.", "", $domain);
    $domain = preg_match("/^\..+/", $domain) ? ltrim($domain, '.') : $domain;
    $domain = preg_replace('/:[0-9]+$/', '', $domain);
    return $domain;
}

/**
 * This function is not 100% but try to tell if the site is in an iFrame
 * @global array $global
 * @return boolean
 */
function isIframeInDifferentDomain()
{
    global $global;
    if (!isIframe()) {
        return false;
    }
    return isSameDomainAsMyAVideo($_SERVER['HTTP_REFERER']);
}

function isIframe()
{
    global $global;
    if (!empty($global['isIframe'])) {
        return true;
    }

    if (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'iframe') {
        return true;
    }

    $pattern = '/' . str_replace('/', '\\/', $global['webSiteRootURL']) . '((view|site)\/?)?/';
    if (empty($_SERVER['HTTP_REFERER']) || preg_match($pattern, $_SERVER['HTTP_REFERER'])) {
        return false;
    }
    return true;
}

function isInfiniteScroll()
{
    return !empty($_GET['isInfiniteScroll']);
}

function inIframe()
{
    return isIframe();
}

function getCredentialsURL()
{
    global $global;
    return "webSiteRootURL=" . urlencode($global['webSiteRootURL']) . "&user=" . urlencode(User::getUserName()) . "&pass=" . urlencode(User::getUserPass()) . "&encodedPass=1";
}

function gotToLoginAndComeBackHere($msg = '')
{
    global $global;
    if (User::isLogged()) {
        forbiddenPage($msg);
        exit;
    }
    if (!empty($_GET['comebackhere'])) {
        return false;
    }
    setAlertMessage($msg, $type = "msg");
    $url = "{$global['webSiteRootURL']}user?redirectUri=" . urlencode(getSelfURI());
    $url = addQueryStringParameter($url, 'comebackhere', 1);
    _error_log("gotToLoginAndComeBackHere($msg) " . getRealIpAddr() . ' ' . json_encode(debug_backtrace()));
    header("Location: {$url}");
    exit;
}

function setAlertMessage($msg, $type = "msg")
{
    _session_start();
    $_SESSION['YPTalertMessage'][] = [$msg, $type];
}

function setToastMessage($msg)
{
    setAlertMessage($msg, "toast");
}

function showAlertMessage()
{
    $check = ['error', 'msg', 'success', 'toast'];

    $newAlerts = [];

    if (!empty($_SESSION['YPTalertMessage'])) {
        foreach ($check as $value) {
            $newAlerts[$value] = [];
        }
        foreach ($_SESSION['YPTalertMessage'] as $value) {
            if (!empty($value[0])) {
                if (empty($newAlerts[$value[1]])) {
                    $newAlerts[$value[1]] = [];
                }
                $newAlerts[$value[1]][] = $value[0];
            }
        }
        _session_start();
        unset($_SESSION['YPTalertMessage']);
    } else {
        if (!requestComesFromSafePlace()) {
            echo PHP_EOL, "/** showAlertMessage !requestComesFromSafePlace [" . getRefferOrOrigin() . "] **/";
            return false;
        }
    }

    $joinString = $check;
    foreach ($joinString as $value) {
        if (!empty($newAlerts[$value])) {
            if (is_array($newAlerts[$value])) {
                $newAlerts[$value] = array_unique($newAlerts[$value]);
                $newStr = [];
                foreach ($newAlerts[$value] as $value2) {
                    if (!empty($value2)) {
                        $newStr[] = $value2;
                    }
                }
                $newAlerts[$value] = implode("<br>", $newStr);
            } else {
                $newAlerts[$value] = $newAlerts[$value];
            }
        }
    }

    foreach ($check as $value) {
        if (!empty($newAlerts[$value])) {
            if (is_array($newAlerts[$value])) {
                $newStr = [];
                foreach ($newAlerts[$value] as $key => $value2) {
                    $value2 = str_replace('"', "''", $value2);
                    if (!empty($value2)) {
                        $newStr[] = $value2;
                    }
                }
                $newAlerts[$value] = $newStr;
            } else {
                $newAlerts[$value] = str_replace('"', "''", $newAlerts[$value]);
            }
        }
    }
    echo "/** showAlertMessage **/", PHP_EOL;
    if (!empty($newAlerts['error'])) {
        echo 'avideoAlertError("' . $newAlerts['error'] . '");';
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    if (!empty($newAlerts['msg'])) {
        echo 'avideoAlertInfo("' . $newAlerts['msg'] . '");';
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    if (!empty($newAlerts['success'])) {
        echo 'avideoAlertSuccess("' . $newAlerts['success'] . '");';
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    if (!empty($newAlerts['toast'])) {
        if (!is_array($newAlerts['toast'])) {
            $newAlerts['toast'] = [$newAlerts['toast']];
        } else {
            $newAlerts['toast'] = array_unique($newAlerts['toast']);
        }
        foreach ($newAlerts['toast'] as $key => $value) {
            $hideAfter = strlen(strip_tags($value)) * 150;

            if ($hideAfter < 3000) {
                $hideAfter = 3000;
            }
            if ($hideAfter > 15000) {
                $hideAfter = 15000;
            }

            echo '$.toast({
                    text: "' . strip_tags($value) . '",
                    hideAfter: ' . $hideAfter . '   // in milli seconds
                });console.log("Toast Hide after ' . $hideAfter . '");';
        }
        echo 'window.history.pushState({}, document.title, "' . getSelfURI() . '");';
    }
    echo PHP_EOL, "/** showAlertMessage END **/";
}

function getResolutionLabel($res)
{
    if ($res == 720) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 1080) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 1440) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 2160) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } elseif ($res == 4320) {
        return "<span class='label label-danger' style='padding: 0 2px; font-size: .8em; display: inline;'>" . getResolutionText($res) . "</span>";
    } else {
        return '';
    }
}

function getResolutionText($res)
{
    $res = intval($res);
    if ($res >= 720 && $res < 1080) {
        return "HD";
    } elseif ($res >= 1080 && $res < 1440) {
        return "FHD";
    } elseif ($res >= 1440 && $res < 2160) {
        return "FHD+";
    } elseif ($res >= 2160 && $res < 4320) {
        return "4K";
    } elseif ($res >= 4320) {
        return "8K";
    } else {
        return '';
    }
}

function getResolutionTextRoku($res)
{
    $res = intval($res);
    if ($res >= 720 && $res < 1080) {
        return "HD";
    } elseif ($res >= 1080 && $res < 2160) {
        return "FHD";
    } elseif ($res >= 2160) {
        return "UHD";
    } else {
        return 'SD';
    }
}

function getValidFormats()
{
    $video = ['webm', 'mp4', 'm3u8'];
    $audio = ['mp3', 'ogg'];
    $image = ['jpg', 'gif', 'webp'];
    return array_merge($video, $audio, $image);
}

function isValidFormats($format)
{
    $format = str_replace(".", "", $format);
    return in_array($format, getValidFormats());
}

function getTimerFromDates($startTime, $endTime = 0)
{
    if (!is_int($startTime)) {
        $startTime = strtotime($startTime);
    }
    if (!is_int($endTime)) {
        $endTime = strtotime($endTime);
    }
    if (empty($endTime)) {
        $endTime = time();
    }
    $timer = abs($endTime - $startTime);
    $uid = uniqid();
    return "<span id='{$uid}'></span><script>$(document).ready(function () {startTimer({$timer}, '#{$uid}', '');})</script>";
}

function getServerClock()
{
    $id = uniqid();
    $today = getdate();
    $html = '<span id="' . $id . '">00:00:00</span>';
    $html .= "<script type=\"text/javascript\">
    $(document).ready(function () {
        var d = new Date({$today['year']},{$today['mon']},{$today['mday']},{$today['hours']},{$today['minutes']},{$today['seconds']});
        setInterval(function() {
            d.setSeconds(d.getSeconds() + 1);
            $('#{$id}').text((d.getHours() +':' + d.getMinutes() + ':' + d.getSeconds() ));
        }, 1000);
    });
</script>";
    return $html;
}

/**
 * Xsendfile and FFMPEG are required for this feature
 * @global array $global
 * @param string $filepath
 * @return boolean
 */
function downloadHLS($filepath)
{
    global $global;

    if (!CustomizeUser::canDownloadVideos()) {
        _error_log("downloadHLS: CustomizeUser::canDownloadVideos said NO");
        return false;
    }

    if (!file_exists($filepath)) {
        _error_log("downloadHLS: file NOT found: {$filepath}");
        return false;
    }
    _error_log("downloadHLS: m3u8ToMP4($filepath) start");
    $output = m3u8ToMP4($filepath);

    if (!empty($output['error'])) {
        $msg = 'downloadHLS was not possible';
        if (User::isAdmin()) {
            $msg .= '<br>' . "m3u8ToMP4($filepath) return empty<br>" . nl2br($output['msg']);
        }
        _error_log("downloadHLS: m3u8ToMP4($filepath) return empty");
        die($msg);
    }

    $outputpath = $output['path'];
    $outputfilename = $output['filename'];

    if (!empty($_REQUEST['title'])) {
        $quoted = sprintf('"%s"', addcslashes(basename($_REQUEST['title']), '"\\'));
    } elseif (!empty($_REQUEST['file'])) {
        $quoted = sprintf('"%s"', addcslashes(basename($_REQUEST['file']), '"\\')) . ".mp4";
    } else {
        $quoted = $outputfilename;
    }

    _error_log("downloadHLS: filepath=($filepath) outputpath={$outputpath}");
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . $quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header("X-Sendfile: {$outputpath}");
    exit;
}

function playHLSasMP4($filepath)
{
    global $global;

    if (!CustomizeUser::canDownloadVideos()) {
        _error_log("playHLSasMP4: CustomizeUser::canDownloadVideos said NO");
        return false;
    }

    if (!file_exists($filepath)) {
        _error_log("playHLSasMP4: file NOT found: {$filepath}");
        return false;
    }
    $output = m3u8ToMP4($filepath);

    if (!empty($output['error'])) {
        $msg = 'playHLSasMP4 was not possible';
        if (User::isAdmin()) {
            $msg .= '<br>' . "m3u8ToMP4($filepath) return empty<br>" . nl2br($output['msg']);
        }
        die($msg);
    }

    $outputpath = $output['path'];

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Content-type: video/mp4');
    header('Content-Length: ' . filesize($outputpath));
    header("X-Sendfile: {$outputpath}");
    exit;
}

function getSocialModal($videos_id, $url = "", $title = "")
{
    global $global;
    $video['id'] = $videos_id;
    $sharingUid = uniqid();
    $filePath = $global['systemRootPath'] . 'objects/functionGetSocialModal.php';
    $contents = getIncludeFileContent(
        $filePath,
        [
            'videos_id' => $videos_id,
            'url' => $url,
            'title' => $title,
            'video' => $video,
            'sharingUid' => $sharingUid
        ]
    );
    return ['html' => $contents, 'id' => $sharingUid];
}

function getPHP()
{
    global $global;
    if (!empty($global['php'])) {
        $php = $global['php'];
        if (file_exists($php)) {
            return $php;
        }
    }
    $php = PHP_BINDIR . "/php";
    if (file_exists($php)) {
        return $php;
    }
    return get_php();
}

function get_php()
{
    return getPHP();
}

function isHTMLPage($url)
{
    if (preg_match('/https?:\/\/(www\.)?(youtu.be|youtube.com|vimeo.com|bitchute.com)\//i', $url)) {
        return true;
    } elseif ($type = getHeaderContentTypeFromURL($url)) {
        if (preg_match('/text\/html/i', $type)) {
            return true;
        }
    }
    return false;
}

function url_exists($url)
{
    global $global;
    if (preg_match('/^https?:\/\//i', $url)) {
        $parts = explode('/videos/', $url);
        if (!empty($parts[1])) {
            $tryFile = "{$global['systemRootPath']}videos/{$parts[1]}";
            //_error_log("try_get_contents_from_local {$url} => {$tryFile}");
            if (file_exists($tryFile)) {
                return $tryFile;
            }
        }
        $file_headers = get_headers($url);
        if (empty($file_headers)) {
            _error_log("url_exists($url) empty headers");
            return false;
        } else {
            foreach ($file_headers as $value) {
                if (preg_match('/404 Not Found/i', $value)) {
                    _error_log("url_exists($url) 404 {$value}");
                    return false;
                }
            }
            return true;
        }
    } else {
        $exists = file_exists($url);
        if ($exists == false) {
            _error_log("url_exists($url) local file do not exists");
        }
        return $exists;
    }
}

function getHeaderContentTypeFromURL($url)
{
    if (isValidURL($url) && $type = get_headers($url, 1)["Content-Type"]) {
        return $type;
    }
    return false;
}

function canFullScreen()
{
    global $doNotFullScreen;
    if (!empty($doNotFullScreen) || isSerie() || !isVideo()) {
        return false;
    }
    return true;
}

function getTinyMCE($id, $simpleMode = false, $allowAttributes = false, $allowCSS = false, $allowAllTags = false)
{
    global $global;
    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functionsGetTinyMCE.php', [
        'id' => $id,
        'simpleMode' => $simpleMode,
        'allowAttributes' => $allowAttributes,
        'allowCSS' => $allowCSS,
        'allowAllTags' => $allowAllTags
    ]);
    return $contents;
}

function pathToRemoteURL($filename, $forceHTTP = false, $ignoreCDN = false)
{
    global $pathToRemoteURL, $global;
    if (!isset($pathToRemoteURL)) {
        $pathToRemoteURL = [];
    }

    if (isset($pathToRemoteURL[$filename])) {
        return $pathToRemoteURL[$filename];
    }
    if (!file_exists($filename) || filesize($filename) < 1000) {
        $fileName = getFilenameFromPath($filename);
        //var_dump($fileName);exit;
        if ($yptStorage = AVideoPlugin::loadPluginIfEnabled("YPTStorage")) {
            $source = $yptStorage->getAddress("{$fileName}");
            $url = $source['url'];
        } elseif (!preg_match('/index.m3u8$/', $filename)) {
            if ($aws_s3 = AVideoPlugin::loadPluginIfEnabled("AWS_S3")) {
                $source = $aws_s3->getAddress("{$fileName}");
                $url = $source['url'];
                if (empty($ignoreCDN)) {
                    $url = replaceCDNIfNeed($url, 'CDN_S3');
                } elseif (!empty($source['url_noCDN'])) {
                    $url = $source['url_noCDN'];
                }
            } elseif ($bb_b2 = AVideoPlugin::loadPluginIfEnabled("Blackblaze_B2")) {
                $source = $bb_b2->getAddress("{$fileName}");
                $url = $source['url'];
                if (empty($ignoreCDN)) {
                    $url = replaceCDNIfNeed($url, 'CDN_B2');
                } elseif (!empty($source['url_noCDN'])) {
                    $url = $source['url_noCDN'];
                }
            } elseif ($ftp = AVideoPlugin::loadPluginIfEnabled("FTP_Storage")) {
                $source = $ftp->getAddress("{$fileName}");
                $url = $source['url'];
                //var_dump($source,$fileName, $filename);exit;
                if (empty($ignoreCDN)) {
                    $url = replaceCDNIfNeed($url, 'CDN_FTP');
                } elseif (!empty($source['url_noCDN'])) {
                    $url = $source['url_noCDN'];
                }
            }
        }
    }
    if (empty($url)) {
        if ($forceHTTP) {
            $paths = Video::getPaths($filename);
            //$url = str_replace(getVideosDir(), getCDN() . "videos/", $filename);
            if (empty($ignoreCDN)) {
                $url = getCDN() . "{$paths['relative']}";
            } else {
                $url = "{$global['webSiteRootURL']}{$paths['relative']}";
            }
            if (preg_match('/index.m3u8$/', $filename) && !preg_match('/index.m3u8$/', $url)) {
                $url .= 'index.m3u8';
            }
        } else {
            $url = $filename;
        }
    }

    //$url = str_replace(array($global['systemRootPath'], '/videos/videos/'), array("", '/videos/'), $url);

    $pathToRemoteURL[$filename] = $url;
    return $url;
}

function pathOrURLToValidURL($filenameOrURL)
{
    global $global;
    $videosURL = "{$global['webSiteRootURL']}videos/";
    $videosPath = getVideosDir();
    $relativePath = '';
    $filePath = '';

    $parts = explode('?', $filenameOrURL);
    $filenameOrURL = $parts[0];

    $defaultURL = $filenameOrURL;
    if (strpos($filenameOrURL, $videosURL) === 0) {
        $relativePath = str_replace($videosURL, '', $filenameOrURL);
        $filePath = "{$videosPath}{$relativePath}";
        $defaultURL = getCDN() . 'videos/'.$relativePath;
    }else if(file_exists($filenameOrURL)){
        $relativePath = str_replace($videosPath, '', $filenameOrURL);
        $filePath = $filenameOrURL;
        $defaultURL = getCDN() . 'videos/'.$relativePath;
    }else if(file_exists("{$global['systemRootPath']}{$filenameOrURL}")){
        $relativePath = str_replace('videos/', '', $filenameOrURL);
        $filePath = "{$global['systemRootPath']}videos/{$relativePath}";
        $defaultURL = getCDN() . 'videos/'.$relativePath;
    }else if(file_exists("{$videosPath}{$filenameOrURL}")){
        $relativePath = "{$filenameOrURL}";
        $filePath = "{$global['systemRootPath']}videos/{$relativePath}";
        $defaultURL = getCDN() . 'videos/'.$relativePath;
    }

    if (file_exists($filePath)) {
        if (!isDummyFile($filePath)) {
            return $defaultURL;
        }

        $obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
        if (!empty($obj) && $obj->enable_storage) {
            $pz = CDNStorage::getPZ();
            return "https://{$pz}{$relativePath}";
        }
        $yptStorage = AVideoPlugin::loadPluginIfEnabled("YPTStorage");
        if (!empty($yptStorage)) {
            $source = $yptStorage->getAddress($relativePath);
            if(!empty($source['url'])){
                return $source['url'];
            }
        }

        if (!preg_match('/index.m3u8$/', $filePath)) {
            if ($aws_s3 = AVideoPlugin::loadPluginIfEnabled("AWS_S3")) {
                $source = $aws_s3->getAddress("{$filePath}");
                if(!empty($source['url'])){
                    return $source['url'];
                }
            } elseif ($bb_b2 = AVideoPlugin::loadPluginIfEnabled("Blackblaze_B2")) {
                $source = $bb_b2->getAddress("{$filePath}");
                if(!empty($source['url'])){
                    return $source['url'];
                }
            } elseif ($ftp = AVideoPlugin::loadPluginIfEnabled("FTP_Storage")) {
                $source = $ftp->getAddress("{$filePath}");
                if(!empty($source['url'])){
                    return $source['url'];
                }
            }
        }
    }

    return $defaultURL;
}

function getFilenameFromPath($path)
{
    global $global;
    $fileName = Video::getCleanFilenameFromFile($path);
    return $fileName;
}

function showCloseButton()
{
    global $global, $showCloseButtonIncluded;
    if (!empty($showCloseButtonIncluded)) {
        return '<!-- showCloseButton is already included -->';
    }
    if (isSerie()) {
        return '<!-- showCloseButton is a serie -->';
    }

    if (!isLive() && $obj = AVideoPlugin::getDataObjectIfEnabled("Gallery")) {
        if (!empty($obj->playVideoOnFullscreen)) {
            $_REQUEST['showCloseButton'] = 1;
        }
    }
    if (isLive() && $obj = AVideoPlugin::getDataObjectIfEnabled("Live")) {
        if (!empty($obj->playLiveInFullScreen)) {
            $_REQUEST['showCloseButton'] = 1;
        }
    }
    if (!empty($_REQUEST['showCloseButton'])) {
        $showCloseButtonIncluded = 1;
        include $global['systemRootPath'] . 'view/include/youtubeModeOnFullscreenCloseButton.php';
    }
    return '<!-- showCloseButton finished -->';
}

function getThemes()
{
    global $_getThemes, $global;
    if (isset($_getThemes)) {
        return $_getThemes;
    }
    $_getThemes = [];
    foreach (glob("{$global['systemRootPath']}view/css/custom/*.css") as $filename) {
        $fileEx = basename($filename, ".css");
        $_getThemes[] = $fileEx;
    }
    return $_getThemes;
}


function getThemesSeparated()
{
    global $_getThemes, $global;
    if (isset($_getThemes)) {
        return $_getThemes;
    }
    $_getThemesLight = [];
    $_getThemesDark = [];
    foreach (glob("{$global['systemRootPath']}view/css/custom/*.css") as $filename) {
        $fileEx = basename($filename, ".css");
        if (in_array($fileEx, AVideoConf::DARKTHEMES)) {
            $_getThemesDark[] = $fileEx;
        } else {
            $_getThemesLight[] = $fileEx;
        }
    }
    return array('light' => $_getThemesLight, 'dark' => $_getThemesDark);
}


function isCurrentThemeDark()
{
    global $config;
    $isDefaultThemeDark = $config->isDefaultThemeDark();
    if (empty($_COOKIE['themeMode'])) {
        // it is default theme
        if ($isDefaultThemeDark) {
            return  true;
        } else {
            return  false;
        }
    } else {
        // it is alrernative theme
        if (!$isDefaultThemeDark) {
            return  true;
        } else {
            return  false;
        }
    }
}

function getBodyClass()
{
    $bodyClass = 'lightTheme';
    if (isCurrentThemeDark()) {
        $bodyClass = 'darkTheme';
    }
    return $bodyClass;
}

function getCurrentTheme()
{
    global $config;
    if (!empty($_REQUEST['customCSS'])) {
        _setcookie('customCSS', $_REQUEST['customCSS']);
        return $_REQUEST['customCSS'];
    }
    if (!empty($_COOKIE['customCSS'])) {
        return $_COOKIE['customCSS'];
    }

    if (!empty($_COOKIE['themeMode'])) {
        return $config->getAlternativeTheme();
    } else {
        return  $config->getDefaultTheme();
    }
}

function isWindowsServer()
{
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function isWindows()
{
    global $global;
    // Check if the HTTP_USER_AGENT is set
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        // Look for 'Windows' in the user agent string
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'Windows') !== false) {
            return true;
        }
    }
    return !empty($global['isWindows']);
}

function isURL200($url, $forceRecheck = false)
{
    global $_isURL200;
    $name = "isURL200" . DIRECTORY_SEPARATOR . md5($url);
    if (empty($forceRecheck)) {
        $result = ObjectYPT::getCacheGlobal($name, 30);
        if (!empty($result)) {
            $object = _json_decode($result);
            return $object->result;
        }
    }


    $object = new stdClass();
    $object->url = $url;
    $object->forceRecheck = $forceRecheck;

    //error_log("isURL200 checking URL {$url}");
    $headers = @get_headers($url);
    if (!is_array($headers)) {
        $headers = [$headers];
    }

    //error_log('isURL200: '.json_encode($headers));
    $object->contentLenght = null;
    $object->result = false;
    foreach ($headers as $value) {
        if (
            strpos($value, '200') ||
            strpos($value, '302') ||
            strpos($value, '304')
        ) {
            $object->result = true;
            break;
        } else {
            //_error_log('isURL200: '.$value);
        }
    }
    if ($object->result) {
        foreach ($headers as $value) {
            if (preg_match('/Content-Length: ?([0-9]+)/', $value, $matches)) {
                $object->contentLenght = $matches[1];
                break;
            } else {
                //_error_log('isURL200: '.$value);
            }
        }
    }

    ObjectYPT::setCacheGlobal($name, json_encode($object));

    if ($object->contentLenght === null) {
        return $object->result;
    } else {
        return $object->contentLenght;
    }
}

function isURL200Clear()
{
    $tmpDir = ObjectYPT::getCacheDir();
    $cacheDir = $tmpDir . "isURL200" . DIRECTORY_SEPARATOR;
    _error_log('isURL200Clear: ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
    rrmdir($cacheDir);
}

function deleteStatsNotifications($clearFirstPage = false)
{
    Live::deleteStatsCache($clearFirstPage);
    $cacheHandler = new LiveCacheHandler();
    $cacheHandler->deleteCache();
}

function getLiveVideosFromUsers_id($users_id)
{
    $videos = [];
    if (!empty($users_id)) {
        $stats = getStatsNotifications();
        foreach ($stats["applications"] as $key => $value) {
            if (empty($value['users_id']) || $users_id != $value['users_id']) {
                if (!empty($_REQUEST['debug'])) {
                    _error_log("getLiveVideosFromUsers_id($users_id) != {$value['users_id']}");
                }
                continue;
            }
            $videos[] = getLiveVideosObject($value);
        }
    }
    //var_dump($videos);exit;
    return $videos;
}

function getLiveVideosObject($application)
{
    foreach ($application as $key => $application2) {
        if (preg_match('/^html/i', $key)) {
            unset($application[$key]);
        }
    }
    $description = '';
    if (!empty($application['liveLinks_id'])) {
        $ll = new LiveLinksTable($application['liveLinks_id']);

        $m3u8 = $ll->getLink();
        $description = $ll->getDescription();
    } elseif (!empty($application['key'])) {
        $m3u8 = Live::getM3U8File($application['key']);
        $lt = LiveTransmition::getFromKey($application['key']);
        $description = $lt['description'];
    } else {
        $m3u8 = '';
    }

    $user = new User($application['users_id']);
    $cat = new Category($application['categories_id']);
    $video = [
        'id' => intval(rand(999999, 9999999)),
        'isLive' => 1,
        'categories_id' => $application['categories_id'],
        'description' => $description,
        'user' => $user->getUser(),
        'name' => $user->getName(),
        'email' => $user->getEmail(),
        'isAdmin' => $user->getIsAdmin(),
        'photoURL' => $user->getPhotoURL(),
        'canStream' => $user->getCanStream(),
        'canUpload' => $user->getCanUpload(),
        'channelName' => $user->getChannelName(),
        'emailVerified' => $user->getEmailVerified(),
        'views_count' => 0,
        'rrating' => "",
        'users_id' => $application['users_id'],
        'type' => 'ready',
        'title' => $application['title'],
        'clean_title' => cleanURLName($application['title']),
        'poster' => @$application['poster'],
        'thumbsJpgSmall' => @$application['poster'],
        'href' => @$application['href'],
        'link' => @$application['link'],
        'imgGif' => @$application['imgGif'],
        'className' => @$application['className'],
        'galleryCallback' => @$application['callback'],
        'stats' => $application,
        'embedlink' => addQueryStringParameter($application['href'], 'embed', 1),
        'images' => [
            "poster" => @$application['poster'],
            "posterPortrait" => @$application['poster'],
            "posterPortraitPath" => @$application['poster'],
            "posterPortraitThumbs" => @$application['poster'],
            "posterPortraitThumbsSmall" => @$application['poster'],
            "thumbsGif" => @$application['imgGif'],
            "gifPortrait" => @$application['imgGif'],
            "thumbsJpg" => @$application['poster'],
            "thumbsJpgSmall" => @$application['poster'],
            "spectrumSource" => false,
            "posterLandscape" => @$application['poster'],
            "posterLandscapePath" => @$application['poster'],
            "posterLandscapeThumbs" => @$application['poster'],
            "posterLandscapeThumbsSmall" => @$application['poster']
        ],
        'videos' => [
            "m3u8" => [
                "url" => $m3u8,
                "url_noCDN" => $m3u8,
                "type" => "video",
                "format" => "m3u8",
                "resolution" => "auto"
            ]
        ],
        'Poster' => @$application['poster'],
        'Thumbnail' => @$application['poster'],
        'createdHumanTiming' => 'Live',
        "videoLink" => "",
        "next_videos_id" => null,
        "isSuggested" => 0,
        "trailer1" => "",
        "trailer2" => "",
        "trailer3" => "",
        "total_seconds_watching" => 0,
        "duration" => 'Live',
        "type" => 'Live',
        "duration_in_seconds" => 0,
        "likes" => 0,
        "dislikes" => 0,
        "users_id_company" => null,
        "iconClass" => $cat->getIconClass(),
        "category" => $cat->getName(),
        "clean_category" => $cat->getClean_name(),
        "category_description" => $cat->getDescription(),
        "videoCreation" => date('Y-m-d H:i:s'),
        "videoModified" => date('Y-m-d H:i:s'),
        "groups" => [],
        "tags" => [],
        "videoTags" => [
            [
                "type_name" => "Starring",
                "name" => ""
            ],
            [
                "type_name" => "Language",
                "name" => "English"
            ],
            [
                "type_name" => "Release_Date",
                "name" => date('Y')
            ],
            [
                "type_name" => "Running_Time",
                "name" => ""
            ],
            [
                "type_name" => "Genres",
                "name" => $cat->getName()
            ]
        ],
        "videoTagsObject" => ['Starring' => [], 'Language' => ["English"], 'Release_Date' => [date('Y')], 'Running_Time' => ['0'], 'Genres' => [$cat->getName()]],
        'descriptionHTML' => '',
        "progress" => [
            "percent" => 0,
            "lastVideoTime" => 0
        ],
        "isFavorite" => null,
        "isWatchLater" => null,
        "favoriteId" => null,
        "watchLaterId" => null,
        "total_seconds_watching_human" => "",
        "views_count_short" => "",
        "identification" => $user->getNameIdentificationBd(),
        "UserPhoto" => $user->getPhotoURL(),
        "isSubscribed" => true,
        "subtitles" => [],
        "subtitlesSRT" => [],
        "comments" => [],
        "commentsTotal" => 0,
        "subscribers" => 1,
        'relatedVideos' => [],
        "wwbnURL" => @$application['href'],
        "wwbnEmbedURL" => addQueryStringParameter($application['href'], 'embed', 1),
        "wwbnImgThumbnail" => @$application['poster'],
        "wwbnImgPoster" => @$application['poster'],
        "wwbnTitle" => $application['title'],
        "wwbnDescription" => '',
        "wwbnChannelURL" => $user->getChannelLink(),
        "wwbnImgChannel" => $user->getPhoto(),
        "wwbnType" => "live",
    ];
    //var_dump($videos);exit;
    return $video;
}

function getLiveVideosFromCategory($categories_id)
{
    $stats = getStatsNotifications();
    $videos = [];
    if (!empty($categories_id)) {
        foreach ($stats["applications"] as $key => $value) {
            if (empty($value['categories_id']) || $categories_id != $value['categories_id']) {
                continue;
            }
            $videos[] = getLiveVideosObject($value);
        }
    }
    //var_dump($videos);exit;
    return $videos;
}

function getStatsNotifications($force_recreate = false, $listItIfIsAdminOrOwner = true)
{
    $timeName = "stats.json.php getStatsNotifications";
    TimeLogStart($timeName);
    global $__getStatsNotifications__;
    $isLiveEnabled = AVideoPlugin::isEnabledByName('Live');
    $cacheHandler = new LiveCacheHandler();
    unset($_POST['sort']);
    if ($force_recreate || !empty($_REQUEST['debug'])) {
        if ($isLiveEnabled) {
            deleteStatsNotifications();
            TimeLogEnd($timeName, __LINE__);
        }
    } else {
        if (!empty($__getStatsNotifications__)) {
            return $__getStatsNotifications__;
        }
        //$pobj = AVideoPlugin::getDataObject("Live");
    }
    TimeLogEnd($timeName, __LINE__);
    if ($isLiveEnabled) {
        if (!empty($_REQUEST['debug'])) {
            _error_log('getStatsNotifications: 1 ' . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));
        }
        $json = Live::getStats();
        $json = object_to_array($json);
        // make sure all the applications are listed on the same array, even from different live servers
        if (empty($json['applications']) && is_array($json)) {
            $oldjson = $json;
            $json = [];
            $json['applications'] = [];
            $json['hidden_applications'] = [];
            foreach ($oldjson as $key => $value) {
                if (!empty($value['applications'])) {
                    $json['applications'] = array_merge($json['applications'], $value['applications']);
                }
                if (!empty($value['hidden_applications'])) {
                    $json['hidden_applications'] = array_merge($json['hidden_applications'], $value['hidden_applications']);
                }
                unset($json[$key]);
            }
            TimeLogEnd($timeName, __LINE__);
        }

        TimeLogEnd($timeName, __LINE__);
        $appArray = AVideoPlugin::getLiveApplicationArray();
        TimeLogEnd($timeName, __LINE__);
        if (!empty($appArray)) {
            if (empty($json)) {
                $json = [];
            }
            $json['error'] = false;
            if (empty($json['msg'])) {
                $json['msg'] = "OFFLINE";
            }
            $json['nclients'] = count($appArray);
            if (empty($json['applications'])) {
                $json['applications'] = [];
            }
            $json['applications'] = array_merge($json['applications'], $appArray);
        }
        TimeLogEnd($timeName, __LINE__);

        $count = 0;
        if (!isset($json['total'])) {
            $json['total'] = 0;
        }
        if (!empty($json['applications'])) {
            $json['total'] += count($json['applications']);
        }
        while (!empty($json[$count])) {
            $json['total'] += count($json[$count]['applications']);
            $count++;
        }
        if (!empty($json['applications'])) {
            $applications = [];
            foreach ($json['applications'] as $key => $value) {
                // remove duplicated
                if (!is_array($value) || empty($value['href']) || in_array($value['href'], $applications)) {
                    unset($json['applications'][$key]);
                    continue;
                }
                $applications[] = $value['href'];
                if (empty($value['users_id']) && !empty($value['user'])) {
                    $u = User::getFromUsername($value['user']);
                    $json['applications'][$key]['users_id'] = $u['id'];
                }
            }
        }
        //$cache = $cacheHandler->setCache($json);
        //Live::checkAllFromStats();
        TimeLogEnd($timeName, __LINE__);
        //_error_log('Live::createStatsCache ' . json_encode($cache));
    } else {
        //_error_log('getStatsNotifications: 2 cached result');
        $json = array();
    }

    TimeLogEnd($timeName, __LINE__);
    if (empty($json['applications'])) {
        $json['applications'] = [];
    }

    TimeLogEnd($timeName, __LINE__);
    foreach ($json['applications'] as $key => $value) {
        $isListed = Live::isApplicationListed(@$value['key'], $listItIfIsAdminOrOwner);
        if (!$isListed) {
            $json['hidden_applications'][] = $value;
            unset($json['applications'][$key]);
        } else {
            $json['applications'][$key]['isListed'] = $isListed;
        }
    }
    TimeLogEnd($timeName, __LINE__);
    if (!empty($json['applications']) && is_array($json['applications'])) {
        $json['countLiveStream'] = count($json['applications']);
    } else {
        $json['countLiveStream'] = 0;
    }
    TimeLogEnd($timeName, __LINE__);
    $json['timezone'] = date_default_timezone_get();
    if (!isset($json['error'])) {
        $json['error'] = false;
    }
    if (!isset($json['msg'])) {
        $json['msg'] = '';
    }
    if (!isset($json['nclients'])) {
        $json['nclients'] = $json['countLiveStream'];
    }
    $__getStatsNotifications__ = $json;
    TimeLogEnd($timeName, __LINE__);
    return $json;
}

function getLiveUsersLabelVideo($videos_id, $totalViews = null, $viewsClass = "label label-default", $counterClass = "label label-primary")
{
    global $global;
    $label = '';
    if (AVideoPlugin::isEnabledByName('LiveUsers') && method_exists("LiveUsers", "getLabels")) {
        $label .= LiveUsers::getLabels(getSocketVideoClassName($videos_id), $totalViews, $viewsClass, $counterClass, 'video');
    }
    return $label;
}

function getLiveUsersLabelLive($key, $live_servers_id, $viewsClass = "label label-default", $counterClass = "label label-primary")
{
    if (AVideoPlugin::isEnabledByName('LiveUsers') && method_exists("LiveUsers", "getLabels")) {
        $totalViews = LiveUsers::getTotalUsers($key, $live_servers_id);
        return LiveUsers::getLabels(getSocketLiveClassName($key, $live_servers_id), $totalViews, $viewsClass, $counterClass, 'live');
    }
}

function getLiveUsersLabelLiveLinks($liveLinks_id, $totalViews = null, $viewsClass = "label label-default", $counterClass = "label label-primary")
{
    if (AVideoPlugin::isEnabledByName('LiveUsers') && method_exists("LiveUsers", "getWatchingNowLabel")) {
        return LiveUsers::getWatchingNowLabel(getSocketLiveLinksClassName($liveLinks_id), "label label-primary", '', $viewsClass, 'livelinks');
    }
}

function getLiveUsersLabel($viewsClass = "label label-default", $counterClass = "label label-primary")
{
    if (empty($_REQUEST['disableLiveUsers']) && AVideoPlugin::isEnabledByName('LiveUsers')) {
        $live = isLive();
        if (!empty($live)) {
            if (!empty($live['key'])) {
                return getLiveUsersLabelLive($live['key'], $live['live_servers_id'], $viewsClass, $counterClass);
            } elseif (!empty($live['liveLinks_id'])) {
                return getLiveUsersLabelLiveLinks($live['liveLinks_id'], null, $viewsClass, $counterClass);
            }
        } else {
            $videos_id = getVideos_id();
            if (!empty($videos_id)) {
                $v = new Video("", "", $videos_id);
                $totalViews = $v->getViews_count();
                return getLiveUsersLabelVideo($videos_id, $totalViews, $viewsClass, $counterClass);
            }
        }
    }
    return "";
}

function getLiveUsersLabelHTML($viewsClass = "label label-default", $counterClass = "label label-primary")
{
    global $global, $_getLiveUsersLabelHTML;
    if (!empty($_getLiveUsersLabelHTML)) {
        return '';
    }
    $_getLiveUsersLabelHTML = 1;
    $htmlMediaTag = '';
    $htmlMediaTag .= '<div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);" class="liveUsersLabel">';
    $htmlMediaTag .= getIncludeFileContent($global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php', ['viewsClass' => $viewsClass, 'counterClass' => $counterClass]);
    $htmlMediaTag .= getLiveUsersLabel($viewsClass, $counterClass);
    $htmlMediaTag .= '</div>';
    return $htmlMediaTag;
}

function getHTMLTitle($titleArray)
{
    global $config, $global;

    if (!empty($_REQUEST['catName'])) {
        $cat = Category::getCategoryByName($_REQUEST['catName']);
        $titleArray[] = $cat['name'];
    }
    if (!is_array($titleArray)) {
        $titleArray = [];
    }
    $titleArray[] = getSEOComplement();
    $titleArray[] = $config->getWebSiteTitle();
    $cleanTitleArray = array();
    foreach ($titleArray as $value) {
        if (!empty($value) && !in_array($value, $cleanTitleArray)) {
            $cleanTitleArray[] = $value;
        }
    }

    $title = implode($config->getPageTitleSeparator(), $cleanTitleArray);
    $global['pageTitle'] = $title;
    return "<title>{$title}</title>";
}

function getButtonSignInAndUp()
{
    $signIn = getButtonSignIn();
    $signUp = getButtonSignUp();
    $html = $signIn . $signUp;
    if (!empty($signIn) && !empty($signIn)) {
        return '<div class="btn-group justified">' . $html . '</div>';
    } else {
        return $html;
    }
}

function getButtonSignUp()
{
    global $global;
    $obj = AVideoPlugin::getDataObject('CustomizeUser');
    if (!empty($obj->disableNativeSignUp)) {
        return '';
    }

    $url = $global['webSiteRootURL'] . 'signUp';
    $url = addQueryStringParameter($url, 'redirectUri', getRedirectUri());

    $html = '<a class="btn navbar-btn btn-default" href="' . $url . '" ><i class="fas fa-user-plus"></i> ' . __("Sign Up") . '</a> ';
    return $html;
}

function getButtonSignIn()
{
    global $global;
    $obj = AVideoPlugin::getDataObject('CustomizeUser');
    if (!empty($obj->disableNativeSignIn)) {
        return '';
    }

    $url = $global['webSiteRootURL'] . 'user';
    $url = addQueryStringParameter($url, 'redirectUri', getRedirectUri());

    $html = '<a class="btn navbar-btn btn-success" href="' . $url . '" ><i class="fas fa-sign-in-alt" ></i> ' . __("Sign In") . '</a> ';
    return $html;
}

function getTitle()
{
    global $global;
    if (empty($global['pageTitle'])) {
        $url = getSelfURI();

        $global['pageTitle'] = str_replace($global['webSiteRootURL'], '', $url);

        if (preg_match('/\/plugin\/([^\/])/i', $url, $matches)) {
            $global['pageTitle'] = __('Plugin') . ' ' . __($matches[1]);
        }

        $title = $global['pageTitle'];
    }

    return $global['pageTitle'];
}

function outputAndContinueInBackground($msg = '')
{
    global $outputAndContinueInBackground;

    if (!empty($outputAndContinueInBackground)) {
        return false;
    }
    $outputAndContinueInBackground = 1;
    @_session_write_close();
    //_mysql_close();
    // Instruct PHP to continue execution
    ignore_user_abort(true);
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    _ob_start();
    echo $msg;
    @header("Connection: close");
    @header("Content-Length: " . ob_get_length());
    @header("HTTP/1.1 200 OK");
    ob_end_flush();
    flush();
}

function cleanUpRowFromDatabase($row)
{
    if (is_array($row)) {
        foreach ($row as $key => $value) {
            if (preg_match('/pass/i', $key)) {
                unset($row[$key]);
            }
        }
    }
    return $row;
}

function getImageTransparent1pxURL()
{
    global $global;
    return getURL("view/img/transparent1px.png");
}

function fixTimezone($timezone)
{
    $known_abbreviations = [
        'PDT' => 'America/Los_Angeles',
        'PST' => 'America/Los_Angeles',
        'EDT' => 'America/New_York',
        'EST' => 'America/New_York',
        'CDT' => 'America/Chicago',
        'CST' => 'America/Chicago',
        'CEST' => 'Europe/Madrid',
        'Etc/UTC' => 'America/Los_Angeles',
        //'UTC' => 'America/Los_Angeles'
    ];

    // If the timezone is a known abbreviation, replace it
    if (array_key_exists($timezone, $known_abbreviations)) {
        $timezone = $known_abbreviations[$timezone];
    }

    // If the timezone is not a valid identifier, default to 'UTC'
    if (!in_array($timezone, timezone_identifiers_list())) {
        $timezone = 'America/Los_Angeles';
    }

    return $timezone;
}

function getSystemTimezone()
{
    global $global, $_getSystemTimezoneName;
    if (isset($_getSystemTimezoneName)) {
        return $_getSystemTimezoneName;
    }

    if (isWindowsServer()) {
        $cmd = 'tzutil /g';
    } else {
        $cmd = 'cat /etc/timezone';
    }

    $_getDatabaseTimezoneName = trim(preg_replace('/[^a-z0-9_ \/-]+/si', '', shell_exec($cmd)));

    $_getDatabaseTimezoneName = fixTimezone($_getDatabaseTimezoneName);

    return $_getDatabaseTimezoneName;
}

function get_js_availableLangs()
{
    global $global;
    if (empty($global['js_availableLangs'])) {
        include_once $global['systemRootPath'] . 'objects/bcp47.php';
    }
    return $global['js_availableLangs'];
}

function listAllWordsToTranslate()
{
    global $global;
    $cacheName = 'listAllWordsToTranslate';
    $cache = ObjectYPT::getCache($cacheName, 0);
    if (!empty($cache)) {
        return object_to_array($cache);
    }
    ini_set('max_execution_time', 300);

    function listAll($dir)
    {
        $vars = [];
        if (preg_match('/vendor.*$/', $dir)) {
            return $vars;
        }
        //echo $dir.'<br>';
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry !== '.' && $entry !== '..') {
                    $filename = ($dir) . DIRECTORY_SEPARATOR . $entry;
                    if (is_dir($filename)) {
                        $vars_dir = listAll($filename);
                        $vars = array_merge($vars, $vars_dir);
                    } elseif (preg_match("/\.php$/", $entry)) {
                        //echo $entry.PHP_EOL;
                        $data = file_get_contents($filename);
                        $regex = '/__\(["\']{1}(.*)["\']{1}\)/U';
                        preg_match_all(
                            $regex,
                            $data,
                            $matches
                        );
                        foreach ($matches[0] as $key => $value) {
                            $vars[$matches[1][$key]] = $matches[1][$key];
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $vars;
    }

    $vars1 = listAll($global['systemRootPath'] . 'plugin');
    //var_dump($vars1);exit;
    $vars2 = listAll($global['systemRootPath'] . 'view');
    //var_dump($vars2);exit;
    $vars3 = listAll($global['systemRootPath'] . 'objects');

    $vars = array_merge($vars1, $vars2, $vars3);

    sort($vars);
    ObjectYPT::setCache($cacheName, $vars);
    return $vars;
}

function getScriptRunMicrotimeInSeconds()
{
    global $global;
    $time_now = microtime(true);
    return ($time_now - $global['avideoStartMicrotime']);
}

if (false) {

    function openssl_cipher_key_length()
    {
        return 0;
    }
}

function getHashMethodsAndInfo()
{
    global $global, $_getHashMethod;

    if (empty($_getHashMethod)) {
        if (empty($global['salt'])) {
            $global['salt'] = '11234567890abcdef';
        }
        $saltMD5 = md5($global['salt']);
        if (!empty($global['useLongHash'])) {
            $base = 2;
            $cipher_algo = 'des';
        } else {
            $base = 32;
            $cipher_algo = 'rc4';
        }
        $cipher_methods = openssl_get_cipher_methods();
        if (!in_array($cipher_algo, $cipher_methods)) {
            $base = 32;
            $cipher_algo = $cipher_methods[0];
        }

        $ivlen = openssl_cipher_iv_length($cipher_algo);
        if (function_exists('openssl_cipher_key_length')) {
            $keylen = openssl_cipher_key_length($cipher_algo);
        } else {
            $keylen = $ivlen;
        }

        $iv = substr($saltMD5, 0, $ivlen);
        $key = substr($saltMD5, 0, $keylen);

        $_getHashMethod = ['cipher_algo' => $cipher_algo, 'iv' => $iv, 'key' => $key, 'base' => $base, 'salt' => $global['salt']];
    }
    return $_getHashMethod;
}

function idToHash($id)
{
    global $global, $_idToHash;

    if (!isset($_idToHash)) {
        $_idToHash = [];
    }

    if (!empty($_idToHash[$id])) {
        return $_idToHash[$id];
    }

    $MethodsAndInfo = getHashMethodsAndInfo();
    $cipher_algo = $MethodsAndInfo['cipher_algo'];
    $iv = $MethodsAndInfo['iv'];
    $key = $MethodsAndInfo['key'];
    $base = $MethodsAndInfo['base'];

    $idConverted = base_convert($id, 10, $base);
    $hash = (@openssl_encrypt($idConverted, $cipher_algo, $key, 0, $iv));
    //$hash = preg_replace('/^([+]+)/', '', $hash);
    $hash = preg_replace('/(=+)$/', '', $hash);
    $hash = str_replace(['/', '+', '='], ['_', '-', '.'], $hash);
    if (empty($hash)) {
        _error_log('idToHash error: ' . openssl_error_string() . PHP_EOL . json_encode(['id' => $id, 'cipher_algo' => $cipher_algo, 'base' => $base, 'idConverted' => $idConverted, 'hash' => $hash, 'iv' => $iv]));
        if (!empty($global['useLongHash'])) {
            $global['useLongHash'] = 0;
            return idToHash($id);
        }
    }
    //return base64_encode($hash);
    $_idToHash[$id] = $hash;
    return $hash;
}

function hashToID($hash)
{
    //return hashToID_old($hash);
    global $global;
    $hash = str_replace(['_', '-', '.'], ['/', '+', '='], $hash);
    //var_dump($_GET, $hash);
    $MethodsAndInfo = getHashMethodsAndInfo();
    $cipher_algo = $MethodsAndInfo['cipher_algo'];
    $iv = $MethodsAndInfo['iv'];
    $key = $MethodsAndInfo['key'];
    $base = $MethodsAndInfo['base'];

    //$hash = base64_decode($hash);
    $decrypt = @openssl_decrypt($hash, $cipher_algo, $key, 0, $iv);
    $decrypt = base_convert($decrypt, $base, 10);
    //var_dump($decrypt);exit;
    if (empty($decrypt) || !is_numeric($decrypt)) {
        return hashToID_old($hash);
    }

    return intval($decrypt);
}

/**
 * Deprecated function
 * @global array $global
 * @param string $hash
 * @return int
 */
function hashToID_old($hash)
{
    global $global;
    if (!empty($global['useLongHash'])) {
        $base = 2;
        $cipher_algo = 'des';
    } else {
        $base = 32;
        $cipher_algo = 'rc4';
    }
    //$hash = str_pad($hash,  4, "=");
    $hash = str_replace(['_', '-', '.'], ['/', '+', '='], $hash);
    //$hash = base64_decode($hash);
    $decrypt = openssl_decrypt(($hash), $cipher_algo, $global['salt']);
    $decrypt = base_convert($decrypt, $base, 10);
    return intval($decrypt);
}

function videosHashToID($hash_of_videos_id)
{
    if (is_int($hash_of_videos_id)) {
        return $hash_of_videos_id;
    }
    if (!is_string($hash_of_videos_id) && !is_numeric($hash_of_videos_id)) {
        if (is_array($hash_of_videos_id)) {
            return $hash_of_videos_id;
        } else {
            return 0;
        }
    }
    if (preg_match('/^\.([0-9a-z._-]+)/i', $hash_of_videos_id, $matches)) {
        $hash_of_videos_id = hashToID($matches[1]);
    }
    return $hash_of_videos_id;
}

/**
 *
 * @global type $advancedCustom
 * @global array $global
 * @global type $_getCDNURL
 * @param string $type enum(CDN, CDN_S3,CDN_B2,CDN_FTP,CDN_YPTStorage,CDN_Live,CDN_LiveServers)
 * @param string $id the ID of the URL in case the CDN is an array
 * @return \string
 */
function getCDN($type = 'CDN', $id = 0)
{
    global $advancedCustom, $global, $_getCDNURL;
    $index = $type . $id;
    if (!isset($_getCDNURL)) {
        $_getCDNURL = [];
    }
    if (empty($_getCDNURL[$index])) {
        if (!empty($type) && class_exists('AVideoPlugin') && AVideoPlugin::isEnabledByName('CDN')) {
            $_getCDNURL[$index] = CDN::getURL($type, $id);
        }
    }
    if ($type == 'CDN') {
        if (!empty($global['ignoreCDN'])) {
            return $global['webSiteRootURL'];
        } elseif (!empty($advancedCustom) && !empty($advancedCustom->videosCDN) && isValidURL($advancedCustom->videosCDN)) {
            $_getCDNURL[$index] = addLastSlash($advancedCustom->videosCDN);
        } elseif (empty($_getCDNURL[$index])) {
            $_getCDNURL[$index] = $global['webSiteRootURL'];
        }
    }
    //var_dump($type, $id, $_getCDNURL[$index]);
    return empty($_getCDNURL[$index]) ? false : $_getCDNURL[$index];
}

function getURL($relativePath, $ignoreCDN = false)
{
    global $global;
    $relativePath = str_replace('\\', '/', $relativePath);
    $relativePath = getRelativePath($relativePath);
    if (!isset($_SESSION['user']['sessionCache']['getURL'])) {
        $_SESSION['user']['sessionCache']['getURL'] = [];
    }
    if (!empty($_SESSION['user']['sessionCache']['getURL'][$relativePath])) {
        $_SESSION['user']['sessionCache']['getURL'][$relativePath] = fixTestURL($_SESSION['user']['sessionCache']['getURL'][$relativePath]);
        return $_SESSION['user']['sessionCache']['getURL'][$relativePath];
    }

    $file = "{$global['systemRootPath']}{$relativePath}";
    if (empty($ignoreCDN)) {
        $url = getCDN() . $relativePath;
    } else {
        $url = $global['webSiteRootURL'] . $relativePath;
    }
    $url = fixTestURL($url);
    if (file_exists($file)) {
        $cache = @filemtime($file) . '_' . @filectime($file);
        $url = addQueryStringParameter($url, 'cache', $cache);
        $_SESSION['user']['sessionCache']['getURL'][$relativePath] = $url;
    } else {
        $url = addQueryStringParameter($url, 'cache', 'not_found');
    }

    return $url;
}

function fixTestURL($text)
{
    if (empty($text) || !is_string($text)) {
        return $text;
    }
    if (isAVideoMobileApp() || !empty($_REQUEST['isAVideoMobileApp'])) {
        $text = str_replace(array('https://vlu.me', 'https://www.vlu.me', 'vlu.me'), array('http://192.168.0.2', 'http://192.168.0.2', '192.168.0.2'), $text);
    }
    $text = str_replace(array('https://192.168.0.2'), array('http://192.168.0.2'), $text);
    return $text;
}

function getCDNOrURL($url, $type = 'CDN', $id = 0)
{
    if (!preg_match('/^http/i', $url)) {
        return $url;
    }
    $cdn = getCDN($type, $id);
    if (!empty($cdn)) {
        return $cdn;
    }
    return addLastSlash($url);
}

function replaceCDNIfNeed($url, $type = 'CDN', $id = 0)
{
    $cdn = getCDN($type, $id);
    if (!empty($_GET['debug'])) {
        $obj = AVideoPlugin::getDataObject('Blackblaze_B2');
        var_dump($url, $type, $id, $cdn, $obj->CDN_Link);
        exit;
    }
    if (empty($cdn)) {
        if ($type === 'CDN_B2') {
            $obj = AVideoPlugin::getDataObject('Blackblaze_B2');
            if (isValidURL($obj->CDN_Link)) {
                $basename = basename($url);
                return addLastSlash($obj->CDN_Link) . $basename;
            }
        } elseif ($type === 'CDN_S3') {
            $obj = AVideoPlugin::getDataObject('AWS_S3');
            if (isValidURL($obj->CDN_Link)) {
                $cdn = $obj->CDN_Link;
            }
        }
        if (empty($cdn)) {
            return $url;
        }
    }

    return str_replace(parse_url($url, PHP_URL_HOST), parse_url($cdn, PHP_URL_HOST), $url);
}

function isIPPrivate($ip)
{
    if ($ip == '192.168.0.2') {
        return false;
    }
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    $result = filter_var(
        $ip,
        FILTER_VALIDATE_IP,
        FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
    if (empty($result)) {
        return true;
    }
    return false;
}

function countDownPage($toTime, $message, $image, $bgImage, $title)
{
    global $global;
    include $global['systemRootPath'] . 'objects/functionCountDownPage.php';
    exit;
}

function inputToRequest()
{
    $content = file_get_contents("php://input");
    if (!empty($content)) {
        $json = json_decode($content);
        if (empty($json)) {
            return false;
        }
        foreach ($json as $key => $value) {
            if (!isset($_REQUEST[$key])) {
                $_REQUEST[$key] = $value;
            }
        }
    }
}

function useVideoHashOrLogin()
{
    if (!empty($_REQUEST['video_id_hash'])) {
        $videos_id = Video::getVideoIdFromHash($_REQUEST['video_id_hash']);
        if (!empty($videos_id)) {
            $users_id = Video::getOwner($videos_id);
            $user = new User($users_id);
            _error_log("useVideoHashOrLogin: $users_id, $videos_id");
            return $user->login(true);
        }
    }
    return User::loginFromRequest();
}

function strip_specific_tags($string, $tags_to_strip = ['script', 'style', 'iframe', 'object', 'applet', 'link'], $removeContent = true)
{
    if (empty($string)) {
        return '';
    }
    foreach ($tags_to_strip as $tag) {
        $replacement = '$1';
        if ($removeContent) {
            $replacement = '';
        }
        $string = preg_replace('/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/s', $replacement, $string);
    }
    return $string;
}

function strip_render_blocking_resources($string)
{
    $tags_to_strip = ['link', 'style'];
    $head = preg_match('/<head>(.*)<\/head>/s', $string, $matches);
    if (empty($matches[0])) {
        $matches[0] = '';
    }
    $string = str_replace($matches[0], '{_head_}', $string);
    foreach ($tags_to_strip as $tag) {
        $string = preg_replace('/<' . $tag . '[^>]*>(.*?)<\/' . $tag . '>/s', '', $string);
        $string = preg_replace('/<' . $tag . '[^>]*\/>/s', '', $string);
    }
    $string = str_replace('{_head_}', $matches[0], $string);
    return $string;
}

function optimizeHTMLTags($html)
{
    return $html;
    //$html = optimizeCSS($html);
    //$html = optimizeJS($html);
    return $html . '<--! optimized -->';
}

function optimizeCSS($html)
{
    global $global;
    $css = '';
    $cacheDir = getVideosDir() . 'cache/';
    $cacheName = md5(getSelfURI() . User::getId()) . '.css';
    $filename = "{$cacheDir}{$cacheName}";
    $urlname = "{$global['webSiteRootURL']}videos/cache/{$cacheName}";
    $HTMLTag = "<link href=\"{$urlname}\" rel=\"stylesheet\" type=\"text/css\"/>";
    $fileExists = file_exists($filename);
    //$fileExists = false;
    // get link tags
    $pattern = '/((<(link)[^>]*(stylesheet|css)[^>]*\/>)|(<(style)[^>]*>([^<]+)<\/style>))/i';
    preg_match_all($pattern, $html, $matches);
    foreach ($matches[3] as $key => $type) {
        if (mb_strtolower($type) == 'link') {
            $linkTag = $matches[0][$key];
            $pattern = '/href=.(http[^"\']+)/i';
            preg_match($pattern, $linkTag, $href);
            if (empty($href)) {
                continue;
            }
            if (!$fileExists) {
                $content = url_get_contents($href[1]);
                if (empty($content)) {
                    continue;
                }
                $css .= PHP_EOL . " /* link {$href[1]} */ " . $content;
            }
            $html = str_replace($linkTag, '', $html);
        } else {
            if (!$fileExists) {
                $css .= PHP_EOL . ' /* style */ ' . $matches[7][$key];
            }
            $html = str_replace($matches[1][$key], '', $html);
        }
    }
    if (!$fileExists) {
        _file_put_contents($filename, $css);
    }
    return str_replace('</title>', '</title><!-- optimized CSS -->' . PHP_EOL . $HTMLTag . PHP_EOL . '', $html);
}

function optimizeJS($html)
{
    global $global;
    $js = '';
    $cacheDir = getVideosDir() . 'cache/';
    $cacheName = md5(getSelfURI() . User::getId()) . '.js';
    $filename = "{$cacheDir}{$cacheName}";
    $urlname = "{$global['webSiteRootURL']}videos/cache/{$cacheName}";
    $HTMLTag = "<script src=\"{$urlname}\"></script>";
    $fileExists = file_exists($filename);
    $fileExists = false;
    // get link tags
    $pattern = '/((<script[^>]+(src=[^ ]+)[^>]*>( *)<\/script>)|(<script[^>]*>([^<]+)<\/script>))/si';
    preg_match_all($pattern, $html, $matches);
    foreach ($matches[2] as $key => $type) {
        if (empty($type)) {
            if (preg_match('/application_ld_json/i', $matches[1][$key])) {
                continue;
            }
            $js .= PHP_EOL . " /* js */ " . $matches[6][$key];
            $html = str_replace($matches[1][$key], '', $html);
        } else {
            $pattern = '/src=.(http[^"\']+)/i';
            preg_match($pattern, $type, $href);
            if (empty($href)) {
                continue;
            }
            if (preg_match('/(jquery|video-js|videojs)/i', $href[1])) {
                continue;
            }
            if (!$fileExists) {
                $content = url_get_contents($href[1]);
                if (empty($content)) {
                    continue;
                }
                $js .= PHP_EOL . " /* js link {$href[1]} */ " . $content;
            }
            $html = str_replace($type, '', $html);
        }
    }
    if (!$fileExists) {
        _file_put_contents($filename, $js);
    }
    return str_replace('</body>', '<!-- optimized JS -->' . PHP_EOL . $HTMLTag . PHP_EOL . '</body>', $html);
}

function number_format_short($n, $precision = 1)
{
    $n = floatval($n);
    if ($n < 900) {
        // 0 - 900
        $n_format = number_format($n, $precision);
        $suffix = '';
    } elseif ($n < 900000) {
        // 0.9k-850k
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } elseif ($n < 900000000) {
        // 0.9m-850m
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } elseif ($n < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
    }

    // Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotzero = '.' . str_repeat('0', $precision);
        $n_format = str_replace($dotzero, '', $n_format);
    }

    return $n_format . $suffix;
}

/**
 * convert a time in a timezone into my time
 * @param string $time
 * @param string $timezone
 * @return string
 */
function getTimeInTimezone($time, $timezone)
{
    if (!is_numeric($time)) {
        $time = strtotime($time);
    }
    if (empty($timezone) || empty(date_default_timezone_get()) || $timezone == date_default_timezone_get()) {
        return $time;
    }
    try {
        $dateTimeZone = new DateTimeZone($timezone);
    } catch (Exception $e) {
        return $time;
    }
    $date = new DateTime(date('Y-m-d H:i:s', $time));
    $date->setTimezone($dateTimeZone);
    //$date->setTimezone(date_default_timezone_get());
    $dateString = $date->format('Y-m-d H:i:s');
    return strtotime($dateString);
}

function convertToMyTimezone($date, $fromTimezone)
{
    $time = getTimestampFromTimezone($date, $fromTimezone);
    return date('Y-m-d H:i:s', $time);
}

function convertFromMyTimeTOMySQL($date)
{
    return ObjectYPT::clientTimezoneToDatabaseTimezone($date);
}

function convertFromMyTimeTODefaultTimezoneTime($date)
{
    return convertDateFromToTimezone($date, date_default_timezone_get(), getDefaultTimezone());
}

function convertFromDefaultTimezoneTimeToMyTimezone($date)
{
    return convertDateFromToTimezone($date, getDefaultTimezone(), date_default_timezone_get());
}

function getDefaultTimezone()
{
    global $advancedCustom, $_getDefaultTimezone;
    if (!empty($_getDefaultTimezone)) {
        return $_getDefaultTimezone;
    }
    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");
    }
    $timeZOnesOptions = object_to_array($advancedCustom->timeZone->type);
    $_getDefaultTimezone = $timeZOnesOptions[$advancedCustom->timeZone->value];
    return $_getDefaultTimezone;
}

function convertDateFromToTimezone($date, $fromTimezone, $toTimezone)
{
    if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}(:[0-9]{2})?/', $date)) {
        _error_log("convertDateFromToTimezone ERROR ($date, $fromTimezone, $toTimezone)");
        return $date;
    }
    //_error_log("convertDateFromToTimezone($date, $fromTimezone, $toTimezone)");
    $currentTimezone = date_default_timezone_get();
    date_default_timezone_set($fromTimezone);
    $time = strtotime($date);
    date_default_timezone_set($toTimezone);

    $newDate = date('Y-m-d H:i:s', $time);

    date_default_timezone_set($currentTimezone);
    return $newDate;
}

function getTimestampFromTimezone($date, $fromTimezone)
{
    $date = new DateTime($date, new DateTimeZone($fromTimezone));
    return $date->getTimestamp();
}

function getCSSAnimation($type = 'animate__flipInX', $loaderSequenceName = 'default', $delay = 0.1)
{
    global $_getCSSAnimationClassDelay;
    getCSSAnimationClassAndStyleAddWait($delay, $loaderSequenceName);
    return ['class' => 'animate__animated ' . $type, 'style' => "-webkit-animation-delay: {$_getCSSAnimationClassDelay[$loaderSequenceName]}s; animation-delay: {$_getCSSAnimationClassDelay[$loaderSequenceName]}s;"];
}

function getCSSAnimationClassAndStyleAddWait($delay, $loaderSequenceName = 'default')
{
    global $_getCSSAnimationClassDelay;
    if (!isset($_getCSSAnimationClassDelay)) {
        $_getCSSAnimationClassDelay = [];
    }
    if (empty($_getCSSAnimationClassDelay[$loaderSequenceName])) {
        $_getCSSAnimationClassDelay[$loaderSequenceName] = 0;
    }
    $_getCSSAnimationClassDelay[$loaderSequenceName] += $delay;
}

function getCSSAnimationClassAndStyle($type = 'animate__flipInX', $loaderSequenceName = 'default', $delay = 0.1)
{
    if (isAVideoMobileApp()) {
        return false;
    }
    $array = getCSSAnimation($type, $loaderSequenceName, $delay);
    return "{$array['class']}\" style=\"{$array['style']}";
}

function isHTMLEmpty($html_string)
{
    // Remove HTML comments
    $html_string_no_comments = preg_replace('/<!--(.*?)-->/', '', $html_string);
    $html_string_no_tags = strip_specific_tags($html_string_no_comments, ['br', 'p', 'span', 'div'], false);
    $result = trim(str_replace(["\r", "\n"], ['', ''], $html_string_no_tags));
    // Uncomment the below line if you want to debug
    // var_dump(empty($result), $result, $html_string_no_tags, $html_string_no_comments, $html_string);
    return empty($result);
}

function emptyHTML($html_string)
{
    return isHTMLEmpty($html_string);
}

function getMediaSessionPosters($imagePath)
{
    global $global;
    if (empty($imagePath) || !file_exists($imagePath)) {
        return array();
    }
    $sizes = [96, 128, 192, 256, 384, 512];

    $posters = [];

    foreach ($sizes as $value) {
        $destination = str_replace('.jpg', "_{$value}.jpg", $imagePath);
        $path = convertImageIfNotExists($imagePath, $destination, $value, $value);
        if (!empty($path)) {
            $convertedImage = convertImageIfNotExists($imagePath, $destination, $value, $value);
            $relativePath = str_replace($global['systemRootPath'], '', $convertedImage);
            $url = getURL($relativePath);
            $posters[$value] = ['path' => $path, 'relativePath' => $relativePath, 'url' => $url];
        }
    }
    return $posters;
}

function deleteMediaSessionPosters($imagePath)
{
    if (empty($imagePath)) {
        return false;
    }
    $sizes = [96, 128, 192, 256, 384, 512];

    foreach ($sizes as $value) {
        $destination = str_replace('.jpg', "_{$value}.jpg", $imagePath);

        _error_log("deleteMediaSessionPosters ($destination) unlink line=" . __LINE__);
        @unlink($destination);
    }
}

function getMediaSession()
{
    $MediaMetadata = new stdClass();
    $MediaMetadata->title = '';
    $videos_id = getVideos_id();
    if ($liveLink = isLiveLink()) {
        $MediaMetadata = LiveLinks::getMediaSession($liveLink);
    } elseif ($live = isLive()) {
        $MediaMetadata = Live::getMediaSession($live['key'], $live['live_servers_id'], @$live['live_schedule_id'], 0);
    } elseif (!empty($videos_id)) {
        if (!empty($videos_id)) {
            $MediaMetadata = Video::getMediaSession($videos_id);
        } else {
            echo '<!-- mediaSession videos id is empty -->';
        }
    } elseif (!empty($_REQUEST['videos_id'])) {
        $MediaMetadata = Video::getMediaSession($_REQUEST['videos_id']);
    } elseif (!empty($_REQUEST['key'])) {
        $MediaMetadata = Live::getMediaSession($_REQUEST['key'], @$_REQUEST['live_servers_id'], @$_REQUEST['live_schedule_id'], 0);
    }
    if (empty($MediaMetadata) || empty($MediaMetadata->title)) {
        $MediaMetadata = new stdClass();
        $MediaMetadata->title = '';
    } else {
        $MediaMetadata->title = getSEOTitle($MediaMetadata->title);
    }
    return $MediaMetadata;
}

function pluginsRequired($arrayPluginName, $featureName = '')
{
    global $global;
    $obj = new stdClass();
    $obj->error = false;
    $obj->msg = '';

    foreach ($arrayPluginName as $name) {
        $loadPluginFile = "{$global['systemRootPath']}plugin/{$name}/{$name}.php";
        if (!file_exists($loadPluginFile)) {
            $obj->error = true;
            $obj->msg = "Plugin {$name} is required for $featureName ";
            break;
        }
        if (!AVideoPlugin::isEnabledByName($name)) {
            $obj->error = true;
            $obj->msg = "Please enable Plugin {$name} it is required for $featureName ";
            break;
        }
    }
    return $obj;
}

function _isSocketPresentOnCrontab()
{
    foreach (getValidCrontabLines() as $line) {
        if (!empty($line) && preg_match('/plugin\/YPTSocket\/server.php/', $line)) {
            return true;
        }
    }
    return false;
}

function _isSchedulerPresentOnCrontab()
{
    foreach (getValidCrontabLines() as $line) {
        if (!empty($line) && preg_match('/plugin\/Scheduler\/run.php/', $line)) {
            return true;
        }
    }
    return false;
}

function getValidCrontabLines()
{
    global $_validCrontabLines;
    if (empty($validCrontabLines)) {
        $crontab = shell_exec('crontab -l');
        if (empty($crontab)) {
            return array();
        }
        $crontabLines = preg_split("/\r\n|\n|\r/", $crontab);
        $_validCrontabLines = [];

        foreach ($crontabLines as $line) {
            $line = trim($line);
            if (!empty($line) && !preg_match('/^#/', $line)) {
                $_validCrontabLines[] = $line;
            }
        }
    }
    return $_validCrontabLines;
}

/**
 * https://codepen.io/ainalem/pen/LJYRxz
 * @global array $global
 * @param string $id
 * @param string $type 1 to 8 [1=x, 2=<-, 3=close, 4=x, 5=<-, 6=x, 7=x, 8=x]
 * @param string $parameters
 * @return string
 */
function getHamburgerButton($id = '', $type = 0, $parameters = 'class="btn btn-default hamburger"', $startActive = false, $invert = false)
{
    global $global;
    if ($type === 'x') {
        $XOptions = [1, 4, 6, 7, 8];
        $type = $XOptions[rand(0, 4)];
    } elseif ($type === '<-') {
        $XOptions = [2, 5];
        $type = $XOptions[rand(0, 1)];
    }
    $type = intval($type);
    if (empty($type) || ($type < 1 && $type > 8)) {
        $type = rand(1, 8);
    }
    if (empty($id)) {
        $id = uniqid();
    }
    $filePath = $global['systemRootPath'] . 'objects/functionGetHamburgerButton.php';
    return getIncludeFileContent($filePath, ['type' => $type, 'id' => $id, 'parameters' => $parameters, 'startActive' => $startActive, 'invert' => $invert]);
}

function getUserOnlineLabel($users_id, $class = '', $style = '')
{
    if (AVideoPlugin::isEnabledByName('YPTSocket')) {
        return YPTSocket::getUserOnlineLabel($users_id, $class, $style);
    } else {
        return '';
    }
}

function sendToEncoder($videos_id, $downloadURL, $checkIfUserCanUpload = false)
{
    global $global, $config;
    _error_log("sendToEncoder($videos_id, $downloadURL) start");

    // Get the video information
    $video = Video::getVideoLight($videos_id);
    if (!$video) {
        _error_log("sendToEncoder: video with ID $videos_id not found");
        return false;
    }

    // Get the user information
    $user = new User($video['users_id']);
    if ($checkIfUserCanUpload && !$user->getCanUpload()) {
        _error_log("sendToEncoder: user cannot upload users_id={$video['users_id']}=" . $user->getBdId());
        return false;
    }

    // Prepare the data to be sent to the encoder
    $postFields = [
        'user' => $user->getUser(),
        'pass' => $user->getPassword(),
        'fileURI' => $downloadURL,
        'videoDownloadedLink' => $downloadURL,
        'filename' => $video['filename'],
        'videos_id' => $videos_id,
        'notifyURL' => $global['webSiteRootURL'],
    ];

    // Check if auto HLS conversion is enabled
    if (AVideoPlugin::isEnabledByName("VideoHLS")) {
        $postFields['inputAutoHLS'] = 1;
    }

    // Send the data to the encoder
    $encoderURL = $config->getEncoderURL();
    $target = "{$encoderURL}queue";
    _error_log("sendToEncoder: SEND To QUEUE: ($target) " . json_encode($postFields));
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $target,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $r = curl_exec($curl);
    $obj = new stdClass();
    $obj->error = true;
    $obj->response = $r;
    if ($errno = curl_errno($curl)) {
        $error_message = curl_strerror($errno);
        $obj->msg = "cURL error ({$errno}):\n {$error_message}";
    } else {
        $obj->error = false;
    }
    _error_log("sendToEncoder: QUEUE CURL: ($target) " . json_encode($obj));
    curl_close($curl);
    Configuration::deleteEncoderURLCache();
    return $obj;
}

function getExtension($link)
{
    $path_parts = pathinfo($link);
    //$extension = mb_strtolower(@$path_parts["extension"]);
    $filebasename = explode('?', $path_parts['basename']);
    return pathinfo($filebasename[0], PATHINFO_EXTENSION);
}

function getHtaccessForVideoVersion($videosHtaccessFile)
{
    if (!file_exists($videosHtaccessFile)) {
        return 0;
    }
    $f = fopen($videosHtaccessFile, 'r');
    $line = fgets($f);
    fclose($f);
    preg_match('/# version +([0-9.]+)/i', $line, $matches);
    return @$matches[1];
}

/**
 * add the twitterjs if the link is present
 * @param string $text
 * @return string
 */
function addTwitterJS($text)
{
    if (preg_match('/href=.+twitter.com.+ref_src=.+/', $text)) {
        if (!preg_match('/platform.twitter.com.widgets.js/', $text)) {
            $text .= '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
        }
    }
    return $text;
}

function getMP3ANDMP4DownloadLinksFromHLS($videos_id, $video_type)
{
    $downloadOptions = [];
    if (empty($videos_id)) {
        return [];
    }
    if (empty($video_type)) {
        $video = Video::getVideoLight($videos_id);
        $video_type = $video['type'];
    }

    if ($video_type == "video" || $video_type == "audio") {
        $videoHLSObj = AVideoPlugin::getDataObjectIfEnabled('VideoHLS');
        if (!empty($videoHLSObj) && method_exists('VideoHLS', 'getMP3ANDMP4DownloadLinks')) {
            $downloadOptions = VideoHLS::getMP3ANDMP4DownloadLinks($videos_id);
        } else {
            //_error_log("getMP3ANDMP4DownloadLinksFromHLS($videos_id, $video_type): invalid plugin");
        }
    } else {
        _error_log("getMP3ANDMP4DownloadLinksFromHLS($videos_id, $video_type): invalid video type");
    }
    return $downloadOptions;
}

function isOnDeveloperMode()
{
    global $global;
    return (!empty($global['developer_mode']) || (!empty($global['developer_mode_admin_only']) && User::isAdmin()));
}

function getWordOrIcon($word, $class = '')
{
    $word = trim($word);
    if (preg_match('/facebook/i', $word)) {
        return '<i class="fab fa-facebook ' . $class . '" data-toggle="tooltip" title="' . $word . '"></i>';
    }
    if (preg_match('/youtube|youtu.be/i', $word)) {
        return '<i class="fab fa-youtube ' . $class . '" data-toggle="tooltip" title="' . $word . '"></i>';
    }
    if (preg_match('/twitch/i', $word)) {
        return '<i class="fab fa-twitch ' . $class . '" data-toggle="tooltip" title="' . $word . '"></i>';
    }
    return $word;
}

function getHomePageURL()
{
    global $global;
    if (useIframe()) {
        return "{$global['webSiteRootURL']}site/";
    } else {
        return "{$global['webSiteRootURL']}";
    }
}

function useIframe()
{
    return false && isOnDeveloperMode() && !isBot();
}

function getIframePaths()
{
    global $global;
    $modeYoutube = false;
    if (!empty($_GET['videoName']) || !empty($_GET['v']) || !empty($_GET['playlist_id']) || !empty($_GET['liveVideoName']) || !empty($_GET['evideo'])) {
        $modeYoutube = true;
        $relativeSRC = 'view/modeYoutube.php';
    } else {
        $relativeSRC = 'view/index_firstPage.php';
    }
    $url = "{$global['webSiteRootURL']}{$relativeSRC}";
    if ($modeYoutube && !empty($_GET['v'])) {
        if (!empty($_GET['v'])) {
            $url = "{$global['webSiteRootURL']}video/" . $_GET['v'] . '/';
            unset($_GET['v']);
            if (!empty($_GET['videoName'])) {
                $url .= urlencode($_GET['videoName']) . '/';
                unset($_GET['videoName']);
            }
        }
    }
    unset($_GET['inMainIframe']);

    foreach ($_GET as $key => $value) {
        $url = addQueryStringParameter($url, $key, $value);
    }

    return ['relative' => $relativeSRC, 'url' => $url, 'path' => "{$global['systemRootPath']}{$relativeSRC}", 'modeYoutube' => $modeYoutube];
}

function getFeedButton($rss, $mrss, $roku)
{
    $buttons = '<div class="dropdown feedDropdown" style="display: inline-block;" data-toggle="tooltip" title="' . __("Feed") . '">
        <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
            <i class="fas fa-rss-square"></i>
            <span class="hidden-xs hidden-sm">' . __("Feed") . '</span>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">';
    if (isValidURL($rss)) {
        $buttons .= '<li><a href="' . $rss . '" target="_blank">RSS</a></li>';
    }
    if (isValidURL($mrss)) {
        $buttons .= '<li><a href="' . $mrss . '" target="_blank">MRSS</a></li>';
    }
    if (isValidURL($roku)) {
        $buttons .= '<li><a href="' . $roku . '" target="_blank">Roku</a></li>';
    }
    $buttons .= '</ul></div>';
    return $buttons;
}

function getPlatformId()
{
    global $global;
    return base_convert(md5(encryptString($global['salt'] . 'AVideo')), 16, 36);
}

function fixQuotes($str)
{
    if (!is_string($str)) {
        return $str;
    }
    $chr_map = [
        // Windows codepage 1252
        "\xC2\x82" => "'", // U+0082⇒U+201A single low-9 quotation mark
        "\xC2\x84" => '"', // U+0084⇒U+201E double low-9 quotation mark
        "\xC2\x8B" => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
        "\xC2\x91" => "'", // U+0091⇒U+2018 left single quotation mark
        "\xC2\x92" => "'", // U+0092⇒U+2019 right single quotation mark
        "\xC2\x93" => '"', // U+0093⇒U+201C left double quotation mark
        "\xC2\x94" => '"', // U+0094⇒U+201D right double quotation mark
        "\xC2\x9B" => "'", // U+009B⇒U+203A single right-pointing angle quotation mark
        // Regular Unicode     // U+0022 quotation mark (")
        // U+0027 apostrophe     (')
        "\xC2\xAB" => '"', // U+00AB left-pointing double angle quotation mark
        "\xC2\xBB" => '"', // U+00BB right-pointing double angle quotation mark
        "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
        "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
        "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
        "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
        "\xE2\x80\x9C" => '"', // U+201C left double quotation mark
        "\xE2\x80\x9D" => '"', // U+201D right double quotation mark
        "\xE2\x80\x9E" => '"', // U+201E double low-9 quotation mark
        "\xE2\x80\x9F" => '"', // U+201F double high-reversed-9 quotation mark
        "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
        "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
    ];
    $chr = array_keys($chr_map); // but: for efficiency you should
    $rpl = array_values($chr_map); // pre-calculate these two arrays
    $str = str_replace($chr, $rpl, html_entity_decode($str, ENT_QUOTES, "UTF-8"));
    return $str;
}

function setIsConfirmationPage()
{
    global $_isConfirmationPage;
    $_isConfirmationPage = 1;
}

function isConfirmationPage()
{
    global $_isConfirmationPage;
    return !empty($_isConfirmationPage);
}

function set_error_reporting()
{
    global $global;
    if (!empty($global['debug']) && empty($global['noDebug'])) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        ini_set('error_reporting', E_ERROR);
        ini_set('log_errors', 1);
        error_reporting(E_ERROR);
        ini_set('display_errors', 0);
    }
}

function addSearchOptions($url)
{
    $url = addQueryStringParameter($url, 'tags_id', intval(@$_GET['tagsid']));
    $url = addQueryStringParameter($url, 'search', getSearchVar());
    $url = addQueryStringParameter($url, 'created', intval(@$_GET['created']));
    $url = addQueryStringParameter($url, 'minViews', intval(@$_GET['minViews']));
    return $url;
}

function is_port_open($port, $address = '127.0.0.1', $timeout = 5)
{
    // Use localhost or 127.0.0.1 as the target address
    $address = '127.0.0.1';

    // Attempt to open a socket connection to the specified port
    $socket = @fsockopen($address, $port, $errno, $errstr, $timeout);

    // If the socket connection was successful, the port is open
    if ($socket) {
        fclose($socket);
        return true;
    }
    _error_log("is_port_open($port, $address) error {$errstr}");
    // If the socket connection failed, the port is closed
    return false;
}

function canSearchUsers()
{
    global $advancedCustomUser;
    if (canAdminUsers()) {
        return true;
    }
    if (AVideoPlugin::isEnabledByName('PlayLists')) {
        if (PlayLists::canManageAllPlaylists()) {
            return true;
        }
    }
    if (empty($advancedCustomUser)) {
        $advancedCustomUser = AVideoPlugin::getObjectDataIfEnabled('CustomizeUser');
    }
    if ($advancedCustomUser->userCanChangeVideoOwner) {
        return true;
    }
    return false;
}

function canAdminUsers()
{
    if (Permissions::canAdminUsers()) {
        return true;
    }
    if (AVideoPlugin::isEnabledByName('PayPerView')) {
        if (PayPerView::canSeePPVManagementInfo()) {
            return true;
        }
    }
    return false;
}

function getRandomCode()
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = strlen($characters) - 1;
    $char1 = $characters[rand(0, $max)];
    $char2 = $characters[rand(0, $max)];
    $char3 = $characters[rand(0, $max)];
    $uniqueId = uniqid();
    $uniquePart1 = str_pad(base_convert(substr($uniqueId, -5), 16, 36), 4, $char1, STR_PAD_LEFT);
    $uniquePart2 = str_pad(base_convert(substr($uniqueId, 4, 4), 16, 36), 4, $char2, STR_PAD_LEFT);
    $uniquePart3 = str_pad(base_convert(substr($uniqueId, 0, 4), 16, 36), 4, $char3, STR_PAD_LEFT);
    $code = strtoupper("{$uniquePart2}-{$uniquePart1}");
    return $code;
}

function getActivationCode()
{
    $code = getRandomCode();
    $obj = array(
        'username' => User::getUserName(),
        'users_id' => User::getId(),
        'code' => $code,
        'expires' => strtotime('+10 minutes'),
    );

    $path = getTmpDir('loginCodes');
    make_path($path);
    $filename = "{$path}{$code}.log";
    //$obj['filename'] = $filename;
    $obj['bytes'] = file_put_contents($filename, encryptString(json_encode($obj)));
    return $obj;
}

function fix_parse_url($url, $parameter)
{
    $cleanParameter = str_replace('.', '_', $parameter);
    //var_dump("{$cleanParameter}%3D", "{$parameter}%3D", $url);
    $url = str_replace("{$cleanParameter}%3D", "{$parameter}%3D", $url);
    $url = str_replace("{$cleanParameter}=", "{$parameter}=", $url);
    return $url;
}

function generateHorizontalFlickity($items)
{
    global $generateHorizontalFlickityLoaded;
    if (empty($generateHorizontalFlickityLoaded)) {
    ?>
        <link href="<?php echo getURL('node_modules/flickity/dist/flickity.min.css'); ?>" rel="stylesheet" type="text/css" />
    <?php
    }
    $carouselClass = 'carousel_' . uniqid();
    ?>
    <div id="<?php echo $carouselClass; ?>" class=" HorizontalFlickity" style="visibility: hidden;">
        <?php
        $initialIndex = 0;
        foreach ($items as $key => $item) {
            $isActive = false;
            if (!empty($item['isActive'])) {
                $isActive = true;
            }
            $class = 'btn-default';
            if ($isActive) {
                $class = 'btn-primary';
                $initialIndex = $key;
            }
        ?>
            <div class="carousel-cell">
                <a title="<?php echo $item['tooltip']; ?>"
                    href="<?php echo $item['href']; ?>"
                    <?php
                    if (preg_match('/^#[0-9a-z.-]+/', $item['href'])) {
                        echo ' data-toggle="tab" ';
                    } else {
                        echo ' data-toggle="tooltip" ';
                    }
                    if (!empty($item['onclick'])) {
                        echo 'onclick="' . $item['onclick'] . '"';
                    } ?>
                    class="btn <?php echo $class; ?>">
                    <?php echo $item['label']; ?>
                </a>
            </div>
        <?php } ?>
    </div>
    <script>
        $(document).ready(function() {
            // jQuery
            var $carousel = $('#<?php echo $carouselClass; ?>');
            // bind event listener first
            $carousel.on('ready.flickity', function() {
                $('#<?php echo $carouselClass; ?>').css('visibility', 'visible');
            });
            // initialize Flickity
            $carousel.flickity({
                cellAlign: 'left',
                contain: true,
                wrapAround: true,
                pageDots: false,
                groupCells: true,
                //initialIndex: <?php echo $initialIndex; ?>,
            });

            $carousel.flickity('selectCell', <?php echo $initialIndex; ?>);
        });
    </script>
    <?php
    if (empty($generateHorizontalFlickityLoaded)) {
    ?>
        <script src="<?php echo getURL('node_modules/flickity/dist/flickity.pkgd.min.js'); ?>" type="text/javascript"></script>
<?php
    }
    $generateHorizontalFlickityLoaded = 1;
}

function saveRequestVars()
{
    global $savedRequestVars;

    $array = array('GET', 'POST', 'REQUEST');

    foreach ($array as $value) {
        eval('$savedRequestVars[$value] = $_' . $value . ';');
    }
}


function restoreRequestVars()
{
    global $savedRequestVars;

    $array = array('GET', 'POST', 'REQUEST');

    foreach ($array as $value) {
        eval('$_' . $value . ' = $savedRequestVars[$value];');
    }
}

function getMVideo($htmlMediaTag)
{
    global $global;
    $filePath = "{$global['systemRootPath']}objects/functionGetMVideo.php";
    $contents = getIncludeFileContent($filePath, ['htmlMediaTag' => $htmlMediaTag]);
    return $contents;
}

function getDeviceName($returnIfEmptyUA = 'unknown')
{
    global $forceDeviceType;
    if (!empty($forceDeviceType)) {
        return $forceDeviceType;
    }
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return $returnIfEmptyUA;
    }
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (strpos($userAgent, 'roku') !== false) {
        return 'roku';
    } elseif (strpos($userAgent, 'appletv') !== false) {
        return 'appleTV';
    } elseif (strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false || strpos($userAgent, 'ipod') !== false) {
        return 'ios';
    } elseif (strpos($userAgent, 'android') !== false) {
        if (strpos($userAgent, 'mobile') !== false) {
            return 'androidMobile';
        } else {
            return 'androidTV';
        }
    } elseif (strpos($userAgent, 'silk') !== false) {
        return 'firestick';
    } elseif (preg_match('/Mozilla.*X11; Linux x86_64.*Chrome/', $userAgent) !== false) {
        return 'androidMobileDesktopMode';
    } else {
        return 'web';
    }
}

function getValueOrBlank($array, $default = '')
{
    $text = $default;
    foreach ($array as $value) {
        if (!empty($_REQUEST[$value])) {
            $text = $_REQUEST[$value];
        }
    }
    return $text;
}

function getRequestUniqueString()
{
    $text = getValueOrBlank(['app_bundle', 'ads_app_bundle', 'publisher_app_bundle']);
    $text = getValueOrBlank(['ads_did']);
    $text .= getValueOrBlank(['platform']);
    $text .= isForKidsSet() ? 'forKids' : '';
    return $text;
}

function isForKidsSet()
{
    return !empty($_COOKIE['forKids']) || (!empty($_REQUEST['forKids']) && intval($_REQUEST['forKids']) > 0);
}

function calculateCenterCrop($originalWidth, $originalHeight, $aspectRatio)
{
    if ($aspectRatio == Video::ASPECT_RATIO_ORIGINAL) {
        return ['newWidth' => intval($originalWidth), 'newHeight' => intval($originalHeight), 'x' => 0, 'y' => 0];
    }

    // Define aspect ratio dimensions
    $aspectRatioDimensions = [
        Video::ASPECT_RATIO_SQUARE => ['width' => 1, 'height' => 1],
        Video::ASPECT_RATIO_VERTICAL => ['width' => 9, 'height' => 16],
        Video::ASPECT_RATIO_HORIZONTAL => ['width' => 16, 'height' => 9],
    ];

    // Validate aspect ratio parameter
    if (!array_key_exists($aspectRatio, $aspectRatioDimensions)) {
        return false; // Invalid aspect ratio
    }

    // Get aspect ratio dimensions
    $targetWidth = $aspectRatioDimensions[$aspectRatio]['width'];
    $targetHeight = $aspectRatioDimensions[$aspectRatio]['height'];

    // Calculate scaling factors for width and height
    $scaleWidth = $originalHeight * $targetWidth / $targetHeight;
    $scaleHeight = $originalWidth * $targetHeight / $targetWidth;

    // Determine new width, height, x, and y for center cropping
    if ($scaleWidth > $originalWidth) {
        // Use scaled height
        $newWidth = $originalWidth;
        $newHeight = $scaleHeight;
        $x = 0;
        $y = ($originalHeight - $scaleHeight) / 2;
    } else {
        // Use scaled width
        $newWidth = $scaleWidth;
        $newHeight = $originalHeight;
        $x = ($originalWidth - $scaleWidth) / 2;
        $y = 0;
    }

    return ['newWidth' => intval($newWidth), 'newHeight' => intval($newHeight), 'x' => intval($x), 'y' => intval($y)];
}

function getTourHelpButton($stepsFileRelativePath, $class = 'btn btn-default', $showLabel = true)
{
    /*
    [
        {
            "element": "#elementId1",
            "intro": "Welcome to our feature!"
        },
        {
            "element": "#elementId2",
            "intro": "Here's how you can use this tool."
        }
    ]
    */
    $label = '';
    if ($showLabel) {
        if (is_string($showLabel)) {
            $label = __($showLabel);
        } else {
            $label = __('Help');
        }
    }
    return "<button type=\"button\" class=\"startTourBtn {$class}\" onclick=\"startTour('{$stepsFileRelativePath}')\"><i class=\"fa-solid fa-circle-question\"></i> {$label}</button>";
}

function getInfoButton($info)
{
    $html = '<button class="btn btn-default btn-block infoButton" data-toggle="tooltip" title="' . __('Info') . '"><i class="fa-solid fa-circle-info"></i><div class="hidden">' . $info . '</div></button>';
    return $html;
}

function mkSubCategory($catId)
{

    global $global, $parsed_cats;
    unset($_REQUEST['parentsOnly']);

    $cacheName = "mkSubCategory_{$catId}";
    $cache = ObjectYPT::getCache($cacheName, rand(300, 600));
    if (!empty($cache)) {
        return $cache;
    }
    $subcats = Category::getChildCategories($catId);
    $html = '';
    if (!empty($subcats)) {
        $html .= "<ul class=\"nav\" style='margin-bottom: 0px; list-style-type: none;'>";
        foreach ($subcats as $subcat) {
            if ($subcat['parentId'] != $catId) {
                continue;
            }
            if (empty($subcat['total'])) {
                continue;
            }
            if (is_array($parsed_cats) && in_array($subcat['id'], $parsed_cats)) {
                continue;
            }
            //$parsed_cats[] = $subcat['id'];
            $html .= '<li class="navsub-toggle ' . ($subcat['clean_name'] == @$_REQUEST['catName'] ? "active" : "") . '">';
            $html .= '<a href="' . $global['webSiteRootURL'] . 'cat/' . $subcat['clean_name'] . '" >';
            $html .=  '<i class="' . (empty($subcat['iconClass']) ? "fa fa-folder" : $subcat['iconClass']) . '"></i>  <span class="menuLabel">' . __($subcat['name']) . ' <span class="badge">' . $subcat['total'] . '</span></span>';
            $html .=  '</a>';
            $html .= mkSubCategory($subcat['id']);
            $html .=  '</li>';
        }
        $html .=  "</ul>";
    }

    ObjectYPT::setCache($cacheName, $html);
    return $html;
}


/**
 * Check if the user came from an external link, sanitize the referrer, store it in the session, and return it.
 * Returns the sanitized external referrer or the existing referrer stored in the session.
 */
function storeAndGetExternalReferrer()
{
    global $global;

    if (!isset($global['external_referrer'])) {
        if (!preg_match('/GoogleInteractiveMediaAds/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/imasdk/i', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/imasdk/i', $_SERVER['HTTP_REFERER'])) {
            // Get the current domain
            $currentDomain = $_SERVER['HTTP_HOST'];

            // Get the referrer URL, if available
            $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

            _session_start();
            if (!empty($referrer)) {
                // Sanitize the referrer URL to remove illegal characters
                $sanitizedReferrer = filter_var($referrer, FILTER_SANITIZE_URL);
                // Parse the referrer's host (domain) if referrer exists
                $referrerHost = parse_url($sanitizedReferrer, PHP_URL_HOST);

                // Check if the referrer is an external link (not the same as current domain)
                if ($referrerHost && $referrerHost !== $currentDomain) {
                    // Check if the session does not have an external referrer or if it's different from the current one
                    if (!isset($_SESSION['external_referrer']) || $_SESSION['external_referrer'] !== $sanitizedReferrer) {
                        if (!empty($sanitizedReferrer)) {
                            //_error_log('external_referrer changed' . json_encode(debug_backtrace()));
                            // Store the sanitized and escaped external referrer in the session
                            $_SESSION['external_referrer'] = htmlspecialchars($sanitizedReferrer, ENT_QUOTES, 'UTF-8');
                        }
                    }
                }
            }
        }

        $global['external_referrer'] = isset($_SESSION['external_referrer']) ? $_SESSION['external_referrer'] : '';
    }
    return $global['external_referrer'];
}


require_once __DIR__ . '/functionsSecurity.php';
require_once __DIR__ . '/functionsMySQL.php';
require_once __DIR__ . '/functionsDocker.php';
require_once __DIR__ . '/functionsImages.php';
require_once __DIR__ . '/functionsExec.php';
require_once __DIR__ . '/functionsLogs.php';
require_once __DIR__ . '/functionsMail.php';
require_once __DIR__ . '/functionsFile.php';
require_once __DIR__ . '/functionsFFMPEG.php';
require_once __DIR__ . '/functionsSocket.php';
require_once __DIR__ . '/functionsPHP.php';
require_once __DIR__ . '/functionsAVideo.php';
require_once __DIR__ . '/functionsBrowser.php';
require_once __DIR__ . '/functionsHuman.php';
