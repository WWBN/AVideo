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
    if (empty($text)) {
        return "";
    }
    $result = @htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    if (empty($result)) {
        $result = str_replace(array('"', "'", "\\"), array("", "", ""), strip_tags($text));
    }
    return $result;
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
    } else if ($a['name'] == 'GoogleAds_IMA') {
        return -1;
    } else if ($a['name'] == 'Subscription') {
        return -1;
    } else if ($a['name'] == 'PayPerView') {
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
        '/–/' => '-', // UTF-8 hyphen to 'normal' hyphen
        '/[’‘‹›‚]/u' => ' ', // Literally a single quote
        '/[“”«»„]/u' => ' ', // Double quote
        '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        '/Є/' => 'YE', '/І/' => 'I', '/Ѓ/' => 'G', '/і/' => 'i', '/№/' => '#', '/є/' => 'ye', '/ѓ/' => 'g',
        '/А/' => 'A', '/Б/' => 'B', '/В/' => 'V', '/Г/' => 'G', '/Д/' => 'D',
        '/Е/' => 'E', '/Ё/' => 'YO', '/Ж/' => 'ZH',
        '/З/' => 'Z', '/И/' => 'I', '/Й/' => 'J', '/К/' => 'K', '/Л/' => 'L',
        '/М/' => 'M', '/Н/' => 'N', '/О/' => 'O', '/П/' => 'P', '/Р/' => 'R',
        '/С/' => 'S', '/Т/' => 'T', '/У/' => 'U', '/Ф/' => 'F', '/Х/' => 'H',
        '/Ц/' => 'C', '/Ч/' => 'CH', '/Ш/' => 'SH', '/Щ/' => 'SHH', '/Ъ/' => '',
        '/Ы/' => 'Y', '/Ь/' => '', '/Э/' => 'E', '/Ю/' => 'YU', '/Я/' => 'YA',
        '/а/' => 'a', '/б/' => 'b', '/в/' => 'v', '/г/' => 'g', '/д/' => 'd',
        '/е/' => 'e', '/ё/' => 'yo', '/ж/' => 'zh',
        '/з/' => 'z', '/и/' => 'i', '/й/' => 'j', '/к/' => 'k', '/л/' => 'l',
        '/м/' => 'm', '/н/' => 'n', '/о/' => 'o', '/п/' => 'p', '/р/' => 'r',
        '/с/' => 's', '/т/' => 't', '/у/' => 'u', '/ф/' => 'f', '/х/' => 'h',
        '/ц/' => 'c', '/ч/' => 'ch', '/ш/' => 'sh', '/щ/' => 'shh', '/ъ/' => '',
        '/ы/' => 'y', '/ь/' => '', '/э/' => 'e', '/ю/' => 'yu', '/я/' => 'ya',
        '/—/' => '-', '/«/' => '', '/»/' => '', '/…/' => ''
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
    if (!is_numeric($seconds)) {
        return $seconds;
    }
    $seconds = round($seconds);
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    $secs = floor($seconds % 60);
    return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
}

function parseSecondsToDuration($seconds) {
    return secondsToVideoTime($seconds);
}

function parseDurationToSeconds($str) {
    if (is_numeric($str)) {
        return intval($str);
    }
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
 * call it before send mail to let AVideo decide the method
 */
function setSiteSendMessage(&$mail) {
    global $global;
    require_once $global['systemRootPath'] . 'objects/configuration.php';
    $config = new Configuration();
    $mail->CharSet = 'UTF-8';
    if ($config->getSmtp()) {
        _error_log("Sending SMTP Email");
        $mail->CharSet = 'UTF-8';
        $mail->IsSMTP(); // enable SMTP
        if (!empty($_POST) && $_POST["comment"] == "Teste of comment" && User::isAdmin()) {
            $mail->SMTPDebug = 3;
            $mail->Debugoutput = function($str, $level) {
                _error_log("SMTP ERROR $level; message: $str", AVideoLog::$ERROR);
            };
        }
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->SMTPAuth = $config->getSmtpAuth(); // authentication enabled
        $mail->SMTPSecure = $config->getSmtpSecure(); // secure transfer enabled REQUIRED for Gmail
        $mail->Host = $config->getSmtpHost();
        $mail->Port = $config->getSmtpPort();
        $mail->Username = $config->getSmtpUsername();
        $mail->Password = $config->getSmtpPassword();
//_error_log(print_r($config, true));
    } else {
        _error_log("Sending SendMail Email");
        $mail->isSendmail();
    }
}

function array_iunique($array) {
    return array_intersect_key(
            $array, array_unique(array_map("strtolower", $array))
    );
}

function partition(Array $list, $totalItens) {
    $listlen = count($list);
    _error_log("partition: listlen={$listlen} totalItens={$totalItens}");
    $p = ceil($listlen / $totalItens);
    $partlen = floor($listlen / $p);

    $partition = array();
    $mark = 0;
    for ($index = 0; $index < $p; $index++) {
        $partition[$index] = array_slice($list, $mark, $totalItens);
        $mark += $totalItens;
    }

    return $partition;
}

function sendSiteEmail($to, $subject, $message) {
    global $advancedCustom;
    if (empty($to)) {
        return false;
    }

    $subject = UTF8encode($subject);
    $message = UTF8encode($message);

    _error_log("sendSiteEmail [" . count($to) . "] {$subject}");
    global $config, $global;
    require_once $global['systemRootPath'] . 'objects/PHPMailer/src/PHPMailer.php';
    require_once $global['systemRootPath'] . 'objects/PHPMailer/src/SMTP.php';
    require_once $global['systemRootPath'] . 'objects/PHPMailer/src/Exception.php';
    $contactEmail = $config->getContactEmail();
    $webSiteTitle = $config->getWebSiteTitle();
    try {

        if (!is_array($to)) {
            $mail = new PHPMailer\PHPMailer\PHPMailer;
            setSiteSendMessage($mail);
            $mail->setFrom($contactEmail, $webSiteTitle);
            $mail->Subject = $subject . " - " . $webSiteTitle;
            $mail->msgHTML($message);

            $mail->addAddress($to);

            $resp = $mail->send();
            if (!$resp) {
                _error_log("sendSiteEmail Error Info: {$mail->ErrorInfo}");
            } else {
                _error_log("sendSiteEmail Success Info: $subject " . json_encode($to));
            }
        } else {
            $size = intval($advancedCustom->splitBulkEmailSend);
            if (empty($size)) {
                $size = 90;
            }

            $to = array_iunique($to);
            $pieces = partition($to, $size);
            foreach ($pieces as $piece) {
                $mail = new PHPMailer\PHPMailer\PHPMailer;
                setSiteSendMessage($mail);
                $mail->setFrom($contactEmail, $webSiteTitle);
                $mail->Subject = $subject . " - " . $webSiteTitle;
                $mail->msgHTML($message);
                $count = 0;
                foreach ($piece as $value) {
                    $count++;
                    _error_log("sendSiteEmail::addBCC [{$count}] {$value}");
                    $mail->addBCC($value);
                }

                $resp = $mail->send();
                if (!$resp) {
                    _error_log("sendSiteEmail Error Info: {$mail->ErrorInfo}");
                } else {
                    _error_log("sendSiteEmail Success Info: $subject " . json_encode($to));
                }
            }
        }
//Set the subject line
        return $resp;
    } catch (phpmailerException $e) {
        _error_log($e->errorMessage()); //Pretty error messages from PHPMailer
    } catch (Exception $e) {
        _error_log($e->getMessage()); //Boring error messages from anything else!
    }
}

function parseVideos($videoString = null, $autoplay = 0, $loop = 0, $mute = 0, $showinfo = 0, $controls = 1, $time = 0, $objectFit = "") {
    //_error_log("parseVideos: $videoString");
    if (strpos($videoString, 'youtube.com/embed') !== false) {
        return $videoString . (parse_url($videoString, PHP_URL_QUERY) ? '&' : '?') . 'modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time&objectFit=$objectFit";
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

    if (stripos($link, 'embed') !== false) {
        return $link . (parse_url($link, PHP_URL_QUERY) ? '&' : '?') . 'modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time&objectFit=$objectFit";
    } else if (strpos($link, 'youtube.com') !== false) {

        preg_match(
                '/[\\?\\&]v=([^\\?\\&]+)/', $link, $matches
        );
//the ID of the YouTube URL: x6qe_kVaBpg
        if (empty($matches[1])) {
            return $link;
        }
        $id = $matches[1];
        return '//www.youtube.com/embed/' . $id . '?modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&te=$time&objectFit=$objectFit";
    } else if (strpos($link, 'youtu.be') !== false) {
//https://youtu.be/9XXOBSsPoMU
        preg_match(
                '/youtu.be\/([a-zA-Z0-9_]+)($|\/)/', $link, $matches
        );
//the ID of the YouTube URL: x6qe_kVaBpg
        $id = $matches[1];
        return '//www.youtube.com/embed/' . $id . '?modestbranding=1&showinfo='
                . $showinfo . "&autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&te=$time&objectFit=$objectFit";
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
        preg_match('/\/\/(www\.)?rutube.ru\/video\/([a-zA-Z0-9_-]+)\/.*/', $link, $matches);
        $id = $matches[2];
        return '//rutube.ru/play/embed/' . $id;
    } else if (strpos($link, 'ok.ru') !== false) {
//extract the ID
        preg_match('/\/\/(www\.)?ok.ru\/video\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//ok.ru/videoembed/' . $id;
    } else if (strpos($link, 'streamable.com') !== false) {
//extract the ID
        preg_match('/\/\/(www\.)?streamable.com\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//streamable.com/s/' . $id;
    } else if (strpos($link, 'twitch.tv/videos') !== false) {
//extract the ID
        preg_match('/\/\/(www\.)?twitch.tv\/videos\/([a-zA-Z0-9_-]+)$/', $link, $matches);
        if (!empty($matches[2])) {
            $id = $matches[2];
            return '//player.twitch.tv/?video=' . $id . '#';
        }
//extract the ID
        preg_match('/\/\/(www\.)?twitch.tv\/[a-zA-Z0-9_-]+\/v\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//player.twitch.tv/?video=' . $id . '#';
    } else if (strpos($link, 'twitch.tv') !== false) {
//extract the ID
        preg_match('/\/\/(www\.)?twitch.tv\/([a-zA-Z0-9_-]+)$/', $link, $matches);

        $id = $matches[2];
        return '//player.twitch.tv/?channel=' . $id . '#';
    } else if (strpos($link, '/evideo/') !== false) {
//extract the ID
        preg_match('/(http.+)\/evideo\/([a-zA-Z0-9_-]+)($|\/)/i', $link, $matches);

//the AVideo site
        $site = $matches[1];
        $id = $matches[2];
        return $site . '/evideoEmbed/' . $id . "?autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
    } else if (strpos($link, '/video/') !== false) {
//extract the ID
        preg_match('/(http.+)\/video\/([a-zA-Z0-9_-]+)($|\/)/i', $link, $matches);

//the AVideo site
        if (!empty($matches[1])) {
            $site = $matches[1];
            $id = $matches[2];
            return $site . '/videoEmbeded/' . $id . "?autoplay={$autoplay}&controls=$controls&loop=$loop&mute=$mute&t=$time";
        } else {
            return $link;
        }
    }

    $url = $videoString;
    $url_parsed = parse_url($url);
    if (empty($url_parsed['query'])) {
        return "";
    }
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
        $pvr360 = AVideoPlugin::isEnabledByName('VR360');
// if the VR360 is enabled you can not use the CDN, it fail to load the GL
        $isVR360Enabled = VideosVR360::isVR360Enabled($videos_id);
        if ($pvr360 && $isVR360Enabled) {
            $ret = false;
        } else {
            $ret = true;
        }

//_error_log(json_encode(array('canUseCDN'=>$ret, '$pvr360'=>$pvr360, '$isVR360Enabled'=>$isVR360Enabled, '$videos_id'=>$videos_id)));
        $canUseCDN[$videos_id] = $ret;
    }
    return $canUseCDN[$videos_id];
}

function clearVideosURL($fileName = "") {
    global $global;
    $path = getCacheDir() . "getVideosURL/";
    if (empty($path)) {
        rrmdir($path);
    } else {
        $cacheFilename = "{$path}{$fileName}.cache";
        @unlink($cacheFilename);
    }
}

$minimumExpirationTime = false;

function minimumExpirationTime() {
    global $minimumExpirationTime;
    if (empty($minimumExpirationTime)) {
        $aws_s3 = AVideoPlugin::getObjectDataIfEnabled('AWS_S3');
        $bb_b2 = AVideoPlugin::getObjectDataIfEnabled('Blackblaze_B2');
        $secure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
        $minimumExpirationTime = 60 * 60 * 24 * 365; //1 year
        if (!empty($aws_s3) && $aws_s3->presignedRequestSecondsTimeout < $minimumExpirationTime) {
            $minimumExpirationTime = $aws_s3->presignedRequestSecondsTimeout;
        }
        if (!empty($bb_b2) && $bb_b2->presignedRequestSecondsTimeout < $minimumExpirationTime) {
            $minimumExpirationTime = $bb_b2->presignedRequestSecondsTimeout;
        }
        if (!empty($secure) && $secure->tokenTimeOut < $minimumExpirationTime) {
            $minimumExpirationTime = $secure->tokenTimeOut;
        }
    }
    return $minimumExpirationTime;
}

$cacheExpirationTime = false;

function cacheExpirationTime() {
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

/**
 * tell if a file should recreate a cache, based on its time and the plugins toke expirations
 * @param type $filename
 * @return boolean
 */
function recreateCache($filename) {
    if (!file_exists($filename) || time() - filemtime($filename) > minimumExpirationTime()) {
        return true;
    }
    return false;
}

function _getImagesURL($fileName, $type) {
    global $global;
    $files = array();
    $source = Video::getSourceFile($fileName, ".jpg");
    $file = $source['path'];
    if (file_exists($file)) {
        $files["jpg"] = array(
            'filename' => "{$fileName}.jpg",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'image',
        );
    } else {
        $files["jpg"] = array(
            'filename' => "{$type}.png",
            'path' => "{$global['systemRootPath']}view/img/{$type}.png",
            'url' => "{$global['webSiteRootURL']}view/img/{$type}.png",
            'type' => 'image',
        );
    }
    $source = Video::getSourceFile($fileName, "_portrait.jpg");
    $file = $source['path'];
    if (file_exists($file)) {
        $files["pjpg"] = array(
            'filename' => "{$fileName}_portrait.jpg",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'image',
        );
    } else if ($type != 'image') {
        $files["pjpg"] = array(
            'filename' => "{$type}_portrait.png",
            'path' => "{$global['systemRootPath']}view/img/{$type}_portrait.png",
            'url' => "{$global['webSiteRootURL']}view/img/{$type}_portrait.png",
            'type' => 'image',
        );
    }
    return $files;
}

function getVideosURLPDF($fileName) {
    global $global;
    if (empty($fileName)) {
        return array();
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $source = Video::getSourceFile($fileName, ".pdf");
    $file = $source['path'];
    $files["pdf"] = array(
        'filename' => "{$fileName}.pdf",
        'path' => $file,
        'url' => $source['url'],
        'type' => 'pdf',
    );
    $files = array_merge($files, _getImagesURL($fileName, 'pdf'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLIMAGE($fileName) {
    global $global;
    if (empty($fileName)) {
        return array();
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $types = array('png', 'gif', 'webp', 'jpg');

    foreach ($types as $value) {
        $source = Video::getSourceFile($fileName, ".{$value}");
        $file = $source['path'];
        $files["image"] = array(
            'filename' => "{$fileName}.{$value}",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'image',
        );
        if (file_exists($file)) {
            break;
        }
    }

    $files = array_merge($files, _getImagesURL($fileName, 'image'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLZIP($fileName) {
    global $global;
    if (empty($fileName)) {
        return array();
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $types = array('zip');

    foreach ($types as $value) {
        $source = Video::getSourceFile($fileName, ".{$value}");
        $file = $source['path'];
        $files["zip"] = array(
            'filename' => "{$fileName}.zip",
            'path' => $file,
            'url' => $source['url'],
            'type' => 'zip',
        );
        if (file_exists($file)) {
            break;
        }
    }

    $files = array_merge($files, _getImagesURL($fileName, 'zip'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLArticle($fileName) {
    global $global;
    if (empty($fileName)) {
        return array();
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    $files = array_merge($files, _getImagesURL($fileName, 'article'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLPDF generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURLAudio($fileName) {
    global $global;
    if (empty($fileName)) {
        return array();
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $source = Video::getSourceFile($fileName, ".mp3");
    $file = $source['path'];
    $files["mp3"] = array(
        'filename' => "{$fileName}.mp3",
        'path' => $file,
        'url' => $source['url'],
        'type' => 'audio',
    );


    $files = array_merge($files, _getImagesURL($fileName, 'audio_wave'));
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURLAudio generated in {$total_time} seconds. fileName: $fileName ");
    return $files;
}

function getVideosURL($fileName, $cache = true) {
    global $global;
    if (empty($fileName)) {
        return array();
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    $path = getCacheDir() . "getVideosURL/";
    make_path($path);
    $cacheFilename = "{$path}{$fileName}.cache";
    //var_dump($cacheFilename, recreateCache($cacheFilename), minimumExpirationTime());$pdf = "{$global['systemRootPath']}videos/{$fileName}.pdf";
    $pdf = "{$global['systemRootPath']}videos/{$fileName}.pdf";
    $mp3 = "{$global['systemRootPath']}videos/{$fileName}.mp3";
    if (file_exists($pdf)) {
        return getVideosURLPDF($fileName);
    } else if (file_exists($mp3)) {
        return getVideosURLAudio($fileName);
    } else
    if (file_exists($cacheFilename) && $cache && !recreateCache($cacheFilename)) {
        $json = file_get_contents($cacheFilename);
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);
        //_error_log("getVideosURL Cache in {$total_time} seconds. fileName: $fileName ");
        //_error_log("getVideosURL age: " . (time() - filemtime($cacheFilename)) . " minimumExpirationTime: " . minimumExpirationTime());
        return object_to_array(json_decode($json));
    }
    global $global;
    $types = array('', '_Low', '_SD', '_HD');
    $files = array();
// old
    require_once $global['systemRootPath'] . 'objects/video.php';

    $plugin = AVideoPlugin::loadPluginIfEnabled("VideoHLS");
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
            } else {
                $files["jpg"] = array(
                    'filename' => "notfound.jpg",
                    'path' => "{$global['systemRootPath']}view/img/notfound.jpg",
                    'url' => "{$global['webSiteRootURL']}view/img/notfound.jpg",
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
            } else {
                $files["gif"] = array(
                    'filename' => "static2.jpg",
                    'path' => "{$global['systemRootPath']}view/img/static2.jpg",
                    'url' => "{$global['webSiteRootURL']}view/img/static2.jpg",
                    'type' => 'image',
                );
            }
            $source = Video::getSourceFile($filename, ".webp");
            $file = $source['path'];
            if (file_exists($file)) {
                $files["gif"] = array(
                    'filename' => "{$fileName}.webp",
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
            } else {
                $files["pjpg"] = array(
                    'filename' => "notfound_portrait.jpg",
                    'path' => "{$global['systemRootPath']}view/img/notfound_portrait.jpg",
                    'url' => "{$global['webSiteRootURL']}view/img/notfound_portrait.jpg",
                    'type' => 'image',
                );
            }
        }
        make_path($cacheFilename);
        file_put_contents($cacheFilename, json_encode($files));
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);
    //_error_log("getVideosURL generated in {$total_time} seconds. fileName: $fileName ");
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
        _error_log("im_resize: Source not found: {$file_src}");
        return false;
    }
    $size = getimgsize($file_src);
    if ($size === false) {
        _error_log("im_resize: Could not get image size: {$file_src}");
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
        _error_log("destformat not found {$file_dest}");
        $destformat = ".jpg";
    }
    $icfunc = "imagecreatefrom" . $format;
    if (!function_exists($icfunc)) {
        _error_log("im_resize: Function does not exists: {$icfunc}");
        return false;
    }

    $imgSize = getimagesize($file_src);
    if (empty($imgSize)) {
        _error_log("im_resize: getimagesize($file_src) return false " . json_encode($imgSize));
        return false;
    }
    try {
        $src = $icfunc($file_src);
    } catch (Exception $exc) {
        _error_log("im_resize: " . $exc->getMessage());
        _error_log("im_resize: Try {$icfunc} from string");
        $src = imagecreatefromstring(file_get_contents($file_src));
        if (!$src) {
            _error_log("im_resize: fail {$icfunc} from string");
            return false;
        }
    }



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
            imagefilledrectangle($dest, 0, 0, $wd, $hd, $transparent);

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
        _error_log('saving failed');
    }

    imagedestroy($dest);
    imagedestroy($src);
    @chmod($file_dest, 0666);

    return true;
}

function im_resizeV2($file_src, $file_dest, $wd, $hd, $q = 80) {

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

function im_resize_max_size($file_src, $file_dest, $max_width, $max_height) {
    $fn = $file_src;
    $size = getimagesize($fn);
    $ratio = $size[0] / $size[1]; // width/height
    if ($size[0] <= $max_width && $size[1] <= $max_height) {
        $width = $size[0];
        $height = $size[1];
    } else
    if ($ratio > 1) {
        $width = $max_width;
        $height = $max_height / $ratio;
    } else {
        $width = $max_width * $ratio;
        $height = $max_height;
    }
    $src = imagecreatefromstring(file_get_contents($fn));
    $dst = imagecreatetruecolor($width, $height);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
    imagedestroy($src);
    imagejpeg($dst, $file_dest); // adjust format as needed
    imagedestroy($dst);
}

function convertImage($originalImage, $outputImage, $quality) {
    // jpg, png, gif or bmp?
    $exploded = explode('.', $originalImage);
    $ext = $exploded[count($exploded) - 1];

    if (preg_match('/jpg|jpeg/i', $ext))
        $imageTmp = imagecreatefromjpeg($originalImage);
    else if (preg_match('/png/i', $ext))
        $imageTmp = imagecreatefrompng($originalImage);
    else if (preg_match('/gif/i', $ext))
        $imageTmp = imagecreatefromgif($originalImage);
    else if (preg_match('/bmp/i', $ext))
        $imageTmp = imagecreatefrombmp($originalImage);
    else if (preg_match('/webp/i', $ext))
        $imageTmp = imagecreatefromwebp($originalImage);
    else
        return 0;

    // quality is a value from 0 (worst) to 100 (best)
    imagejpeg($imageTmp, $outputImage, $quality);
    imagedestroy($imageTmp);

    return 1;
}

function decideMoveUploadedToVideos($tmp_name, $filename, $type = "video") {
    global $global;
    $obj = new stdClass();
    $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');

    _error_log("decideMoveUploadedToVideos: {$filename}");
    $path_info = pathinfo($filename);
    if ($type !== "zip" && $path_info['extension'] === 'zip') {
        _error_log("decideMoveUploadedToVideos: ZIp file {$filename}");
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
        _error_log("decideMoveUploadedToVideos: NOT ZIp file {$filename}");
        if (!empty($aws_s3)) {
            _error_log("decideMoveUploadedToVideos: S3 {$filename}");
            $aws_s3->move_uploaded_file($tmp_name, $filename);
        } else if (!empty($bb_b2)) {
            _error_log("decideMoveUploadedToVideos: B2 {$filename}");
            $bb_b2->move_uploaded_file($tmp_name, $filename);
        } else if (!empty($ftp)) {
            _error_log("decideMoveUploadedToVideos: FTP {$filename}");
            $ftp->move_uploaded_file($tmp_name, $filename);
        } else {
            $destinationFile = "{$global['systemRootPath']}videos/{$filename}";
            _error_log("decideMoveUploadedToVideos: Local {$filename}");
            if (!move_uploaded_file($tmp_name, $destinationFile)) {
                if (!rename($tmp_name, $destinationFile)) {
                    if (!copy($tmp_name, $destinationFile)) {
                        $obj->msg = "Error on decideMoveUploadedToVideos({$tmp_name}, $destinationFile)";
                        die(json_encode($obj));
                    }
                }
            }
            chmod($destinationFile, 0644);
        }
    }
}

function unzipDirectory($filename, $destination) {
    global $global;
    // wait a couple of seconds to make sure the file is completed transfer
    sleep(2);
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 7200); // 2 hours
    $cmd = "unzip {$filename} -d {$destination}" . "  2>&1";
    _error_log("unzipDirectory: {$cmd}");
    exec($cmd, $output, $return_val);
    if ($return_val !== 0 && function_exists("zip_open")) {
// try to unzip using PHP
        _error_log("unzipDirectory: TRY to use PHP {$filename}");
        $zip = zip_open($filename);
        if ($zip) {
            while ($zip_entry = zip_read($zip)) {
                $path = "{$destination}/" . zip_entry_name($zip_entry);
                _error_log("unzipDirectory: fopen $path");
                if (substr(zip_entry_name($zip_entry), -1) == '/') {
                    make_path($path);
                } else {
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
        } else {
            _error_log("unzipDirectory: ERROR php zip does not work");
        }
    } else {
        _error_log("unzipDirectory: Success {$destination}");
    }
    @unlink($filename);
}

function make_path($path) {
    if (substr($path, -1) !== '/') {
        $path = pathinfo($path, PATHINFO_DIRNAME);
    }
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }
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
    $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
    $bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
    $ftp = AVideoPlugin::loadPluginIfEnabled('FTP_Storage');
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

function fontAwesomeClassName($filename) {
    $mime_type = mime_content_type_per_filename($filename);
    // List of official MIME Types: http://www.iana.org/assignments/media-types/media-types.xhtml
    $icon_classes = array(
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
    );
    foreach ($icon_classes as $text => $icon) {
        if (strpos($mime_type, $text) === 0) {
            return $icon;
        }
    }
    return 'fas fa-file';
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
    $cacheDir = $global['systemRootPath'] . 'videos/cache/' . $extension . "/";
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
        if ((($extension == "js" || $extension == "css") && ($minifyEnabled))) {
            require_once $global['systemRootPath'] . 'objects/jshrink.php';
            $str = \JShrink\Minifier::minify($str, array('flaggedComments' => false));
        }
        file_put_contents($cacheDir . $md5FileName, $str);
    }
    return $global['webSiteRootURL'] . 'videos/cache/' . $extension . "/" . $md5FileName . "?" . filectime($cacheDir . $md5FileName);
}

function local_get_contents($path) {
    if (function_exists('fopen')) {
        $myfile = fopen($path, "r") or die("Unable to open file!");
        $text = fread($myfile, filesize($path));
        fclose($myfile);
        return $text;
    }
}

function url_get_contents($url, $ctx = "", $timeout = 0, $debug = false) {
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
    if ($debug) {
        _error_log("url_get_contents: Start $url, $ctx, $timeout");
    }
    if (filter_var($url, FILTER_VALIDATE_URL)) {

        $session = $_SESSION;
        session_write_close();
        if (!empty($timeout)) {
            if ($debug) {
                _error_log("url_get_contents: no timout {$url}");
            }
            ini_set('default_socket_timeout', $timeout);
        }
        @$global['mysqli']->close();
    }

    if (empty($ctx)) {
        $opts = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true,
            ),
        );
        if (!empty($timeout)) {
            ini_set('default_socket_timeout', $timeout);
            $opts['http'] = array('timeout' => $timeout);
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
            $tmp = @file_get_contents($url, false, $context);
            if ($tmp != false) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    _session_start();
                    $_SESSION = $session;
                    _mysql_connect();
                }
                if ($debug) {
                    _error_log("url_get_contents: SUCCESS file_get_contents($url) ");
                }
                return remove_utf8_bom($tmp);
            }
            if ($debug) {
                _error_log("url_get_contents: ERROR file_get_contents($url) ");
            }
        } catch (ErrorException $e) {
            if ($debug) {
                _error_log("url_get_contents: allow_url_fopen ERROR " . $e->getMessage() . "  {$url}");
            }
            return "url_get_contents: " . $e->getMessage();
        }
    } else if (function_exists('curl_init')) {
        if ($debug) {
            _error_log("url_get_contents: CURL  {$url} ");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (!empty($timeout)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout + 10);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            _session_start();
            $_SESSION = $session;
            _mysql_connect();
        }
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
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                _session_start();
                $_SESSION = $session;
                _mysql_connect();
            }
            return remove_utf8_bom($result);
        }
    } else if ($debug) {
        _error_log("url_get_contents: try wget fail {$url}");
    }

    $result = @file_get_contents($url, false, $context);
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        _session_start();
        $_SESSION = $session;
        _mysql_connect();
    }
    if ($debug) {
        _error_log("url_get_contents: Last try  {$url}");
    }
    return remove_utf8_bom($result);
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
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }
// User lowercase string for comparison.
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
// A list of some common words used only for bots and crawlers.
    $bot_identifiers = array(
        'bot',
        'slurp',
        'crawler',
        'spider',
        'curl',
        'facebook',
        'fetch',
        'loader',
    );
// See if one of the identifiers is in the UA string.
    foreach ($bot_identifiers as $identifier) {
        if (strpos($user_agent, $identifier) !== FALSE) {
            return TRUE;
        }
    }
    return FALSE;
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
        _error_log("encryptPasswordVerify: encrypt");
        $passwordSalted = encryptPassword($password);
// in case you enable the salt later
        $passwordUnSalted = encryptPassword($password, true);
    } else {
        _error_log("encryptPasswordVerify: do not encrypt");
        $passwordSalted = $password;
// in case you enable the salt later
        $passwordUnSalted = $password;
    }
//_error_log("passwordSalted = $passwordSalted,  hash=$hash, passwordUnSalted=$passwordUnSalted");
    return $passwordSalted === $hash || $passwordUnSalted === $hash || $password === $hash;
}

function isMobile() {
    if (empty($_SERVER["HTTP_USER_AGENT"])) {
        return false;
    }
    global $global;
    require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
    $detect = new Mobile_Detect;

    return $detect->isMobile();
}

function siteMap() {
    _error_log("siteMap: start");
    ini_set('memory_limit', '-1');
    ini_set('max_execution_time', 0);
    global $global, $advancedCustom;
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

    $_POST['rowCount'] = $advancedCustom->siteMapRowsLimit;
    _error_log("siteMap: rowCount {$_POST['rowCount']} ");
    $_POST['sort']['modified'] = "DESC";
    $users = User::getAllUsersThatHasVideos(true);
    _error_log("siteMap: getAllUsers " . count($users));
    foreach ($users as $value) {
        $xml .= '        
            <url>
                <loc>' . User::getChannelLink($value['id']) . '</loc>
                <lastmod>' . $date . '</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.90</priority>
            </url>
            ';
    }
    $xml .= ' 
        <!-- Categories -->
        ';
    $_POST['rowCount'] = $advancedCustom->siteMapRowsLimit;
    $_POST['sort']['modified'] = "DESC";
    $rows = Category::getAllCategories();
    _error_log("siteMap: getAllCategories " . count($rows));
    foreach ($rows as $value) {
        $xml .= '  
            <url>
                <loc>' . $global['webSiteRootURL'] . 'cat/' . $value['clean_name'] . '</loc>
                <lastmod>' . $date . '</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.80</priority>
            </url>
            ';
    }
    $xml .= '<!-- Videos -->';
    $_POST['rowCount'] = $advancedCustom->siteMapRowsLimit * 10;
    $_POST['sort']['created'] = "DESC";
    $rows = Video::getAllVideos(!empty($advancedCustom->showPrivateVideosOnSitemap) ? "viewableNotUnlisted" : "publicOnly");
    _error_log("siteMap: getAllVideos " . count($rows));
    foreach ($rows as $video) {
        $videos_id = $video['id'];
        //_error_log("siteMap: getAllVideos videos_id {$videos_id} start");
        $source = Video::getSourceFile($video['filename']);
        if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
            $img = $source['url'];
            $data = getimgsize($source['path']);
            $imgw = $data[0];
            $imgh = $data[1];
        } else if ($video['type'] == "audio") {
            $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
        }
        $type = 'video';
        if ($video['type'] === 'pdf') {
            $type = 'pdf';
        }
        if ($video['type'] === 'article') {
            $type = 'article';
        }
        $images = Video::getImageFromFilename($video['filename'], $type);
        if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
            $img = $images->posterPortrait;
            $data = getimgsize($images->posterPortraitPath);
            $imgw = $data[0];
            $imgh = $data[1];
        } else {
            $img = $images->poster;
        }

        $description = str_replace(array('"', "\n", "\r"), array('', ' ', ' '), empty(trim($video['description'])) ? $video['title'] : $video['description']);
        $duration = parseDurationToSeconds($video['duration']);
        if ($duration > 28800) {
            // this is because this issue https://github.com/WWBN/AVideo/issues/3338 remove in the future if is not necessary anymore
            $duration = 28800;
        }
        $xml .= '
            <url>
                <loc>' . Video::getLink($video['id'], $video['clean_title']) . '</loc>
                <video:video>
                    <video:thumbnail_loc>' . $img . '</video:thumbnail_loc>
                    <video:title>' . str_replace('"', '', $video['title']) . '</video:title>
                    <video:description><![CDATA[' . substr(strip_tags($description), 0, 2048) . ']]></video:description>
                    <video:player_loc>' . htmlentities(parseVideos(Video::getLinkToVideo($videos_id))) . '</video:player_loc>
                    <video:duration>' . $duration . '</video:duration>
                    <video:view_count>' . $video['views_count'] . '</video:view_count>
                    <video:publication_date>' . date("Y-m-d\TH:i:s", strtotime($video['created'])) . '+00:00</video:publication_date>
                    <video:family_friendly>yes</video:family_friendly>
                    <video:requires_subscription>' . (Video::isPublic($video['id']) ? "no" : "yes") . '</video:requires_subscription>
                    <video:uploader info="' . User::getChannelLink($video['users_id']) . '">' . User::getNameIdentificationById($video['users_id']) . '</video:uploader>
                    <video:live>no</video:live>
                </video:video>
            </url>
            ';
    }
    $xml .= '</urlset> ';
    _error_log("siteMap: done ");
    $newXML1 = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xml);
    if (empty($newXML1)) {
        _error_log("siteMap: pregreplace1 fail ");
        $newXML1 = $xml;
    }
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

    return $newXML5;
}

function object_to_array($obj) {
//only process if it's an object or array being passed to the function
    if (is_object($obj) || is_array($obj)) {
        $ret = (array) $obj;
        foreach ($ret as &$item) {
//recursively process EACH element regardless of type
            $item = object_to_array($item);
        }
        return $ret;
    }
//otherwise (i.e. for scalar values) return without modification
    else {
        return $obj;
    }
}

function allowOrigin() {
    global $global;
    if (empty($_SERVER['HTTP_ORIGIN'])) {
        $server = parse_url($global['webSiteRootURL']);
        header('Access-Control-Allow-Origin: ' . $server["scheme"] . '://imasdk.googleapis.com');
    } else {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    }
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods", "GET,HEAD,OPTIONS,POST,PUT");
    header("Access-Control-Allow-Headers", "Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
}

function rrmdir($dir) {
    global $global;
    if ($dir == "{$global['systemRootPath']}videos/" || $dir == "{$global['systemRootPath']}videos") {
        return false;
    }
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object))
                    rrmdir($dir . "/" . $object);
                else
                    unlink($dir . "/" . $object);
            }
        }
        rmdir($dir);
    }
}

/**
 * You can now configure it on the configuration.php
 * @return boolean
 */
function ddosProtection() {
    global $global;
    $maxCon = empty($global['ddosMaxConnections']) ? 40 : $global['ddosMaxConnections'];
    $secondTimeout = empty($global['ddosSecondTimeout']) ? 5 : $global['ddosSecondTimeout'];
    $whitelistedFiles = array(
        'playlists.json.php',
        'playlistsFromUserVideos.json.php'
    );

    if (in_array(basename($_SERVER["SCRIPT_FILENAME"]), $whitelistedFiles)) {
        return true;
    }

    $time = time();
    if (!isset($_SESSION['bruteForceBlock']) || empty($_SESSION['bruteForceBlock'])) {
        $_SESSION['bruteForceBlock'] = array();
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

function getAdsLeaderBoardBigVideo() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return trim($ad->leaderBoardBigVideoMobile->value);
        } else {
            return trim($ad->leaderBoardBigVideo->value);
        }
    }
}

function getAdsLeaderBoardTop() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return $ad->leaderBoardTopMobile->value;
        } else {
            return $ad->leaderBoardTop->value;
        }
    }
}

function getAdsChannelLeaderBoardTop() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return $ad->channelLeaderBoardTopMobile->value;
        } else {
            return $ad->channelLeaderBoardTop->value;
        }
    }
}

function getAdsLeaderBoardTop2() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return $ad->leaderBoardTopMobile2->value;
        } else {
            return $ad->leaderBoardTop2->value;
        }
    }
}

function getAdsLeaderBoardMiddle() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return $ad->leaderBoardMiddleMobile->value;
        } else {
            return $ad->leaderBoardMiddle->value;
        }
    }
}

function getAdsLeaderBoardFooter() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return $ad->leaderBoardFooterMobile->value;
        } else {
            return $ad->leaderBoardFooter->value;
        }
    }
}

function getAdsSideRectangle() {
    $ad = AVideoPlugin::getObjectDataIfEnabled('ADs');
    if (!empty($ad)) {
        if (isMobile()) {
            return $ad->sideRectangle->value;
        } else {
            return $ad->sideRectangle->value;
        }
    }
}

function isToHidePrivateVideos() {
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

function ogSite() {
    global $global, $config;
    echo "<!-- OpenGraph for the Site -->";
    if ($user_id = isChannel()) {
        $imgw = 200;
        $imgh = 200;
        $img = User::getPhoto($user_id);
        $title = User::getNameIdentificationById($user_id);
        ?>
        <meta property="og:type" content="profile" />
        <meta property="profile:username" content="<?php echo $title; ?>" />
        <?php
    } else if (!isVideo()) {
        $imgw = 200;
        $imgh = 200;
        $img = $config->getFavicon(true, false);
        $title = html2plainText($config->getWebSiteTitle());
        ?>
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="<?php echo $title; ?>">
        <?php
    } else {
        return false;
    }
    ?>
    <link rel="canonical" href="<?php echo $global['webSiteRootURL']; ?>">
    <link rel="image_src" href="<?php echo $img; ?>" />
    <meta property="og:image" content="<?php echo $img; ?>" />
    <meta property="og:image:url" content="<?php echo $img; ?>" />
    <meta property="og:image:secure_url" content="<?php echo $img; ?>" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
    <meta property="og:image:height"       content="<?php echo $imgh; ?>" />

    <meta property="fb:app_id"             content="774958212660408" />
    <meta property="og:title"              content="<?php echo $title; ?>" />
    <meta property="og:description"        content="<?php echo $title; ?>" />
    <meta property="og:url"                content="<?php echo $global['webSiteRootURL']; ?>" />

    <?php
    if (!empty($advancedCustom->twitter_summary_large_image)) {
        ?>
        <meta name="twitter:card" content="summary_large_image" />   
        <?php
    } else {
        ?>
        <meta name="twitter:card" content="summary" />   
        <?php
    }
    ?>
    <meta name="twitter:url" content="<?php echo $global['webSiteRootURL']; ?>"/>
    <meta name="twitter:title" content="<?php echo $title; ?>"/>
    <meta name="twitter:description" content="<?php echo $title; ?>"/>
    <meta name="twitter:image" content="<?php echo $img; ?>"/>
    <?php
}

function getOpenGraph($videos_id) {
    global $global, $config, $advancedCustom;
    echo "<!-- OpenGraph -->";
    if (empty($videos_id)) {
        echo "<!-- OpenGraph no video id -->";
        if (!empty($_GET['videoName'])) {
            echo "<!-- OpenGraph videoName {$_GET['videoName']} -->";
            $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        }
    } else {
        echo "<!-- OpenGraph videos_id {$videos_id} -->";
        $video = Video::getVideoLight($videos_id);
    }
    if (empty($video)) {
        echo "<!-- OpenGraph no video -->";
        return false;
    }
    $videos_id = $video['id'];
    $source = Video::getSourceFile($video['filename']);
    $imgw = 1024;
    $imgh = 768;
    if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } else if ($video['type'] == "audio") {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
    $type = 'video';
    if ($video['type'] === 'pdf') {
        $type = 'pdf';
    }
    if ($video['type'] === 'article') {
        $type = 'article';
    }
    $images = Video::getImageFromFilename($video['filename'], $type);
    if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
        $img = $images->posterPortrait;
        $data = getimgsize($images->posterPortraitPath);
        $imgw = $data[0];
        $imgh = $data[1];
    } else {
        $img = $images->poster;
    }
    $twitter_site = $advancedCustom->twitter_site;
    ?>
    <link rel="image_src" href="<?php echo $img; ?>" />
    <meta property="og:image" content="<?php echo $img; ?>" />
    <meta property="og:image:secure_url" content="<?php echo $img; ?>" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
    <meta property="og:image:height"       content="<?php echo $imgh; ?>" />

    <meta property="fb:app_id"             content="774958212660408" />
    <meta property="og:title"              content="<?php echo html2plainText($video['title']); ?>" />
    <meta property="og:description"        content="<?php echo html2plainText($video['description']); ?>" />
    <meta property="og:url"                content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
    <meta property="og:type"               content="video.other" />

    <?php
    $sourceMP4 = Video::getSourceFile($video['filename'], ".mp4");
    if (!AVideoPlugin::isEnabledByName("SecureVideosDirectory") && !empty($sourceMP4['url'])) {
        ?>
        <meta property="og:video" content="<?php echo $sourceMP4['url']; ?>" />
        <meta property="og:video:secure_url" content="<?php echo $sourceMP4['url']; ?>" />
        <meta property="og:video:type" content="video/mp4" />
        <meta property="og:video:width" content="<?php echo $imgw; ?>" />
        <meta property="og:video:height" content="<?php echo $imgh; ?>" />
        <?php
    } else {
        ?>
        <meta property="og:video" content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
        <meta property="og:video:secure_url" content="<?php echo Video::getLinkToVideo($videos_id); ?>" />
        <?php
    }
    ?>
    <meta property="video:duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />
    <meta property="duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />

    <!-- Twitter cards -->
    <?php
    if (!empty($advancedCustom->twitter_player)) {
        ?>
        <meta name="twitter:card" content="player" />
        <meta name="twitter:player" content="<?php echo Video::getLinkToVideo($videos_id, $video['clean_title'], true); ?>" />
        <meta name="twitter:player:width" content="480" />
        <meta name="twitter:player:height" content="480" />    
        <?php
    } else {
        if (!empty($advancedCustom->twitter_summary_large_image)) {
            ?>
            <meta name="twitter:card" content="summary_large_image" />   
            <?php
        } else {
            ?>
            <meta name="twitter:card" content="summary" />   
            <?php
        }
    }
    ?>
    <meta name="twitter:site" content="<?php echo $twitter_site; ?>" />
    <meta name="twitter:url" content="<?php echo Video::getLinkToVideo($videos_id); ?>"/>
    <meta name="twitter:title" content="<?php echo html2plainText($video['title']); ?>"/>
    <meta name="twitter:description" content="<?php echo html2plainText($video['description']); ?>"/>
    <meta name="twitter:image" content="<?php echo $img; ?>"/>
    <?php
}

function getLdJson($videos_id) {
    $cache = ObjectYPT::getCache("getLdJson{$videos_id}", 0);
    if (empty($cache)) {
        echo $cache;
    }
    global $global, $config;
    echo "<!-- ld+json -->";
    if (empty($videos_id)) {
        echo "<!-- ld+json no video id -->";
        if (!empty($_GET['videoName'])) {
            echo "<!-- ld+json videoName {$_GET['videoName']} -->";
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
    $source = Video::getSourceFile($video['filename']);
    if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } else if ($video['type'] == "audio") {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
    $type = 'video';
    if ($video['type'] === 'pdf') {
        $type = 'pdf';
    }
    if ($video['type'] === 'article') {
        $type = 'article';
    }
    $images = Video::getImageFromFilename($video['filename'], $type);
    if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
        $img = $images->posterPortrait;
        $data = getimgsize($images->posterPortraitPath);
        $imgw = $data[0];
        $imgh = $data[1];
    } else {
        $img = $images->poster;
    }

    $description = html2plainText(empty(trim($video['description'])) ? $video['title'] : $video['description']);
    $duration = Video::getItemPropDuration($video['duration']);
    if ($duration == "PT0H0M0S") {
        $duration = "PT0H0M1S";
    }
    $output = '
    <script type="application/ld+json">
        {
        "@context": "http://schema.org/",
        "@type": "VideoObject",
        "name": "' . html2plainText($video['title']) . '",
        "description": "' . $description . '",
        "thumbnailUrl": [
        "' . $img . '"
        ],
        "uploadDate": "' . date("Y-m-d\Th:i:s", strtotime($video['created'])) . '",
        "duration": "' . $duration . '",
        "contentUrl": "' . Video::getLinkToVideo($videos_id) . '",
        "embedUrl": "' . parseVideos(Video::getLinkToVideo($videos_id)) . '",
        "interactionCount": "' . $video['views_count'] . '",
        "@id": "' . Video::getPermaLink($videos_id) . '",
        "datePublished": "' . date("Y-m-d", strtotime($video['created'])) . '",
        "interactionStatistic": [
        {
        "@type": "InteractionCounter",
        "interactionService": {
        "@type": "WebSite",
        "name": "' . str_replace('"', '', $config->getWebSiteTitle()) . '",
        "@id": "' . $global['webSiteRootURL'] . '"
        },
        "interactionType": "http://schema.org/LikeAction",
        "userInteractionCount": "' . $video['views_count'] . '"
        },
        {
        "@type": "InteractionCounter",
        "interactionType": "http://schema.org/WatchAction",
        "userInteractionCount": "' . $video['views_count'] . '"
        }
        ]
        }
    </script>';
    ObjectYPT::setCache("getLdJson{$videos_id}", $output);
    echo $output;
}

function getItemprop($videos_id) {
    $cache = ObjectYPT::getCache("getItemprop{$videos_id}", 0);
    if (empty($cache)) {
        echo $cache;
    }
    global $global, $config;
    echo "<!-- Itemprop -->";
    if (empty($videos_id)) {
        echo "<!-- Itemprop no video id -->";
        if (!empty($_GET['videoName'])) {
            echo "<!-- Itemprop videoName {$_GET['videoName']} -->";
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
    $source = Video::getSourceFile($video['filename']);
    if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } else if ($video['type'] == "audio") {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
    $type = 'video';
    if ($video['type'] === 'pdf') {
        $type = 'pdf';
    }
    if ($video['type'] === 'image') {
        $type = 'image';
    }
    if ($video['type'] === 'zip') {
        $type = 'zip';
    }
    if ($video['type'] === 'article') {
        $type = 'article';
    }
    $images = Video::getImageFromFilename($video['filename'], $type);
    if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
        $img = $images->posterPortrait;
        $data = getimgsize($images->posterPortraitPath);
        $imgw = $data[0];
        $imgh = $data[1];
    } else {
        $img = $images->poster;
    }

    $description = html2plainText(empty(trim($video['description'])) ? $video['title'] : $video['description']);
    $duration = Video::getItemPropDuration($video['duration']);
    if ($duration == "PT0H0M0S") {
        $duration = "PT0H0M1S";
    }
    $output = '<span itemprop="name" content="' . str_replace('"', '', $video['title']) . '" />
    <span itemprop="description" content="' . $description . '" />
    <span itemprop="thumbnailUrl" content="' . $img . '" />
    <span itemprop="uploadDate" content="' . date("Y-m-d\Th:i:s", strtotime($video['created'])) . '" />
    <span itemprop="duration" content="' . $duration . '" />
    <span itemprop="contentUrl" content="' . Video::getLinkToVideo($videos_id) . '" />
    <span itemprop="embedUrl" content="' . parseVideos(Video::getLinkToVideo($videos_id)) . '" />
    <span itemprop="interactionCount" content="' . $video['views_count'] . '" />';

    ObjectYPT::setCache("getItemprop{$videos_id}", $output);
    echo $output;
}

function get_browser_name($user_agent = "") {
    if (empty($user_agent)) {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
    }
    if (empty($user_agent)) {
        return 'Unknow';
    }
    // Make case insensitive.
    $t = strtolower($user_agent);

    // If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
    // "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
    //     http://php.net/manual/en/function.strpos.php
    $t = " " . $t;

    // Humans / Regular Users  
    if (strpos($t, 'crkey')) {
        return 'Chromecast';
    } else if (strpos($t, 'opera') || strpos($t, 'opr/'))
        return 'Opera';
    elseif (strpos($t, 'edge'))
        return 'Edge';
    elseif (strpos($t, 'chrome'))
        return 'Chrome';
    elseif (strpos($t, 'safari'))
        return 'Safari';
    elseif (strpos($t, 'firefox'))
        return 'Firefox';
    elseif (strpos($t, 'msie') || strpos($t, 'trident/7'))
        return 'Internet Explorer';
    elseif (strpos($t, 'applecoremedia'))
        return 'Native Apple Player';

    // Search Engines 
    elseif (strpos($t, 'google'))
        return '[Bot] Googlebot';
    elseif (strpos($t, 'bing'))
        return '[Bot] Bingbot';
    elseif (strpos($t, 'slurp'))
        return '[Bot] Yahoo! Slurp';
    elseif (strpos($t, 'duckduckgo'))
        return '[Bot] DuckDuckBot';
    elseif (strpos($t, 'baidu'))
        return '[Bot] Baidu';
    elseif (strpos($t, 'yandex'))
        return '[Bot] Yandex';
    elseif (strpos($t, 'sogou'))
        return '[Bot] Sogou';
    elseif (strpos($t, 'exabot'))
        return '[Bot] Exabot';
    elseif (strpos($t, 'msn'))
        return '[Bot] MSN';

    // Common Tools and Bots
    elseif (strpos($t, 'mj12bot'))
        return '[Bot] Majestic';
    elseif (strpos($t, 'ahrefs'))
        return '[Bot] Ahrefs';
    elseif (strpos($t, 'semrush'))
        return '[Bot] SEMRush';
    elseif (strpos($t, 'rogerbot') || strpos($t, 'dotbot'))
        return '[Bot] Moz or OpenSiteExplorer';
    elseif (strpos($t, 'frog') || strpos($t, 'screaming'))
        return '[Bot] Screaming Frog';

    // Miscellaneous
    elseif (strpos($t, 'facebook'))
        return '[Bot] Facebook';
    elseif (strpos($t, 'pinterest'))
        return '[Bot] Pinterest';

    // Check for strings commonly used in bot user agents  
    elseif (strpos($t, 'crawler') || strpos($t, 'api') ||
            strpos($t, 'spider') || strpos($t, 'http') ||
            strpos($t, 'bot') || strpos($t, 'archive') ||
            strpos($t, 'info') || strpos($t, 'data'))
        return '[Bot] Other';
    _error_log("Unknow user agent ($t) IP=" . getRealIpAddr() . " URI=" . getRequestURI());
    return 'Other (Unknown)';
}

function TimeLogStart($name) {
    global $global;
    if (!empty($global['noDebug'])) {
        return false;
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    if (empty($global['start']) || !is_array($global['start'])) {
        $global['start'] = array();
    }
    $global['start'][$name] = $time;
}

function TimeLogEnd($name, $line, $limit = 0.7) {
    global $global;
    if (!empty($global['noDebug'])) {
        return false;
    }
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $global['start'][$name]), 4);
    if ($total_time > $limit) {
        _error_log("Warning: Slow process detected [{$name}] On  Line {$line} takes {$total_time} seconds to complete. {$_SERVER["SCRIPT_FILENAME"]}");
    }
    TimeLogStart($name);
}

class AVideoLog {

    static $DEBUG = 0;
    static $WARNING = 1;
    static $ERROR = 2;
    static $SECURITY = 3;

}

function _error_log($message, $type = 0) {
    global $global;
    if (!empty($global['noDebug']) && $type == 0) {
        return false;
    }
    $prefix = "AVideoLog::";
    switch ($type) {
        case 0:
            $prefix .= "DEBUG: ";
            break;
        case 1:
            $prefix .= "WARNING: ";
            break;
        case 2:
            $prefix .= "ERROR: ";
            break;
        case 3:
            $prefix .= "SECURITY: ";
            break;
    }
    error_log($prefix . $message);
}

function postVariables($url, $array) {
    if (!$url || !is_string($url) || !preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url)) {
        return false;
    }
    $array = object_to_array($array);
    $ch = curl_init($url);
    @curl_setopt($ch, CURLOPT_HEADER, true);  // we want headers
    @curl_setopt($ch, CURLOPT_NOBODY, true);  // we don't need body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    // execute!
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // close the connection, release resources used
    curl_close($ch);
    if ($httpcode == 200) {
        return true;
    }
    return $httpcode;
}

function _session_start(Array $options = array()) {
    try {
        if (session_status() == PHP_SESSION_NONE) {
            return session_start($options);
        }
    } catch (Exception $exc) {
        _error_log("_session_start: " . $exc->getTraceAsString());
        return false;
    }
}

function _mysql_connect() {
    global $global, $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort;
    if (is_object($global['mysqli']) && empty(@$global['mysqli']->ping())) {
        try {
            $global['mysqli'] = new mysqli($mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, @$mysqlPort);
            if (!empty($global['mysqli_charset'])) {
                $global['mysqli']->set_charset($global['mysqli_charset']);
            }
        } catch (Exception $exc) {
            _error_log($exc->getTraceAsString());
            return false;
        }
    }
}

function remove_utf8_bom($text) {
    if (strlen($text) > 1000000) {
        return $text;
    }

    $bom = pack('H*', 'EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    return $text;
}

function getCacheDir() {
    $p = AVideoPlugin::loadPlugin("Cache");
    return $p->getCacheDir();
}

function clearCache() {
    global $global;
    $dir = "{$global['systemRootPath']}videos/cache/";
    if (!empty($_GET['FirstPage'])) {
        $dir .= "firstPage/";
    }
    rrmdir($dir);
    $dir = getCacheDir();
    if (!empty($_GET['FirstPage'])) {
        $dir .= "firstPage/";
    }
    rrmdir($dir);
    ObjectYPT::deleteCache("getEncoderURL");
}

function getUsageFromFilename($filename, $dir = "") {
    global $global;

    if (!empty($global['getUsageFromFilename'])) { // manually add this variable in your configuration.php file to not scan your video usage
        return 0;
    }

    if (empty($dir)) {
        $dir = "{$global['systemRootPath']}videos/";
    }
    $pos = strrpos($dir, '/');
    $dir .= (($pos === false) ? "/" : "");
    $totalSize = 0;
    _error_log("getUsageFromFilename: start {$dir}{$filename}");
    $files = glob("{$dir}{$filename}*");
    session_write_close();
    $filesProcessed = array();
    foreach ($files as $f) {
        if (is_dir($f)) {
            _error_log("getUsageFromFilename: {$f} is Dir");
            $dirSize = getDirSize($f);
            $totalSize += $dirSize;
            if ($dirSize < 10000 && AVideoPlugin::isEnabledByName('YPTStorage')) {
                // probably the HLS file is hosted on the YPTStorage
                $info = YPTStorage::getFileInfo($filename);
                if (!empty($info->size)) {
                    $totalSize += $info->size;
                }
            }
        } else if (is_file($f)) {
            $filesize = filesize($f);
            if ($filesize < 20) { // that means it is a dummy file
                $lockFile = $f . ".size.lock";
                if (!file_exists($lockFile) || (time() - 600) > filemtime($cachefile)) {
                    file_put_contents($lockFile, time());
                    _error_log("getUsageFromFilename: {$f} is Dummy file ({$filesize})");
                    $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
                    //$bb_b2 = AVideoPlugin::loadPluginIfEnabled('Blackblaze_B2');
                    if (!empty($aws_s3)) {
                        _error_log("getUsageFromFilename: Get from S3");
                        $filesize += $aws_s3->getFilesize($filename);
                    } else if (!empty($bb_b2)) {
                        // TODO
                    } else {
                        $urls = Video::getVideosPaths($filename, true);
                        _error_log("getUsageFromFilename: Paths " . json_encode($urls));
                        if (!empty($urls["m3u8"]['url'])) {
                            $filesize += getUsageFromURL($urls["m3u8"]['url']);
                        }
                        if (!empty($urls['mp4'])) {
                            foreach ($urls['mp4'] as $mp4) {
                                if (in_array($mp4, $filesProcessed)) {
                                    continue;
                                }
                                $filesProcessed[] = $mp4;
                                $filesize += getUsageFromURL($mp4);
                            }
                        }
                        if (!empty($urls['webm'])) {
                            foreach ($urls['webm'] as $mp4) {
                                if (in_array($mp4, $filesProcessed)) {
                                    continue;
                                }
                                $filesProcessed[] = $mp4;
                                $filesize += getUsageFromURL($mp4);
                            }
                        }
                        if (!empty($urls["pdf"]['url'])) {
                            $filesize += getUsageFromURL($urls["pdf"]['url']);
                        }
                        if (!empty($urls["image"]['url'])) {
                            $filesize += getUsageFromURL($urls["image"]['url']);
                        }
                        if (!empty($urls["zip"]['url'])) {
                            $filesize += getUsageFromURL($urls["zip"]['url']);
                        }
                        if (!empty($urls["mp3"]['url'])) {
                            $filesize += getUsageFromURL($urls["mp3"]['url']);
                        }
                    }
                    unlink($lockFile);
                }
            } else {
                _error_log("getUsageFromFilename: {$f} is File ({$filesize})");
            }
            $totalSize += $filesize;
        }
    }
    return $totalSize;
}

/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return The size of the file referenced by $url, or false if the size
 * could not be determined.
 */
function getUsageFromURL($url) {
    global $global;

    if (!empty($global['doNotGetUsageFromURL'])) { // manually add this variable in your configuration.php file to not scan your video usage
        return 0;
    }

    _error_log("getUsageFromURL: start ({$url})");
    // Assume failure.
    $result = false;

    $curl = curl_init($url);

    _error_log("getUsageFromURL: curl_init ");

    try {
        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($curl, CURLOPT_USERAGENT, get_user_agent_string());
        $data = curl_exec($curl);
    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
        _error_log("getUsageFromURL: ERROR " . $exc->getMessage());
        _error_log("getUsageFromURL: ERROR " . curl_errno($curl));
        _error_log("getUsageFromURL: ERROR " . curl_error($curl));
    }

    if ($data) {
        _error_log("getUsageFromURL: response header " . $data);
        $content_length = "unknown";
        $status = "unknown";

        if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
            $status = (int) $matches[1];
        }

        if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
            $content_length = (int) $matches[1];
        }

        // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
        if ($status == 200 || ($status > 300 && $status <= 308)) {
            $result = $content_length;
        }
    } else {
        _error_log("getUsageFromURL: ERROR no response data " . curl_error($curl));
    }

    curl_close($curl);
    return $result;
}

function getDirSize($dir) {
    _error_log("getDirSize: start {$dir}");
    $command = "du -sb {$dir}";
    exec($command . " < /dev/null 2>&1", $output, $return_val);
    if ($return_val !== 0) {
        _error_log("getDirSize: ERROR ON Command {$command}");
        return 0;
    } else {
        if (!empty($output[0])) {
            preg_match("/^([0-9]+).*/", $output[0], $matches);
        }
        if (!empty($matches[1])) {
            _error_log("getDirSize: found {$matches[1]} from - {$output[0]}");
            return intval($matches[1]);
        }

        _error_log("getDirSize: ERROR on pregmatch {$output[0]}");
        return 0;
    }
}

function unsetSearch() {
    unset($_GET['searchPhrase']);
    unset($_POST['searchPhrase']);
    unset($_GET['search']);
    unset($_GET['q']);
}

function encrypt_decrypt($string, $action) {
    global $global;
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'This is my secret key';
    $secret_iv = $global['systemRootPath'];
    while (strlen($secret_iv) < 16) {
        $secret_iv .= $global['systemRootPath'];
    }

    // hash
    $key = hash('sha256', $global['salt']);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function compressString($string) {
    if (function_exists("gzdeflate")) {
        $string = gzdeflate($string, 9);
    }
    return $string;
}

function decompressString($string) {
    if (function_exists("gzinflate")) {
        $string = gzinflate($string);
    }
    return $string;
}

function encryptString($string) {
    if (is_object($string)) {
        $string = json_encode($string);
    }
    return encrypt_decrypt($string, 'encrypt');
}

function decryptString($string) {
    return encrypt_decrypt($string, 'decrypt');
}

function getToken($timeout = 0, $salt = "") {
    global $global;
    $obj = new stdClass();
    $obj->salt = $global['salt'] . $salt;

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

function verifyToken($token, $salt = "") {
    global $global;
    $obj = json_decode(decryptString($token));
    if (empty($obj)) {
        _error_log("verifyToken invalid token");
        return false;
    }
    if ($obj->salt !== $global['salt'] . $salt) {
        _error_log("verifyToken salt fail");
        return false;
    }
    $time = time();
    if (!($time >= $obj->time && $time <= $obj->timeout)) {
        _error_log("verifyToken token timout time = $time; obj->time = $obj->time;  obj->timeout = $obj->timeout");
        return false;
    }
    return true;
}

class YPTvideoObject {

    public $id, $title, $description, $thumbnails, $channelTitle, $videoLink;

    function __construct($id, $title, $description, $thumbnails, $channelTitle, $videoLink) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->thumbnails = $thumbnails;
        $this->channelTitle = $channelTitle;
        $this->videoLink = $videoLink;
    }

}

function isToShowDuration($type) {
    $notShowTo = array('pdf', 'article', 'serie', 'zip', 'image');
    if (in_array($type, $notShowTo)) {
        return false;
    } else {
        return true;
    }
}

function _dieAndLogObject($obj, $prefix = "") {
    $objString = json_encode($obj);
    _error_log($prefix . $objString);
    die($objString);
}

function isAVideoPlayer() {
    global $isEmbed;

    if (!empty($_GET['videoName']) || !empty($_GET['u']) || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
        return true;
    }
    return false;
}

function isVideo() {
    global $isModeYouTube;
    return !empty($isModeYouTube) || isEmbed() || isLive();
}

function isChannel() {
    global $isChannel;
    if (!empty($isChannel) && !isVideo()) {
        $user_id = 0;
        if (empty($_GET['channelName'])) {
            if (User::isLogged()) {
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

function isEmbed() {
    global $isEmbed;
    return !empty($isEmbed);
}

function isLive() {
    global $isLive;
    return !empty($isLive);
}

function isVideoPlayerHasProgressBar() {
    if (isLive()) {
        $obj = AVideoPlugin::getObjectData('Live');
        if (empty($obj->disableDVR)) {
            return true;
        }
    } else if (isAVideoPlayer()) {
        return true;
    }
    return false;
}

function isHLS() {
    global $video, $global;
    if (isLive()) {
        return true;
    } else if ($video['type'] == 'video' && file_exists("{$global['systemRootPath']}videos/{$video['filename']}/index.m3u8")) {
        return true;
    }
    return false;
}

function getRequestURI() {
    if (empty($_SERVER['REQUEST_URI'])) {
        return "";
    }
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function getSelfURI() {
    if (empty($_SERVER['PHP_SELF'])) {
        return "";
    }
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]";
}

function hasLastSlash($word) {
    return substr($word, -1) === '/';
}

function addLastSlash($word) {
    return $word . (hasLastSlash($word) ? "" : "/");
}

function URLHasLastSlash() {
    return hasLastSlash($_SERVER["REQUEST_URI"]);
}

function getSEOComplement() {
    $txt = "";
    if (!empty($_GET['catName'])) {
        $txt .= " {$_GET['catName']}";
    }
    if (!empty($_GET['page'])) {
        $txt .= " " . __("Page") . " {$_GET['page']}";
    }
    if (!empty($_GET['channelName'])) {
        $txt .= " {$_GET['channelName']}";
    }
    if (!empty($_GET['type'])) {
        $txt .= " {$_GET['type']}";
    }
    if (!empty($_GET['showOnly'])) {
        $txt .= " {$_GET['showOnly']}";
    }
    if (!empty($_GET['error'])) {
        $txt .= " " . __("Error");
    }
    if (URLHasLastSlash()) {
        $txt .= ": •";
    }
    if (strrpos($_SERVER['HTTP_HOST'], 'www.') === false) {
        $txt .= ": ¨";
    }
    if (!empty($_GET['error'])) {
        $txt .= ": …";
    }
    return htmlentities(strip_tags($txt));
}

function getCurrentPage() {
    if (!empty($_REQUEST['current'])) {
        return intval($_REQUEST['current']);
    } else if (!empty($_POST['current'])) {
        return intval($_POST['current']);
    } else if (!empty($_GET['current'])) {
        return intval($_GET['current']);
    }
    return 1;
}

function getRowCount($default = 1000) {
    if (!empty($_REQUEST['rowCount'])) {
        return intval($_REQUEST['rowCount']);
    } else if (!empty($_POST['rowCount'])) {
        return intval($_POST['rowCount']);
    } else if (!empty($_GET['rowCount'])) {
        return intval($_GET['rowCount']);
    } else if (!empty($_REQUEST['length'])) {
        return intval($_REQUEST['length']);
    } else if (!empty($_POST['length'])) {
        return intval($_POST['length']);
    } else if (!empty($_GET['length'])) {
        return intval($_GET['length']);
    }
    return $default;
}

function getSearchVar() {
    if (!empty($_REQUEST['search'])) {
        return $_REQUEST['search'];
    } else if (!empty($_REQUEST['q'])) {
        return $_REQUEST['q'];
    } if (!empty($_REQUEST['searchPhrase'])) {
        return $_REQUEST['searchPhrase'];
    } else if (!empty($_REQUEST['search']['value'])) {
        return $_REQUEST['search']['value'];
    }
    return "";
}

$cleanSearchHistory = "";

function cleanSearchVar() {
    global $cleanSearchHistory;
    $search = getSearchVar();
    if (!empty($search)) {
        $cleanSearchHistory = $search;
    }
    $searchIdex = array('q', 'searchPhrase', 'search');
    foreach ($searchIdex as $value) {
        unset($_REQUEST[$value]);
        unset($_POST[$value]);
        unset($_GET[$value]);
    }
}

function reloadSearchVar() {
    global $cleanSearchHistory;
    $_REQUEST['search'] = $cleanSearchHistory;
    if (empty($_GET['search'])) {
        $_GET['search'] = $cleanSearchHistory;
    }
    if (empty($_POST['search'])) {
        $_POST['search'] = $cleanSearchHistory;
    }
}

function wget($url, $filename, $debug = false) {
    if (wgetIsLocked($url)) {
        if ($debug) {
            _error_log("wget: ERROR the url is already downloading $url, $filename");
        }
        return false;
    }
    wgetLock($url);
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $content = file_get_contents($url);
        if (!empty($content) && file_put_contents($filename, $content) > 100) {
            wgetRemoveLock($url);
            return true;
        }
        wgetRemoveLock($url);
        return false;
    }
    $cmd = "wget --tries=1 {$url} -O {$filename} --no-check-certificate";
    if ($debug) {
        _error_log("wget Start ({$cmd}) ");
    }
    //echo $cmd;
    exec($cmd);
    wgetRemoveLock($url);
    if (!file_exists($filename)) {
        _error_log("wget: ERROR the url does not download $url, $filename");
        return false;
    }
    if (empty(filesize($filename))) {
        _error_log("wget: ERROR the url download but is empty $url, $filename");
        return true;
    }
    return false;
}

function wgetLockFile($url) {
    return getTmpDir("YPTWget") . md5($url) . ".lock";
}

function wgetLock($url) {
    $file = wgetLockFile($url);
    return file_put_contents($file, time() . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function wgetRemoveLock($url) {
    $filename = wgetLockFile($url);
    if (!file_exists($filename)) {
        return false;
    }
    return unlink($filename);
}

function wgetIsLocked($url) {
    $filename = wgetLockFile($url);
    if (!file_exists($filename)) {
        return false;
    }
    $time = intval(file_get_contents($filename));
    if (time() - $time > 36000) { // more then 10 hours
        unlink($filename);
        return false;
    }
    return true;
}

// due the some OS gives a fake is_writable response
function isWritable($dir) {
    $dir = rtrim($dir, '/') . '/';
    $file = $dir . uniqid();
    $result = false;
    $time = time();
    if (@file_put_contents($file, $time)) {
        if ($fileTime = @file_get_contents($file)) {
            if ($fileTime == $time) {
                $result = true;
            }
        }
    }
    @unlink($file);
    return $result;
}

function _isWritable($dir) {
    if (!isWritable($dir)) {
        return false;
    }
    $tmpFile = "{$dir}" . uniqid();
    $bytes = @file_put_contents($tmpFile, time());
    @unlink($tmpFile);
    return !empty($bytes);
}

function getTmpDir($subdir = "") {
    global $global;
    if (empty($_SESSION['getTmpDir'])) {
        $_SESSION['getTmpDir'] = array();
    }
    if (empty($_SESSION['getTmpDir'][$subdir . "_"])) {
        $tmpDir = sys_get_temp_dir();
        if (empty($tmpDir) || !_isWritable($tmpDir)) {
            $tmpDir = "{$global['systemRootPath']}videos/cache/";
        }
        $tmpDir = rtrim($tmpDir, '/') . '/';
        $tmpDir = "{$tmpDir}{$subdir}";
        $tmpDir = rtrim($tmpDir, '/') . '/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        _session_start();
        $_SESSION['getTmpDir'][$subdir . "_"] = $tmpDir;
    } else {
        $tmpDir = $_SESSION['getTmpDir'][$subdir . "_"];
    }
    return $tmpDir;
}

function getMySQLDate() {
    global $global;
    $sql = "SELECT now() as time FROM configurations LIMIT 1";
    // I had to add this because the about from customize plugin was not loading on the about page http://127.0.0.1/AVideo/about
    $res = sqlDAL::readSql($sql);
    $data = sqlDAL::fetchAssoc($res);
    sqlDAL::close($res);
    if ($res) {
        $row = $data['time'];
    } else {
        $row = false;
    }
    return $row;
}

function _file_put_contents($filename, $data, $flags = 0, $context = NULL) {
    make_path($filename);
    return file_put_contents($filename, $data, $flags, $context);
}

function html2plainText($html) {
    $text = strip_tags($html);
    $text = str_replace(array('\\', "\n", "\r", '"'), array('', ' ', ' ', ''), trim($text));
    return $text;
}

function getInputPassword($id, $attributes = 'class="form-control"', $paceholder = '') {
    if (empty($paceholder)) {
        $paceholder = __("Password");
    }
    ?>
    <div class="input-group">
        <span class="input-group-addon"><i class="fas fa-lock"></i></span>
        <input id="<?php echo $id; ?>" type="password"  placeholder="<?php echo $paceholder; ?>" <?php echo $attributes; ?> >
            <span class="input-group-addon" style="cursor: pointer;" id="toggle_<?php echo $id; ?>"  data-toggle="tooltip" data-placement="left" title="<?php echo __('Show/Hide Password'); ?>"><i class="fas fa-eye-slash"></i></span>
    </div>    
    <script>
        $(document).ready(function () {
            $('#toggle_<?php echo $id; ?>').click(function () {
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

function getInputCopyToClipboard($id, $value, $attributes = 'class="form-control" readonly="readonly"', $paceholder = '') {
    if (strpos($value, '"') !== false) {
        $valueAttr = "value='{$value}'";
    } else {
        $valueAttr = 'value="' . $value . '"';
    }
    ?>
    <div class="input-group">
        <input id="<?php echo $id; ?>" type="text"  placeholder="<?php echo $paceholder; ?>" <?php echo $attributes; ?> <?php echo $valueAttr; ?> >
            <span class="input-group-addon" style="cursor: pointer;" id="copyToClipboard_<?php echo $id; ?>"  data-toggle="tooltip" data-placement="left" title="<?php echo __('Copy to Clipboard'); ?>"><i class="fas fa-clipboard"></i></span>
    </div>    
    <script>
        var timeOutCopyToClipboard_<?php echo $id; ?>;
        $(document).ready(function () {
            $('#copyToClipboard_<?php echo $id; ?>').click(function () {
                clearTimeout(timeOutCopyToClipboard_<?php echo $id; ?>);
                $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("fa-clipboard");
                $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("text-success");
                $('#copyToClipboard_<?php echo $id; ?>').addClass('bg-success');
                $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("fa-clipboard-check");
                timeOutCopyToClipboard_<?php echo $id; ?> = setTimeout(function () {
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("fa-clipboard-check");
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').removeClass("text-success");
                    $('#copyToClipboard_<?php echo $id; ?>').removeClass('bg-success');
                    $('#copyToClipboard_<?php echo $id; ?>').find('i').addClass("fa-clipboard");
                }, 3000);
                copyToClipboard($('#<?php echo $id; ?>').val());
            })
        });
    </script>
    <?php
}

function getButtontCopyToClipboard($elemToCopyId, $attributes = 'class="btn btn-default btn-sm btn-xs pull-right"', $label = "Copy to Clipboard") {
    $id = "getButtontCopyToClipboard" . uniqid();
    ?>
    <button id="<?php echo $id; ?>" <?php echo $attributes; ?> data-toggle="tooltip" data-placement="left" title="<?php echo __($label); ?>"><i class="fas fa-clipboard"></i> <?php echo __($label); ?></button>
    <script>
        var timeOutCopyToClipboard_<?php echo $id; ?>;
        $(document).ready(function () {
            $('#<?php echo $id; ?>').click(function () {
                clearTimeout(timeOutCopyToClipboard_<?php echo $id; ?>);
                $('#<?php echo $id; ?>').find('i').removeClass("fa-clipboard");
                $('#<?php echo $id; ?>').find('i').addClass("text-success");
                $('#<?php echo $id; ?>').addClass('bg-success');
                $('#<?php echo $id; ?>').find('i').addClass("fa-clipboard-check");
                timeOutCopyToClipboard_<?php echo $id; ?> = setTimeout(function () {
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
}

function fakeBrowser($url) {
    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $url);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);
    return $output;
}
