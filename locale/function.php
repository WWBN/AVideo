<?php

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
if (empty($_SESSION['language'])) {
    $_SESSION['language'] = $config->getLanguage();
}
if (!empty($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}
@include_once "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";

function __($str) {
    global $t;
    if (empty($t[$str])) {
        return $str;
    } else {
        return $t[$str];
    }
}

function getAllFlags() {
    global $global;
    $dir = "{$global['systemRootPath']}view/css/flag-icon-css-master/flags/4x3";
    $flags = array();
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $flags[] = str_replace(".svg", "", $entry);
            }
        }
        closedir($handle);
    }
    sort($flags);
    return $flags;
}

function getEnabledLangs() {
    global $global;
    $dir = "{$global['systemRootPath']}locale";
    $flags = array('us');
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && $entry != "index.php" && $entry != "function.php" && $entry != "save.php") {
                $flags[] = str_replace(".php", "", $entry);
            }
        }
        closedir($handle);
    }
    sort($flags);
    return $flags;
}

function textToLink($text) {
    $reg_exUrl = "/((http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3})([\s.,;\?\!]|$)/";
    if (preg_match_all($reg_exUrl, $text, $matches)) {
        foreach ($matches[0] as $i => $match) {
            $text = str_replace(
                    $match, '<a href="' . $matches[1][$i] . '" rel="nofollow">' . $matches[1][$i] . '</a>' . $matches[3][$i], $text
            );
        }
    }
    return $text;
}
