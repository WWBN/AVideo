<?php
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
    if ((!$unit && $size >= 1 << 30) || $unit == "GB")
        return number_format($size / (1 << 30), 2) . "GB";
    if ((!$unit && $size >= 1 << 20) || $unit == "MB")
        return number_format($size / (1 << 20), 2) . "MB";
    if ((!$unit && $size >= 1 << 10) || $unit == "KB")
        return number_format($size / (1 << 10), 2) . "KB";
    return number_format($size) . " bytes";
}

function get_max_file_size() {
    return humanFileSize(file_upload_max_size());
}

function humanTiming($time) {
    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => __('year'),
        2592000 => __('month'),
        604800 => __('week'),
        86400 => __('day'),
        3600 => __('hour'),
        60 => __('minute'),
        1 => __('second')
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit)
            continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
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
    if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false)
        return true;
    else
        return false;
}

function isPHP($version = "'7.0.0'") {
    if (version_compare(PHP_VERSION, $version) >= 0) {
        return true;
    } else {
        return false;
    }
}

function modRewriteEnabled() {
    if (!function_exists('apache_get_modules')) {
        ob_start();
        phpinfo(INFO_MODULES);
        $contents = ob_get_contents();
        ob_end_clean();
        return (strpos($contents, 'mod_rewrite') !== false);
    } else {
        return in_array('mod_rewrite', apache_get_modules());
    }
}

function isFFMPEG() {
    return trim(shell_exec('which ffmpeg'));
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
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
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
                        function($text) {
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
    $sql = "SELECT * FROM videos v ";
    $res = $global['mysqli']->query($sql);
    $seconds = 0;
    while ($row = $res->fetch_assoc()) {
        $seconds += parseDurationToSeconds($row['duration']);
    }
    return $seconds;
}

function getMinutesTotalVideosLength() {
    $seconds = getSecondsTotalVideosLength();
    return floor($seconds / 60);
}

function parseDurationToSeconds($str) {
    $durationParts = explode(":", $str);
    if (empty($durationParts[1]) || $durationParts[0]=="EE")
        return 0;
    if(empty($durationParts[2])){
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
        $mail->IsSMTP(); // enable SMTP
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = $config->getSmtpSecure(); // secure transfer enabled REQUIRED for Gmail
        $mail->Host = $config->getSmtpHost();
        $mail->Port = $config->getSmtpPort();
        $mail->Username = $config->getSmtpUsername();
        $mail->Password = $config->getSmtpPassword();
    } else {
        $mail->isSendmail();
    }
}

function parseVideos($videoString = null){   
    if (strpos($videoString, 'youtube.com/embed') !== FALSE)     {
        return $videoString;
    }
    if (strpos($videoString, 'iframe') !== FALSE)     {
        // retrieve the video url
        $anchorRegex = '/src="(.*)?"/isU';
        $results = array();
        if (preg_match($anchorRegex, $video, $results)) 
        {
            $link = trim($results[1]);
        }
    } else {
        // we already have a url
        $link = $videoString;
    }    

    if (strpos($link, 'youtube.com') !== FALSE) { 

        preg_match(
        '/[\\?\\&]v=([^\\?\\&]+)/',
        $link,
        $matches
        );  
        //the ID of the YouTube URL: x6qe_kVaBpg
        $id = $matches[1];
        return '//www.youtube.com/embed/'.$id;
    }
    else if (strpos($link, 'youtu.be') !== FALSE) { 
        preg_match(
        '/youtu.be\/([a-zA-Z0-9_]+)\??/i',
        $link,
        $matches
        );  
        $id = $matches[1];
        return '//www.youtube.com/embed/'.$id;
    }
    else if (strpos($link, 'player.vimeo.com') !== FALSE) {
        // works on:
        // http://player.vimeo.com/video/37985580?title=0&amp;byline=0&amp;portrait=0
        $videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
        preg_match($videoIdRegex, $link, $matches);
        $id = $matches[1];  
        return '//player.vimeo.com/video/'.$id;
    }
    else if (strpos($link, 'vimeo.com/channels') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vimeo.com\/channels\/[a-z0-9-]+\/(\d+)($|\/)/i',
                $link,
                $matches
            );

        //the ID of the Vimeo URL: 71673549 
        $id = $matches[2];  
        return '//player.vimeo.com/video/'.$id;
    }
    else if (strpos($link, 'vimeo.com') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/',
                $link,
                $matches
            );

        //the ID of the Vimeo URL: 71673549 
        $id = $matches[2];  
        return '//player.vimeo.com/video/'.$id;
    }
    return $videoString;
    // return data

}