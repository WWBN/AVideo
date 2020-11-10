<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/Mobile_Detect.php';
$detect = new Mobile_Detect;
if (!$detect->isMobile() && !$detect->isTablet()) {
    $browser = get_browser_name();
    $browserInfo = "chrome.php";
    if ($browser == ('Chrome')) {
        $browserInfo = "chrome.php";
    } else if ($browser == ('Safari')) {
        $browserInfo = "safari.php";
    } else if ($browser == ('Firefox')) {
        $browserInfo = "firefox.php";
    } else {
        $browserInfo = "chrome.php";
    }

    $file = "{$global['systemRootPath']}plugin/PlayerSkins/allowAutoplay/locale/{$_SESSION["language"]}/chrome.php";
    if(!file_exists($file)){
        $file = "{$global['systemRootPath']}plugin/PlayerSkins/allowAutoplay/chrome.php";
    }
    include $file;
}