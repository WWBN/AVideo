<?php

function forbiddenWords($text) {
    global $global;
    if (empty($global['forbiddenWords'])) {
        return false;
    }
    foreach ($global['forbiddenWords'] as $value) {
        if (preg_match("/{$value}/i", $text)) {
            return true;
        }
    }
    return false;
}

function xss_esc($text) {
    return @htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function xss_esc_back($text) {
    $text = htmlspecialchars_decode($text, ENT_QUOTES);
    $text = str_replace(array('&amp;', '&#039;', "#039;"), array(" ", "`", "`"), $text);
    return $text;
}

// make sure SecureVideosDirectory will be the first
function cmpPlugin($a, $b) {
    if ($a['name'] == 'SecureVideosDirectory') {
        return -1;
    }else if ($a['name'] == 'GoogleAds_IMA') {
        return -1;
    }

    return 1;
}

// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
    static $max_size = -1;

    if ($max_size < 0) {
        // Start with post_max_size.
        $max_size = parse_size(ini_get('post_max_size'));

        // If upload_max_size is less, then reduce. Except if upload_max_size is
        // zero, which indicates no limit.
        $upload_max = parse_size(ini_get('upload_max_filesize'));
        if ($upload_max > 0 && $upload_max < $max_size) {
            $max_size = $upload_max;
        }
    }
    return $max_size;
}

function parse_size($size) {
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
    $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
    if ($unit) {
        // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    } else {
        return round($size);
    }
}

function humanFileSize($size, $unit = "") {
    if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
        return number_format($size / (1 << 30), 2) . "GB";
    }

    if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
        return number_format($size / (1 << 20), 2) . "MB";
    }

    if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
        return number_format($size / (1 << 10), 2) . "KB";
    }

    return number_format($size) . " bytes";
}

function get_max_file_size() {
    return humanFileSize(file_upload_max_size());
}

function humanTiming($time, $precision = 0) {
    if (!is_int($time)) {
        $time = strtotime($time);
    }
    $time = time() - $time; // to get the time since that moment
    return secondsToHumanTiming($time, $precision);
}

function secondsToHumanTiming($time, $precision = 0) {
    $time = ($time < 0) ? $time * -1 : $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second',
    );

    /**
     * For detection propouse only
     */
    __('year');
    __('month');
    __('week');
    __('day');
    __('hour');
    __('minute');
    __('second');
    __('years');
    __('months');
    __('weeks');
    __('days');
    __('hours');
    __('minutes');
    __('seconds');

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) {
            continue;
        }

        $numberOfUnits = floor($time / $unit);
        if ($numberOfUnits > 1) {
            $text = __($text . "s");
        } else {
            $text = __($text);
        }

        if ($precision) {
            $rest = $time % $unit;
            if ($rest) {
                $text .= ' ' . secondsToHumanTiming($rest, $precision - 1);
            }
        }

        return $numberOfUnits . ' ' . $text;
    }
}

function checkVideosDir() {
    $dir = "../videos";
    if (file_exists($dir)) {
        if (is_writable($dir)) {
            return true;
        } else {
            return false;
        }
    } else {
        return mkdir($dir);
    }
}

function isApache() {
    if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
        return true;
    } else {
        return false;
    }
}

function isPHP($version = "'7.0.0'") {
    if (version_compare(PHP_VERSION, $version) >= 0) {
        return true;
    } else {
        return false;
    }
}

function modEnabled($mod_name) {
    if (!function_exists('apache_get_modules')) {
        ob_start();
        phpinfo(INFO_MODULES);
        $contents = ob_get_contents();
        ob_end_clean();
        return (strpos($contents, 'mod_' . $mod_name) !== false);
    } else {
        return in_array('mod_' . $mod_name, apache_get_modules());
    }
}

function modRewriteEnabled() {
    return modEnabled("rewrite");
}

function modAliasEnabled() {
    return modEnabled("alias");
}

function isFFMPEG() {
    return trim(shell_exec('which ffmpeg'));
}

function isUnzip() {
    return trim(shell_exec('which unzip'));
}

function isExifToo() {
    return trim(shell_exec('which exiftool'));
}

function getPathToApplication() {
    return str_replace("install/index.php", "", $_SERVER["SCRIPT_FILENAME"]);
}

function getURLToApplication() {
    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url = explode("install/index.php", $url);
    $url = $url[0];
    return $url;
}

//max_execution_time = 7200
function check_max_execution_time() {
    $max_size = ini_get('max_execution_time');
    $recomended_size = 7200;
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//post_max_size = 100M
function check_post_max_size() {
    $max_size = parse_size(ini_get('post_max_size'));
    $recomended_size = parse_size('100M');
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//upload_max_filesize = 100M
function check_upload_max_filesize() {
    $max_size = parse_size(ini_get('upload_max_filesize'));
    $recomended_size = parse_size('100M');
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

//memory_limit = 100M
function check_memory_limit() {
    $max_size = parse_size(ini_get('memory_limit'));
    $recomended_size = parse_size('512M');
    if ($recomended_size > $max_size) {
        return false;
    } else {
        return true;
    }
}

function check_mysqlnd() {
    return function_exists('mysqli_fetch_all');
}

function base64DataToImage($imgBase64) {
    $img = $imgBase64;
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    return base64_decode($img);
}

function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "127.0.0.1";
    }
    return $ip;
}

function cleanString($text) {
    $utf8 = array(
        '/[áàâãªä]/u' => 'a',
        '/[ÁÀÂÃÄ]/u' => 'A',
        '/[ÍÌÎÏ]/u' => 'I',
        '/[íìîï]/u' => 'i',
        '/[éèêë]/u' => 'e',
        '/[ÉÈÊË]/u' => 'E',
        '/[óòôõºö]/u' => 'o',
        '/[ÓÒÔÕÖ]/u' => 'O',
        '/[úùûü]/u' => 'u',
        '/[ÚÙÛÜ]/u' => 'U',
        '/ç/' => 'c',
        '/Ç/' => 'C',
        '/ñ/' => 'n',
        '/Ñ/' => 'N',
        '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u' => ' ', // Literally a single quote
        '/[“”«»„]/u' => ' ', // Double quote
        '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

/**
 * @brief return true if running in CLI, false otherwise
 * if is set $_GET['ignoreCommandLineInterface'] will return false
 * @return boolean
 */
function isCommandLineInterface() {
    return (empty($_GET['ignoreCommandLineInterface']) && php_sapi_name() === 'cli');
}

/**
 * @brief show status message as text (CLI) or JSON-encoded array (web)
 *
 * @param array $statusarray associative array with type/message pairs
 * @return string
 */
function status($statusarray) {
    if (isCommandLineInterface()) {
        foreach ($statusarray as $status => $message) {
            echo $status . ":" . $message . "\n";
        }
    } else {
        echo json_encode(array_map(
                        function ($text) {
                    return nl2br($text);
                }
                        , $statusarray));
    }
}

/**
 * @brief show status message and die
 *
 * @param array $statusarray associative array with type/message pairs
 */
function croak($statusarray) {
    status($statusarray);
    die;
}

function getSecondsTotalVideosLength() {
    $configFile = dirname(__FILE__) . '/../videos/configuration.php';
    require_once $configFile;
    global $global;

    if (!User::isLogged()) {
        return 0;
    }
    $sql = "SELECT * FROM videos v ";
    $formats = "";
    $values = array();
    if (!User::isAdmin()) {
        $id = User::getId();
        $sql .= " WHERE users_id = ? ";
        $formats = "i";
        $values = array($id);
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

function getMinutesTotalVideosLength() {
    $seconds = getSecondsTotalVideosLength();
    return floor($seconds / 60);
}

function secondsToVideoTime($seconds) {
    $seconds = round($seconds);
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    $secs = floor($seconds % 60);
    return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
}

function parseDurationToSeconds($str) {
    $durationParts = explode(":", $str);
    if (empty($durationParts[1]) || $durationParts[0] == "EE") {
        return 0;
    }

    if (empty($durationParts[2])) {
        $durationParts[2] = 0;
    }
    $minutes = intval(($durationParts[0]) * 60) + intval($durationParts[1]);
    return intval($durationParts[2]) + ($minutes * 60);
}

/**
 *
 * @global type $global
 * @param type $mail
 * call it before send mail to let YouPHPTube decide the method
 */
function setSiteSendMessage(&$mail) {
    global $global;
    require_once $global['systemRootPath'] . 'objects/configuration.php';
    $config = new Configuration();

    if ($config->getSmtp()) {
        error_log("Sending SMTP Email");
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = $config->getSmtpAuth(); // authentication enabled
        $mail->SMTPSecure = $config->getSmtpSecure(); // secure transfer enabled REQUIRED for Gmail
        $mail->Host = $config->getSmtpHost();
        $mail->Port = $config->getSmtpPort();
        $mail->Username = $config->getSmtpUsername();
        $mail->Password = $config->getSmtpPassword();
        //error_log(print_r($config, true));
    } else {
        error_log("Sending SendMail Email");
        $mail->isSendmail();
    }
}

function parseVideos($videoString = null, $autoplay = 0, $loop = 0, $mute = 0, $showinfo = 0, $controls = 1, $time = 0) {
    if (strpos($videoString, 'youtube.com/embed') !== false) {
        return $videoString . (parse_url($videoString, PHP_URL_QUERY) ? '&' : '?') . 'modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    }
    if (strpos($videoString, 'iframe') !== false) {
        // retrieve the video url
        $anchorRegex = '/src="(.*)?"/isU';
        $results = array();
        if (preg_match($anchorRegex, $video, $results)) {
            $link = trim($results[1]);
        }
    } else {
        // we already have a url
        $link = $videoString;
    }

    if (strpos($link, 'embed') !== false) {
        return $link;
    } else if (strpos($link, 'youtube.com') !== false) {

        preg_match(
                '/[\\?\\&]v=([^\\?\\&]+)/', $link, $matches
        );
        //the ID of the YouTube URL: x6qe_kVaBpg
        $id = $matches[1];
        return '//www.youtube.com/embed/' . $id . '?modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&te=$time";
    } else if (strpos($link, 'youtu.be') !== false) {
        preg_match(
                '/youtu.be\/([a-zA-Z0-9_]+)\??/i', $link, $matches
        );
        $id = $matches[1];
        return '//www.youtube.com/embed/' . $id . '?modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    } else if (strpos($link, 'player.vimeo.com') !== false) {
        // works on:
        // http://player.vimeo.com/video/37985580?title=0&amp;byline=0&amp;portrait=0
        $videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
        preg_match($videoIdRegex, $link, $matches);
        $id = $matches[1];
        return '//player.vimeo.com/video/' . $id;
    } else if (strpos($link, 'vimeo.com/channels') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vimeo.com\/channels\/[a-z0-9-]+\/(\d+)($|\/)/i', $link, $matches
        );

        //the ID of the Vimeo URL: 71673549
        $id = $matches[2];
        return '//player.vimeo.com/video/' . $id;
    } else if (strpos($link, 'vimeo.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/', $link, $matches
        );

        //the ID of the Vimeo URL: 71673549
        $id = $matches[2];
        return '//player.vimeo.com/video/' . $id;
    } else if (strpos($link, 'dailymotion.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?dailymotion.com\/video\/([a-zA-Z0-9_]+)($|\/)/', $link, $matches
        );

        //the ID of the Vimeo URL: 71673549
        $id = $matches[2];
        return '//www.dailymotion.com/embed/video/' . $id;
    } else if (strpos($link, 'metacafe.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?metacafe.com\/watch\/([a-zA-Z0-9_\/-]+)$/', $link, $matches
        );
        $id = $matches[2];
        return '//www.metacafe.com/embed/' . $id;
    } else if (strpos($link, 'vid.me') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vid.me\/([a-zA-Z0-9_-]+)$/', $link, $matches
        );

        $id = $matches[2];
        return '//vid.me/e/' . $id;
    } else if (strpos($link, 'rutube.ru') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?rutube.ru\/video\/([a-zA-Z0-9_-]+)\/.*/', $link, $matches
        );
        $id = $matches[2];
        return '//rutube.ru/play/embed/' . $id;
    } else if (strpos($link, 'ok.ru') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?ok.ru\/video\/([a-zA-Z0-9_-]+)$/', $link, $matches
        );

        $id = $matches[2];
        return '//ok.ru/videoembed/' . $id;
    } else if (strpos($link, 'streamable.com') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?streamable.com\/([a-zA-Z0-9_-]+)$/', $link, $matches
        );

        $id = $matches[2];
        return '//streamable.com/s/' . $id;
    } else if (strpos($link, 'twitch.tv/videos') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?twitch.tv\/videos\/([a-zA-Z0-9_-]+)$/', $link, $matches
        );

        $id = $matches[2];
        return '//player.twitch.tv/?video=' . $id . '#';
    } else if (strpos($link, 'twitch.tv/videos') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?twitch.tv\/[a-zA-Z0-9_-]+\/v\/([a-zA-Z0-9_-]+)$/', $link, $matches
        );

        $id = $matches[2];
        return '//player.twitch.tv/?video=' . $id . '#';
    } else if (strpos($link, 'twitch.tv') !== false) {
        //extract the ID
        preg_match(
                '/\/\/(www\.)?twitch.tv\/([a-zA-Z0-9_-]+)$/', $link, $matches
        );

        $id = $matches[2];
        return '//player.twitch.tv/?channel=' . $id . '#';
    } else if (strpos($link, '/video/') !== false) {
        //extract the ID
        preg_match(
                '/(http.+)\/video\/([a-zA-Z0-9_-]+)($|\/)/i', $link, $matches
        );

        //the YouPHPTube site
        $site = $matches[1];
        $id = $matches[2];
        return $site . '/videoEmbeded/' . $id . "?autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    }

    $url = $videoString;
    $url_parsed = parse_url($url);
    $new_qs_parsed = array();
// Grab our first query string
    parse_str($url_parsed['query'], $new_qs_parsed);
// Here's the other query string
    $other_query_string = 'modestbranding=1&showinfo='
            . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    $other_qs_parsed = array();
    parse_str($other_query_string, $other_qs_parsed);
// Stitch the two query strings together
    $final_query_string_array = array_merge($new_qs_parsed, $other_qs_parsed);
    $final_query_string = http_build_query($final_query_string_array);
// Now, our final URL:
    $new_url = $url_parsed['scheme']
            . '://'
            . $url_parsed['host']
            . $url_parsed['path']
            . '?'
            . $final_query_string;

    return $new_url;
    // return data
}

$canUseCDN = array();

function canUseCDN($videos_id) {
    if (empty($videos_id)) {
        return false;
    }
    global $global, $canUseCDN;
    if (!isset($canUseCDN[$videos_id])) {
        require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
        $pvr360 = YouPHPTubePlugin::isEnabledByName('VR360');
        // if the VR360 is enabled you can not use the CDN, it fail to load the GL
        $isVR360Enabled = VideosVR360::isVR360Enabled($videos_id);
        if ($pvr360 && $isVR360Enabled) {
            $ret = false;
        } else {
            $ret = true;
        }

        //error_log(json_encode(array('canUseCDN'=>$ret, '$pvr360'=>$pvr360, '$isVR360Enabled'=>$isVR360Enabled, '$videos_id'=>$videos_id)));
        $canUseCDN[$videos_id] = $ret;
    }
    return $canUseCDN[$videos_id];
}

function getVideosURL($fileName) {
    if (empty($fileName)) {
        return array();
    }
    global $global;
    $types = array('', '_Low', '_SD', '_HD');
    $files = array();
    // old
    require_once $global['systemRootPath'] . 'objects/video.php';

    $plugin = YouPHPTubePlugin::loadPluginIfEnabled("VideoHLS");
    if (!empty($plugin)) {
        $files = VideoHLS::getSourceFile($fileName);
    }

    foreach ($types as $key => $value) {
        $filename = "{$fileName}{$value}";
        $source = Video::getSourceFile($filename, ".webm");
        $file = $source['path'];
        if (file_exists($file)) {
            $files["webm{$value}"] = array(
                'filename' => "{$fileName}{$value}.webm",
                'path' => $file,
                'url' => $source['url'],
                'type' => 'video',
            );
        }
        $source = Video::getSourceFile($filename, ".mp4");
        $file = $source['path'];
        if (file_exists($file)) {
            $files["mp4{$value}"] = array(
                'filename' => "{$fileName}{$value}.mp4",
                'path' => $file,
                'url' => $source['url'],
                'type' => 'video',
            );
        }
        $source = Video::getSourceFile($filename, ".mp3");
        $file = $source['path'];
        if (file_exists($file)) {
            $files["mp3{$value}"] = array(
                'filename' => "{$fileName}{$value}.ogg",
                'path' => $file,
                'url' => $source['url'],
                'type' => 'audio',
            );
        }
        $source = Video::getSourceFile($filename, ".ogg");
        $file = $source['path'];
        if (file_exists($file)) {
            $files["ogg{$value}"] = array(
                'filename' => "{$fileName}{$value}.ogg",
                'path' => $file,
                'url' => $source['url'],
                'type' => 'audio',
            );
        }
        if (empty($value)) {
            $source = Video::getSourceFile($filename, ".jpg");
            $file = $source['path'];
            if (file_exists($file)) {
                $files["jpg"] = array(
                    'filename' => "{$fileName}.jpg",
                    'path' => $file,
                    'url' => $source['url'],
                    'type' => 'image',
                );
            }
            $source = Video::getSourceFile($filename, ".gif");
            $file = $source['path'];
            if (file_exists($file)) {
                $files["gif"] = array(
                    'filename' => "{$fileName}.gif",
                    'path' => $file,
                    'url' => $source['url'],
                    'type' => 'image',
                );
            }
            $source = Video::getSourceFile($filename, "_portrait.jpg");
            $file = $source['path'];
            if (file_exists($file)) {
                $files["pjpg"] = array(
                    'filename' => "{$fileName}_portrait.jpg",
                    'path' => $file,
                    'url' => $source['url'],
                    'type' => 'image',
                );
            }
        }
    }
    return $files;
}

function getSources($fileName, $returnArray = false) {
    $name = "getSources_{$fileName}_" . intval($returnArray);
    /*
      $cached = ObjectYPT::getCache($name, 86400); //one day
      if (!empty($cached)) {
      return $cached->result;
      }
     *
     */
    if ($returnArray) {
        $videoSources = $audioTracks = $subtitleTracks = array();
    } else {
        $videoSources = $audioTracks = $subtitleTracks = "";
    }

    $video = Video::getVideoFromFileName($fileName);

    if ($video['type'] !== 'audio' && function_exists('getVRSSources')) {
        $videoSources = getVRSSources($fileName, $returnArray);
    } else {
        $files = getVideosURL($fileName);
        $sources = "";
        $sourcesArray = array();
        foreach ($files as $key => $value) {
            $path_parts = pathinfo($value['path']);
            if ($path_parts['extension'] == "webm" || $path_parts['extension'] == "mp4" || $path_parts['extension'] == "m3u8" || $path_parts['extension'] == "mp3" || $path_parts['extension'] == "ogg") {
                $obj = new stdClass();
                $obj->type = mime_content_type_per_filename($value['path']);
                if ($path_parts['extension'] == "webm" || $path_parts['extension'] == "mp4" || $path_parts['extension'] == "m3u8") {
                    $sources .= "<source src=\"{$value['url']}\" type=\"{$obj->type}\">";
                } else {
                    $sources .= "<source src=\"{$value['url']}\" type=\"{$obj->type}\">";
                }
                $obj->src = $value['url'];
                $sourcesArray[] = $obj;
            }
        }
        $videoSources = $returnArray ? $sourcesArray : $sources;
    }
    if (function_exists('getVTTTracks')) {
        $subtitleTracks = getVTTTracks($fileName, $returnArray);
    }

    if ($returnArray) {
        $return = array_merge($videoSources, $audioTracks, $subtitleTracks);
    } else {
        $return = $videoSources . $audioTracks . $subtitleTracks;
    }

    $obj = new stdClass();
    $obj->result = $return;
    //ObjectYPT::setCache($name, $obj);
    return $return;
}

/**
 *
 * @param type $file_src
 * @return typeget image size with cache
 */
function getimgsize($file_src) {
    $name = "getimgsize_" . md5($file_src);
    $cached = ObjectYPT::getCache($name, 86400); //one day
    if (!empty($cached)) {
        $c = (Array) $cached;
        $size = array();
        foreach ($c as $key => $value) {
            if (preg_match("/^[0-9]+$/", $key)) {
                $key = intval($key);
            }
            $size[$key] = $value;
        }
        return $size;
    }

    $size = @getimagesize($file_src);

    if (empty($size)) {
        $size = array(1024, 768);
    }

    ObjectYPT::setCache($name, $size);
    return $size;
}

function im_resize($file_src, $file_dest, $wd, $hd, $q = 50) {
    if (empty($file_dest)) {
        return false;
    }
    if (!file_exists($file_src)) {
        error_log("im_resize: Source not found: {$file_src}");
        return false;
    }
    $size = getimgsize($file_src);
    if ($size === false) {
        error_log("im_resize: Could not get image size: {$file_src}");
        return false;
    }
    if ($size['mime'] == 'image/pjpeg') {
        $size['mime'] = 'image/jpeg';
    }

    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
    if (empty($format)) {
        $format = 'jpeg';
    }
    $destformat = strtolower(substr($file_dest, -4));
    if (empty($destformat)) {
        error_log("destformat not found {$file_dest}");
        $destformat = ".jpg";
    }
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc)) {
        error_log("im_resize: Function does not exists: {$icfunc}");
        return false;
    }

    $src = $icfunc($file_src);

    $ws = imagesx($src);
    $hs = imagesy($src);

    if ($ws <= $hs) {
        $hd = ceil(($wd * $hs) / $ws);
    } else {
        $wd = ceil(($hd * $ws) / $hs);
    }
    if ($ws <= $wd) {
        $wd = $ws;
        $hd = $hs;
    }
    $wc = ($wd * $hs) / $hd;

    if ($wc <= $ws) {
        $hc = ($wc * $hd) / $wd;
    } else {
        $hc = ($ws * $hd) / $wd;
        $wc = ($wd * $hc) / $hd;
    }

    $dest = imagecreatetruecolor($wd, $hd);
    switch ($format) {
        case "png":
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
            imagefilledrectangle($dest, 0, 0, $nw, $nh, $transparent);

            break;
        case "gif":
            // integer representation of the color black (rgb: 0,0,0)
            $background = imagecolorallocate($src, 0, 0, 0);
            // removing the black from the placeholder
            imagecolortransparent($src, $background);

            break;
    }

    imagecopyresampled($dest, $src, 0, 0, ($ws - $wc) / 2, ($hs - $hc) / 2, $wd, $hd, $wc, $hc);
    $saved = false;
    if ($destformat == '.png') {
        $saved = imagepng($dest, $file_dest);
    }

    if ($destformat == '.jpg') {
        $saved = imagejpeg($dest, $file_dest, $q);
    }

    if (!$saved) {
        error_log('saving failed');
    }

    imagedestroy($dest);
    imagedestroy($src);
    @chmod($file_dest, 0666);

    return true;
}

function im_resizeV2($file_src, $file_dest, $wd, $hd, $q = 50) {

    $newImage = im_resize($file_src, $file_dest, $wd, $hd);
    if (!$newImage) {
        return false;
    }
    $src = imagecreatefromjpeg($file_dest);
    $ws = imagesx($src);
    $hs = imagesy($src);

    if ($ws < $wd) {
        $dst_x = ($wd - $ws) / 2;
    } else {
        $dst_x = 0;
    }

    if ($hs < $hd) {
        $dst_y = ($hd - $hs) / 2;
    } else {
        $dst_y = 0;
    }

    $mapImage = imagecreatetruecolor($wd, $hd);
    $bgColor = imagecolorallocate($mapImage, 0, 0, 0);
    imagefill($mapImage, 0, 0, $bgColor);

    $tileImg = imagecreatefromjpeg($file_dest);
    imagecopy($mapImage, $tileImg, $dst_x, $dst_y, 0, 0, $ws, $hs);

    $saved = imagejpeg($mapImage, $file_dest, $q);

    return $saved;
}

function im_resizeV3($file_src, $file_dest, $wd, $hd) {
    // this trys to preserve the aspect ratio of the thumb while letterboxing it in
    // the same way that the encoder now does.
    eval('$ffmpeg ="ffmpeg -i {$file_src} -filter_complex \"scale=(iw*sar)*min({$wd}/(iw*sar)\,{$hd}/ih):ih*min({$wd}/(iw*sar)\,{$hd}/ih), pad={$wd}:{$hd}:({$wd}-iw*min({$wd}/iw\,{$hd}/ih))/2:({$hd}-ih*min({$wd}/iw\,{$hd}/ih))/2\" -sws_flags lanczos -qscale:v 2 {$file_dest}";');
    exec($ffmpeg . " < /dev/null 2>&1", $output, $return_val);
}

function decideMoveUploadedToVideos($tmp_name, $filename) {
    global $global;
    $obj = new stdClass();
    $aws_s3 = YouPHPTubePlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = YouPHPTubePlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = YouPHPTubePlugin::loadPluginIfEnabled('FTP_Storage');

    $path_info = pathinfo($filename);
    if ($path_info['extension'] === 'zip') {
        $dir = "{$global['systemRootPath']}videos/{$path_info['filename']}";
        unzipDirectory($tmp_name, $dir); // unzip it
        cleanDirectory($dir);
        if (!empty($aws_s3)) {
            //$aws_s3->move_uploaded_file($tmp_name, $filename);
        } else if (!empty($bb_b2)) {
            $bb_b2->move_uploaded_directory($dir);
        } else if (!empty($ftp)) {
            //$ftp->move_uploaded_file($tmp_name, $filename);
        }
    } else {
        if (!empty($aws_s3)) {
            $aws_s3->move_uploaded_file($tmp_name, $filename);
        } else if (!empty($bb_b2)) {
            $bb_b2->move_uploaded_file($tmp_name, $filename);
        } else if (!empty($ftp)) {
            $ftp->move_uploaded_file($tmp_name, $filename);
        } else {
            if (!move_uploaded_file($tmp_name, "{$global['systemRootPath']}videos/{$filename}")) {
                if (!rename($tmp_name, "{$global['systemRootPath']}videos/{$filename}")) {
                    if (!copy($tmp_name, "{$global['systemRootPath']}videos/{$filename}")) {
                        $obj->msg = "Error on decideMoveUploadedToVideos({$tmp_name}, {$global['systemRootPath']}videos/{$filename})";
                        die(json_encode($obj));
                    }
                }
            }
        }
    }
}

function unzipDirectory($filename, $destination) {
    global $global;    
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 7200); // 2 hours
    error_log("unzipDirectory: {$filename}");
    exec("unzip {$filename} -d {$destination}" . "  2>&1", $output, $return_val);
    if ($return_val !== 0) {
        // try to unzip using PHP
        error_log("unzipDirectory: TRY to use PHP {$filename}");
        $zip = zip_open($filename);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                $path = "{$destination}/" . zip_entry_name($zip_entry);
                error_log("unzipDirectory: fopen $path");
                if (substr(zip_entry_name($zip_entry), -1) == '/') {
                    make_path($path);
                }else{
                    make_path($path);
                    $fp = fopen($path, "w");
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        fwrite($fp, "$buf");
                        zip_entry_close($zip_entry);
                        fclose($fp);
                    }
                }
            }
            zip_close($zip);
        }else{
            error_log("unzipDirectory: ERROR php zip does not work");
        }
    }else{
        error_log("unzipDirectory: Success {$destination}");
    }
    @unlink($filename);
}

function make_path($path){
	$dir = pathinfo($path , PATHINFO_DIRNAME);	
	if( is_dir($dir) ){
		return true;
	}else{
		if( make_path($dir) ){
			if( mkdir($dir) ){
				chmod($dir , 0777);
				return true;
			}
		}
	}	
	return false;
}

/**
 * for security clean all non secure files from directory
 * @param type $dir
 * @param type $allowedExtensions
 * @return type
 */
function cleanDirectory($dir, $allowedExtensions = array('key', 'm3u8', 'ts', 'vtt', 'jpg', 'gif', 'mp3', 'webm')) {
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    foreach ($ffs as $ff) {
        $current = $dir . '/' . $ff;
        if (is_dir($current)) {
            cleanDirectory($current, $allowedExtensions);
        }
        $path_parts = pathinfo($current);
        if (!empty($path_parts['extension']) && !in_array($path_parts['extension'], $allowedExtensions)) {
            unlink($current);
        }
    }
}

function decideFile_put_contentsToVideos($tmp_name, $filename) {
    global $global;
    $aws_s3 = YouPHPTubePlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = YouPHPTubePlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = YouPHPTubePlugin::loadPluginIfEnabled('FTP_Storage');
    if (!empty($bb_b2)) {
        $bb_b2->move_uploaded_file($tmp_name, $filename);
    } else if (!empty($aws_s3)) {
        $aws_s3->move_uploaded_file($tmp_name, $filename);
    } else if (!empty($ftp)) {
        $ftp->move_uploaded_file($tmp_name, $filename);
    } else {
        if (!move_uploaded_file($tmp_name, "{$global['systemRootPath']}videos/{$filename}")) {
            $obj->msg = "Error on move_uploaded_file({$tmp_name}, {$global['systemRootPath']}videos/{$filename})";
            die(json_encode($obj));
        }
    }
}

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename) {
        return mime_content_type_per_filename($filename);
    }

}

function mime_content_type_per_filename($filename) {
    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'avi' => 'video/avi',
        'mkv' => 'video/mkv',
        'wav' => 'audio/wav',
        'm4v' => 'video/mpeg',
        'webm' => 'video/webm',
        'wmv' => 'video/wmv',
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'f4v' => 'video/x-flv',
        'm4v' => 'video/m4v',
        'm4a' => 'video/quicktime',
        'm2p' => 'video/quicktime',
        'rm' => 'video/quicktime',
        'vob' => 'video/quicktime',
        'mkv' => 'video/quicktime',
        '3gp' => 'video/quicktime',
        'm3u8' => 'application/x-mpegURL',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
    );
    if (filter_var($filename, FILTER_VALIDATE_URL) === FALSE) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
    } else {
        $ext = pathinfo(parse_url($filename, PHP_URL_PATH), PATHINFO_EXTENSION);
    }
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

function combineFiles($filesArray, $extension = "js") {
    global $global, $advancedCustom;
    $cacheDir = $global['systemRootPath'] . 'videos/cache/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    $str = "";
    $fileName = "";
    foreach ($filesArray as $value) {
        $fileName .= $value;
    }
    if ($advancedCustom != false) {
        $minifyEnabled = $advancedCustom->EnableMinifyJS;
    } else {
        $minifyEnabled = false;
    }
    $md5FileName = md5($fileName) . ".{$extension}";
    if (!file_exists($cacheDir . $md5FileName)) {
        foreach ($filesArray as $value) {
            if (file_exists($global['systemRootPath'] . $value)) {
                $str .= "\n/*{$value} created local with systemRootPath */\n" . local_get_contents($global['systemRootPath'] . $value);
            } else if (file_exists($value)) {
                $str .= "\n/*{$value} created local with full-path given */\n" . local_get_contents($value);
            } else {
                $allowed = "";
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
        if (($extension == "js") && ($minifyEnabled)) {
            require_once $global['systemRootPath'] . 'objects/jshrink.php';
            $str = \JShrink\Minifier::minify($str, array('flaggedComments' => false));
        }
        file_put_contents($cacheDir . $md5FileName, $str);
    }
    return $global['webSiteRootURL'] . 'videos/cache/' . $md5FileName;
}

function local_get_contents($path) {
    if (function_exists('fopen')) {
        $myfile = fopen($path, "r") or die("Unable to open file!");
        $text = fread($myfile, filesize($path));
        fclose($myfile);
        return $text;
    }
}

function url_get_contents($Url, $ctx = "") {
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
    $session = $_SESSION;
    session_write_close();
    $global['mysqli']->close();
    if (empty($ctx)) {
        $opts = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true,
            ),
        );
        $context = stream_context_create($opts);
    } else {
        $context = $ctx;
    }
    if (ini_get('allow_url_fopen')) {
        try {
            $tmp = @file_get_contents($Url, false, $context);
            if ($tmp != false) {
                session_start();
                $_SESSION = $session;
                $global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);
                return $tmp;
            }
        } catch (ErrorException $e) {
            
        }
    } else if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        session_start();
        $_SESSION = $session;
        $global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);
        return $output;
    }
    $result = @file_get_contents($Url, false, $context);
    session_start();
    $_SESSION = $session;
    $global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);
    return $result;
}

function getUpdatesFilesArray() {
    global $config, $global;
    if (!class_exists('User') || !User::isAdmin()) {
        return array();
    }
    $files1 = scandir($global['systemRootPath'] . "updatedb");
    $updateFiles = array();
    foreach ($files1 as $value) {
        preg_match("/updateDb.v([0-9.]*).sql/", $value, $match);
        if (!empty($match)) {
            if ($config->currentVersionLowerThen($match[1])) {
                $updateFiles[] = array('filename' => $match[0], 'version' => $match[1]);
            }
        }
    }
    return $updateFiles;
}

function UTF8encode($data) {
    global $advancedCustom, $global;

    if (!empty($advancedCustom->utf8Encode)) {
        return utf8_encode($data);
    }
    if (!empty($advancedCustom->utf8Decode)) {
        return utf8_decode($data);
    }
    return $data;
}

//detect search engine bots
function isBot() {
    return false;
    $bot_regex = '/BotLink|bingbot|AhrefsBot|ahoy|AlkalineBOT|anthill|appie|arale|araneo|AraybOt|ariadne|arks|ATN_Worldwide|Atomz|bbot|Bjaaland|Ukonline|borg\-bot\/0\.9|boxseabot|bspider|calif|christcrawler|CMC\/0\.01|combine|confuzzledbot|CoolBot|cosmos|Internet Cruiser Robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|DIIbot|grabber|downloadexpress|DragonBot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|fastcrawler|FDSE|FELIX IDE|ESI|fido|H�m�h�kki|KIT\-Fireball|fouineur|Freecrawl|gammaSpider|gazz|gcreep|golem|googlebot|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|iajabot|INGRID\/0\.1|Informant|InfoSpiders|inspectorwww|irobot|Iron33|JBot|jcrawler|Teoma|Jeeves|jobo|image\.kapsi\.net|KDD\-Explorer|ko_yappo_robot|label\-grabber|larbin|legs|Linkidator|linkwalker|Lockon|logo_gif_crawler|marvin|mattie|mediafox|MerzScope|NEC\-MeshExplorer|MindCrawler|udmsearch|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|sharp\-info\-agent|WebMechanic|NetScoop|newscan\-online|ObjectsSearch|Occam|Orbsearch\/1\.0|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|Getterrobo\-Plus|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Search\-AU|searchprocess|Senrigan|Shagseeker|sift|SimBot|Site Valet|skymob|SLCrawler\/2\.0|slurp|ESI|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|TechBOT|templeton|titin|topiclink|UdmSearch|urlck|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|crawlpaper|wapspider|WebBandit\/1\.0|webcatcher|T\-H\-U\-N\-D\-E\-R\-S\-T\-O\-N\-E|WebMoose|webquest|webreaper|webs|webspider|WebWalker|wget|winona|whowhere|wlm|WOLP|WWWC|none|XGET|Nederland\.zoek|AISearchBot|woriobot|NetSeer|Nutch|YandexBot|YandexMobileBot|SemrushBot|FatBot|MJ12bot|DotBot|AddThis|baiduspider|SeznamBot|mod_pagespeed|CCBot|openstat.ru\/Bot|m2e/i';
    $userAgent = empty($_SERVER['HTTP_USER_AGENT']) ? false : $_SERVER['HTTP_USER_AGENT'];
    $isBot = !$userAgent || preg_match($bot_regex, $userAgent);

    return $isBot;
}

/**
 * A function that could get me the last N lines of a log file.
 * @param type $filepath
 * @param type $lines
 * @param type $adaptive
 * @return boolean
 */
function tail($filepath, $lines = 1, $adaptive = true, $returnArray = false) {
    // Open file
    $f = @fopen($filepath, "rb");
    if ($f === false) {
        return false;
    }

    // Sets buffer size, according to the number of lines to retrieve.
    // This gives a performance boost when reading a few lines from the file.
    if (!$adaptive) {
        $buffer = 4096;
    } else {
        $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
    }

    // Jump to last character
    fseek($f, -1, SEEK_END);
    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if (fread($f, 1) != "\n") {
        $lines -= 1;
    }

    // Start reading
    $output = '';
    $chunk = '';
    // While we would like more
    while (ftell($f) > 0 && $lines >= 0) {
        // Figure out how far back we should jump
        $seek = min(ftell($f), $buffer);
        // Do the jump (backwards, relative to where we are)
        fseek($f, -$seek, SEEK_CUR);
        // Read a chunk and prepend it to our output
        $output = ($chunk = fread($f, $seek)) . $output;
        // Jump back to where we started reading
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        // Decrease our line counter
        $lines -= substr_count($chunk, "\n");
    }
    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while ($lines++ < 0) {
        // Find first newline and remove all text before that
        $output = substr($output, strpos($output, "\n") + 1);
    }
    // Close file and return
    fclose($f);
    $output = trim($output);
    if ($returnArray) {
        $array = explode("\n", $output);
        $newArray = array();
        foreach ($array as $value) {
            $newArray[] = array($value);
        }
        return $newArray;
    } else {
        $output;
    }
}

function encryptPassword($password, $noSalt = false) {
    global $advancedCustom, $global, $advancedCustomUser;
    if (!empty($advancedCustomUser->encryptPasswordsWithSalt) && !empty($global['salt']) && empty($noSalt)) {
        $password .= $global['salt'];
    }

    return md5(hash("whirlpool", sha1($password)));
}

function encryptPasswordVerify($password, $hash, $encodedPass = false) {
    global $advancedCustom, $global;
    if (!$encodedPass || $encodedPass === 'false') {
        $passwordSalted = encryptPassword($password);
        // in case you enable the salt later
        $passwordUnSalted = encryptPassword($password, true);
    } else {
        $passwordSalted = $password;
        // in case you enable the salt later
        $passwordUnSalted = $password;
    }

    return $passwordSalted === $hash || $passwordUnSalted === $hash;
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
