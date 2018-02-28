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
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
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
        if ($time < $unit)
            continue;
        
        $numberOfUnits = floor($time / $unit);
        if($numberOfUnits > 1){
            $text = __($text."s");
        }else{
            $text = __($text);
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

function modEnabled($mod_name){
    if (!function_exists('apache_get_modules')) {
        ob_start();
        phpinfo(INFO_MODULES);
        $contents = ob_get_contents();
        ob_end_clean();
        return (strpos($contents, 'mod_'.$mod_name) !== false); 
    } else {
        return in_array('mod_'.$mod_name, apache_get_modules());
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

    if (strpos($link, 'embed') !== FALSE) { 
        return $link;
    }else if (strpos($link, 'youtube.com') !== FALSE) { 

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
    else if (strpos($link, 'dailymotion.com') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?dailymotion.com\/video\/([a-zA-Z0-9_]+)($|\/)/',
                $link,
                $matches
            );

        //the ID of the Vimeo URL: 71673549 
        $id = $matches[2];  
        return '//www.dailymotion.com/embed/video/'.$id;
    }else if (strpos($link, 'metacafe.com') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?metacafe.com\/watch\/([a-zA-Z0-9_\/-]+)$/',
                $link,
                $matches
            );
        $id = $matches[2];  
        return '//www.metacafe.com/embed/'.$id;
    }else if (strpos($link, 'vid.me') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?vid.me\/([a-zA-Z0-9_-]+)$/',
                $link,
                $matches
            );

        $id = $matches[2];  
        return '//vid.me/e/'.$id;
    }else if (strpos($link, 'rutube.ru') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?rutube.ru\/video\/([a-zA-Z0-9_-]+)\/.*/',
                $link,
                $matches
            );
        $id = $matches[2];  
        return '//rutube.ru/play/embed/'.$id;
    }else if (strpos($link, 'ok.ru') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?ok.ru\/video\/([a-zA-Z0-9_-]+)$/',
                $link,
                $matches
            );

        $id = $matches[2];  
        return '//ok.ru/videoembed/'.$id;
    }else if (strpos($link, 'streamable.com') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?streamable.com\/([a-zA-Z0-9_-]+)$/',
                $link,
                $matches
            );

        $id = $matches[2];  
        return '//streamable.com/s/'.$id;
    }else if (strpos($link, 'twitch.tv') !== FALSE) { 
        //extract the ID
        preg_match(
                '/\/\/(www\.)?twitch.tv\/([a-zA-Z0-9_-]+)$/',
                $link,
                $matches
            );

        $id = $matches[2];  
        return '//player.twitch.tv/?channel='.$id.'#';
    }else if (strpos($link, '/video/') !== FALSE) { 
        //extract the ID
        preg_match(
                '/(http.+)\/video\/([a-zA-Z0-9_-]+)($|\/)/i',
                $link,
                $matches
            );

        //the YouPHPTube site 
        $site = $matches[1];  
        $id = $matches[2];  
        return $site.'/videoEmbeded/'.$id;
    }
    return $videoString;
    // return data

}

function getVideosURL($fileName){
    global $global;
    $types = array('', '_Low', '_SD', '_HD');
    $files = array();
    // old
    foreach ($types as $key => $value) {
        $file = "{$global['systemRootPath']}videos/{$fileName}{$value}.mp4";
        if(file_exists($file)){
            $files["mp4{$value}"]=array(
                'filename'=>"{$fileName}{$value}.mp4",
                'path'=>$file,
                'url'=>"{$global['webSiteRootURL']}videos/{$fileName}{$value}.mp4",
                'type'=>'video'
            );
        }
        $file = "{$global['systemRootPath']}videos/{$fileName}{$value}.webm";
        if(file_exists($file)){
            $files["webm{$value}"]=array(
                'filename'=>"{$fileName}{$value}.webm",
                'path'=>$file,
                'url'=>"{$global['webSiteRootURL']}videos/{$fileName}{$value}.webm",
                'type'=>'video'

            );
        }
        $file = "{$global['systemRootPath']}videos/{$fileName}{$value}.jpg";
        if(file_exists($file)){
            $files["jpg{$value}"]=array(
                'filename'=>"{$fileName}{$value}.jpg",
                'path'=>$file,
                'url'=>"{$global['webSiteRootURL']}videos/{$fileName}{$value}.jpg",
                'type'=>'image'

            );
        }
        $file = "{$global['systemRootPath']}videos/{$fileName}{$value}.gif";
        if(file_exists($file)){
            $files["gif{$value}"]=array(
                'filename'=>"{$fileName}{$value}.gif",
                'path'=>$file,
                'url'=>"{$global['webSiteRootURL']}videos/{$fileName}{$value}.gif",
                'type'=>'image'

            );
        }
    } 
    return $files;
}

function getSources($fileName, $returnArray=false){ 
    $videoSources = "";
    if(function_exists('getVRSSources')){
        $videoSources = getVRSSources($fileName, $returnArray);
    }else{
        $files = getVideosURL($fileName);
        $sources = "";
        $sourcesArray = array();
        foreach ($files as $key => $value) {
            $path_parts = pathinfo($value['path']);
            if($path_parts['extension'] == "webm" || $path_parts['extension'] == "mp4"){
                $sources .= "<source src=\"{$value['url']}\" type=\"video/{$path_parts['extension']}\">";
                $obj = new stdClass();
                $obj->type = "video/{$path_parts['extension']}";
                $obj->src = $value['url'];
                $sourcesArray[] = $obj;
            }
        }
        $videoSources = $returnArray?$sourcesArray:$sources;
    }
    if(function_exists('getVTTTracks')){
        $subtitleTracks = getVTTTracks($fileName, $returnArray);
    }
    
    return $videoSources;
}

function im_resize($file_src, $file_dest, $wd, $hd) {
    if (!file_exists($file_src))
        return false;
    $size = getimagesize($file_src);
    if ($size === false)
        return false;
    if ($size['mime'] == 'image/pjpeg')
        $size['mime'] = 'image/jpeg';

    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
    $destformat = strtolower(substr($file_dest, -4));
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc))
        return false;

    $src = $icfunc($file_src);

    $ws = imagesx($src);
    $hs = imagesy($src);

    if ($ws >= $hs) {
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

    if (!isset($q))
        $q = 100;
    if ($destformat == '.png')
        $saved = imagepng($dest, $file_dest);
    if ($destformat == '.jpg')
        $saved = imagejpeg($dest, $file_dest, $q);
    if (!$saved)
        my_error_log('saving failed');

    imagedestroy($dest);
    imagedestroy($src);
    @chmod($file_dest, 0666);

    return true;
}